<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryIssueRequest;
use App\Http\Requests\StoreInventoryIssueReturnRequest;
use App\Models\InventoryIssue;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\ItemBranch;
use App\Models\ItemSerial;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryIssueController extends Controller
{
    public function create(ItemBranch $itemBranch)
    {
        $this->authorize('issue-inventory');
        abort_unless($itemBranch->item && $itemBranch->locationRecord, 404);

        $itemBranch->load(['item', 'locationRecord.branch']);
        $serials = $itemBranch->item->is_serialized
            ? $itemBranch->serials()->where('status', ItemSerial::STATUS_ACTIVE)->orderBy('serial_number')->get()
            : collect();
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return view('inventory.issues.create', compact('itemBranch', 'serials', 'users'));
    }

    public function store(StoreInventoryIssueRequest $request, ItemBranch $itemBranch)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $itemBranch, $request): void {
            $stock = ItemBranch::query()->whereKey($itemBranch->getKey())->lockForUpdate()->firstOrFail();
            $item = Item::findOrFail($stock->item_id);
            $quantity = (float) $data['quantity'];

            if ($quantity > (float) $stock->current_stock) {
                throw ValidationException::withMessages(['quantity' => 'The issue quantity exceeds available stock.']);
            }

            $serials = collect();
            $serialIds = collect($data['serial_ids'] ?? [])->map(fn($id) => (int) $id)->unique()->values();
            if ($item->is_serialized) {
                if ($quantity !== floor($quantity)) {
                    throw ValidationException::withMessages(['quantity' => 'Serialized items must be issued in whole units.']);
                }
                if ($serialIds->count() !== (int) $quantity) {
                    throw ValidationException::withMessages(['serial_ids' => 'Select the exact serial numbers being issued.']);
                }

                $serials = ItemSerial::query()
                    ->whereIn('id', $serialIds)
                    ->where('item_id', $item->id)
                    ->where('item_branch_id', $stock->id)
                    ->where('status', ItemSerial::STATUS_ACTIVE)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($serials->count() !== $serialIds->count()) {
                    throw ValidationException::withMessages(['serial_ids' => 'One or more selected serial numbers are no longer available at this location.']);
                }
            } elseif ($serialIds->isNotEmpty()) {
                throw ValidationException::withMessages(['serial_ids' => 'Serial numbers can only be selected for serialized items.']);
            }

            $issue = InventoryIssue::create([
                'item_branch_id' => $stock->id,
                'type' => $data['type'],
                'quantity' => $quantity,
                'returned_quantity' => 0,
                'recipient_user_id' => $data['recipient_user_id'] ?? null,
                'recipient_name' => filled($data['recipient_name'] ?? null) ? trim($data['recipient_name']) : null,
                'purpose' => trim($data['purpose']),
                'issued_by' => $request->user()->id,
            ]);

            $stockAfter = (float) $stock->current_stock - $quantity;
            $stock->update(['current_stock' => $stockAfter]);

            if ($serials->isNotEmpty()) {
                $issue->serials()->attach($serials->modelKeys());
                ItemSerial::whereKey($serials->modelKeys())->update([
                    'status' => $data['type'] === InventoryIssue::TYPE_CHECKOUT
                        ? ItemSerial::STATUS_CHECKED_OUT
                        : ItemSerial::STATUS_INACTIVE,
                ]);
            }

            InventoryMovement::create([
                'item_branch_id' => $stock->id,
                'inventory_issue_id' => $issue->id,
                'type' => InventoryMovement::TYPE_ISSUE_OUT,
                'quantity' => $quantity,
                'unit_cost' => $stock->unit_cost,
                'stock_after' => $stockAfter,
                'from_location_id' => $stock->location_id,
                'performed_by' => $request->user()->id,
            ]);
        });

        return redirect()->route('inventory.show', $itemBranch)->with('success', 'Inventory issued successfully.');
    }

    public function return(StoreInventoryIssueReturnRequest $request, InventoryIssue $inventoryIssue)
    {
        $data = $request->validated();
        $itemBranchId = $inventoryIssue->item_branch_id;

        DB::transaction(function () use ($data, $inventoryIssue, $request): void {
            $issue = InventoryIssue::query()->whereKey($inventoryIssue->id)->lockForUpdate()->firstOrFail();
            if ($issue->type !== InventoryIssue::TYPE_CHECKOUT) {
                throw ValidationException::withMessages(['quantity' => 'Consumed issues cannot be returned.']);
            }

            $stock = ItemBranch::query()->whereKey($issue->item_branch_id)->lockForUpdate()->firstOrFail();
            $item = Item::findOrFail($stock->item_id);
            $remaining = (float) $issue->quantity - (float) $issue->returned_quantity;
            $serialIds = collect($data['serial_ids'] ?? [])->map(fn($id) => (int) $id)->unique()->values();

            if ($item->is_serialized) {
                if ($serialIds->isEmpty()) {
                    throw ValidationException::withMessages(['serial_ids' => 'Select the serialized units being returned.']);
                }

                $quantity = (float) $serialIds->count();
                if ($quantity > $remaining) {
                    throw ValidationException::withMessages(['serial_ids' => 'The return exceeds the outstanding checkout quantity.']);
                }

                $links = DB::table('inventory_issue_serials')
                    ->where('inventory_issue_id', $issue->id)
                    ->whereIn('item_serial_id', $serialIds)
                    ->whereNull('returned_at')
                    ->lockForUpdate()
                    ->get();
                $serials = ItemSerial::query()
                    ->whereIn('id', $serialIds)
                    ->where('item_branch_id', $stock->id)
                    ->where('status', ItemSerial::STATUS_CHECKED_OUT)
                    ->lockForUpdate()
                    ->get();

                if ($links->count() !== $serialIds->count() || $serials->count() !== $serialIds->count()) {
                    throw ValidationException::withMessages(['serial_ids' => 'One or more selected units are not outstanding on this checkout.']);
                }

                DB::table('inventory_issue_serials')
                    ->where('inventory_issue_id', $issue->id)
                    ->whereIn('item_serial_id', $serialIds)
                    ->whereNull('returned_at')
                    ->update(['returned_at' => now(), 'updated_at' => now()]);
                ItemSerial::whereKey($serialIds)->update(['status' => ItemSerial::STATUS_ACTIVE]);
            } else {
                if ($serialIds->isNotEmpty()) {
                    throw ValidationException::withMessages(['serial_ids' => 'Serial numbers can only be returned for serialized items.']);
                }
                if (! isset($data['quantity'])) {
                    throw ValidationException::withMessages(['quantity' => 'Enter the quantity being returned.']);
                }

                $quantity = (float) $data['quantity'];
                if ($quantity > $remaining) {
                    throw ValidationException::withMessages(['quantity' => 'The return exceeds the outstanding checkout quantity.']);
                }
            }

            $stockAfter = (float) $stock->current_stock + $quantity;
            $stock->update(['current_stock' => $stockAfter]);
            $issue->update(['returned_quantity' => (float) $issue->returned_quantity + $quantity]);

            InventoryMovement::create([
                'item_branch_id' => $stock->id,
                'inventory_issue_id' => $issue->id,
                'type' => InventoryMovement::TYPE_ISSUE_RETURN,
                'quantity' => $quantity,
                'unit_cost' => $stock->unit_cost,
                'stock_after' => $stockAfter,
                'to_location_id' => $stock->location_id,
                'performed_by' => $request->user()->id,
            ]);
        });

        return redirect()->route('inventory.show', $itemBranchId)->with('success', 'Checkout returned successfully.');
    }
}
