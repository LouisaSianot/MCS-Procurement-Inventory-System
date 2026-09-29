<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryTransferRequest;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\ItemBranch;
use App\Models\ItemSerial;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryTransferController extends Controller
{
    public function create()
    {
        $this->authorize('transfer', ItemBranch::class);

        $stockRecords = ItemBranch::with(['item', 'locationRecord.branch'])
            ->where('current_stock', '>', 0)
            ->orderBy('item_id')
            ->get();
        $items = $stockRecords->pluck('item')->filter()->unique('id')->sortBy('description')->values();
        $locations = Location::with('branch')->orderBy('name')->get();

        return view('inventory.transfers.create', compact('stockRecords', 'items', 'locations'));
    }

    public function store(StoreInventoryTransferRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request): void {
            $locations = Location::query()
                ->whereIn('id', [$data['from_location_id'], $data['to_location_id']])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $sourceLocation = $locations->get((int) $data['from_location_id']);
            $destinationLocation = $locations->get((int) $data['to_location_id']);

            if (! $sourceLocation || ! $destinationLocation || $sourceLocation->is($destinationLocation)) {
                throw ValidationException::withMessages(['to_location_id' => 'Choose two different valid locations.']);
            }

            if ((int) $sourceLocation->branch_id !== (int) $destinationLocation->branch_id) {
                throw ValidationException::withMessages(['to_location_id' => 'Source and destination locations must belong to the same branch.']);
            }

            $source = ItemBranch::query()
                ->where('item_id', $data['item_id'])
                ->where('location_id', $sourceLocation->id)
                ->where('branch_id', $sourceLocation->branch_id)
                ->lockForUpdate()
                ->first();
            $quantity = (float) $data['quantity'];

            if (! $source || (float) $source->current_stock < $quantity) {
                throw ValidationException::withMessages(['quantity' => 'The transfer quantity exceeds available source stock.']);
            }

            $item = Item::findOrFail($data['item_id']);
            $serials = collect();
            if ($item->is_serialized) {
                if ($quantity !== floor($quantity)) {
                    throw ValidationException::withMessages(['quantity' => 'Serialized items must be transferred in whole units.']);
                }

                $serials = ItemSerial::query()
                    ->where('item_id', $item->id)
                    ->where('item_branch_id', $source->id)
                    ->where('status', ItemSerial::STATUS_ACTIVE)
                    ->orderBy('id')
                    ->limit((int) $quantity)
                    ->lockForUpdate()
                    ->get();

                if ($serials->count() !== (int) $quantity) {
                    throw ValidationException::withMessages(['quantity' => 'The source location does not have enough active serialized units.']);
                }
            }

            $destination = ItemBranch::query()
                ->where('item_id', $item->id)
                ->where('location_id', $destinationLocation->id)
                ->lockForUpdate()
                ->first();

            if (! $destination) {
                $destination = ItemBranch::create([
                    'item_id' => $item->id,
                    'branch_id' => $destinationLocation->branch_id,
                    'branch' => $source->branch,
                    'location_id' => $destinationLocation->id,
                    'location' => $destinationLocation->name,
                    'uom' => $source->uom,
                    'unit_cost' => $source->unit_cost,
                ]);
            }

            $sourceStockAfter = (float) $source->current_stock - $quantity;
            $destinationOldStock = (float) $destination->current_stock;
            $destinationStockAfter = $destinationOldStock + $quantity;
            $transferCost = (float) $source->unit_cost;

            $source->update(['current_stock' => $sourceStockAfter]);
            $destination->update([
                'current_stock' => $destinationStockAfter,
                'unit_cost' => $destinationStockAfter > 0
                    ? (($destinationOldStock * (float) $destination->unit_cost) + ($quantity * $transferCost)) / $destinationStockAfter
                    : 0,
                'location' => $destinationLocation->name,
            ]);

            if ($serials->isNotEmpty()) {
                ItemSerial::whereKey($serials->modelKeys())->update(['item_branch_id' => $destination->id]);
            }

            InventoryMovement::create([
                'item_branch_id' => $source->id,
                'type' => InventoryMovement::TYPE_TRANSFER,
                'quantity' => $quantity,
                'unit_cost' => $transferCost,
                'stock_after' => $sourceStockAfter,
                'from_location_id' => $sourceLocation->id,
                'to_location_id' => $destinationLocation->id,
                'performed_by' => $request->user()->id,
                'destination_stock_after' => $destinationStockAfter,
            ]);
        });

        return redirect()->route('inventory.index')->with('success', 'Stock transferred successfully.');
    }
}
