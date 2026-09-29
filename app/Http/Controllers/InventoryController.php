<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryIndexRequest;
use App\Models\Branch;
use App\Models\Item;
use App\Models\ItemBranch;
use App\Models\InventoryMovement;
use App\Models\GEOrderItem;
use App\Models\Location;
use App\Models\PurchaseOrderItem;

class InventoryController extends Controller
{
    public function index(InventoryIndexRequest $request)
    {
        $filters = $request->validated();
        $inventory = ItemBranch::query()
            ->with(['item.supplier', 'branchRecord', 'locationRecord'])
            ->whereNotNull('branch_id')
            ->whereHas('item')
            ->whereHas('branchRecord')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $itemBranchTable = (new ItemBranch)->getTable();
                    $searchQuery->where($itemBranchTable . '.location', 'like', "%{$search}%")
                        ->orWhereHas('locationRecord', fn($locationQuery) => $locationQuery->where('name', 'like', "%{$search}%"))
                        ->when(is_numeric($search), fn($itemQuery) => $itemQuery->orWhere($itemBranchTable . '.item_id', (int) $search))
                        ->orWhereHas('item', fn($itemQuery) => $itemQuery->where('description', 'like', "%{$search}%"));
                });
            })
            ->when($filters['branch'] ?? null, fn($query, int $branchId) => $query->where('branch_id', $branchId))
            ->when($filters['location'] ?? null, fn($query, int $locationId) => $query->where('location_id', $locationId))
            ->when($filters['category'] ?? null, fn($query, string $category) => $query->whereHas('item', fn($itemQuery) => $itemQuery->where('category', $category)))
            ->when($filters['status'] ?? null, function ($query, string $status): void {
                match ($status) {
                    ItemBranch::STATUS_OUT_OF_STOCK => $query->where('current_stock', 0),
                    ItemBranch::STATUS_LOW_STOCK => $query->where('current_stock', '>', 0)->whereColumn('current_stock', '<=', 'reorder_level'),
                    ItemBranch::STATUS_IN_STOCK => $query->whereColumn('current_stock', '>', 'reorder_level'),
                };
            })
            ->orderBy('item_id')
            ->orderBy('branch_id')
            ->orderBy('location_id')
            ->paginate(15)
            ->withQueryString();

        return view('inventory.index', [
            'inventory' => $inventory,
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'locations' => Location::query()
                ->with('branch')
                ->when($filters['branch'] ?? null, fn($query, int $branchId) => $query->where('branch_id', $branchId))
                ->orderBy('name')->get(),
            'categories' => Item::query()->distinct()->orderBy('category')->pluck('category'),
            'filters' => $filters,
        ]);
    }

    public function show(ItemBranch $itemBranch)
    {
        $this->authorize('view', $itemBranch);

        abort_unless($itemBranch->branch_id && $itemBranch->item && $itemBranch->branchRecord, 404);

        $itemBranch->load(['item.supplier', 'branchRecord', 'locationRecord', 'serials']);
        $locationStocks = ItemBranch::query()
            ->where('item_id', $itemBranch->item_id)
            ->with(['branchRecord', 'locationRecord'])
            ->orderBy('branch_id')
            ->orderBy('location_id')
            ->get();
        $totalStock = $locationStocks->sum(fn(ItemBranch $record) => (float) $record->current_stock);
        $movements = InventoryMovement::query()
            ->whereHas('itemBranch', fn($query) => $query->where('item_id', $itemBranch->item_id))
            ->with(['itemBranch.locationRecord', 'purchaseReceiptItem.receipt.purchaseOrder.supplier', 'fromLocation', 'toLocation', 'performer'])
            ->latest()
            ->paginate(20);

        $purchaseOrderItems = PurchaseOrderItem::query()
            ->where('item_id', $itemBranch->item_id)
            ->whereHas('purchaseOrder')
            ->with(['purchaseOrder.branch', 'purchaseOrder.supplier', 'purchaseOrder.geOrder', 'receiptItems.receipt'])
            ->orderBy('purchase_order_id')
            ->orderBy('id')
            ->get();
        $purchaseOrders = $purchaseOrderItems->groupBy('purchase_order_id')->map(function ($orderItems): array {
            return [
                'order' => $orderItems->first()->purchaseOrder,
                'items' => $orderItems,
                'ordered_quantity' => $orderItems->sum(fn(PurchaseOrderItem $orderItem) => (float) $orderItem->quantity),
                'received_quantity' => $orderItems->sum(fn(PurchaseOrderItem $orderItem) => $orderItem->receiptItems->sum(fn($receiptItem) => (float) $receiptItem->quantity_received)),
            ];
        });

        $geOrderItems = GEOrderItem::query()
            ->where('item_id', $itemBranch->item_id)
            ->whereHas('geOrder')
            ->with(['geOrder.branch', 'geOrder.supplier'])
            ->orderBy('ge_order_id')
            ->orderBy('id')
            ->get();
        $geOrders = $geOrderItems->groupBy('ge_order_id')->map(fn($orderItems) => [
            'order' => $orderItems->first()->geOrder,
            'items' => $orderItems,
        ]);

        return view('inventory.show', compact('itemBranch', 'locationStocks', 'totalStock', 'movements', 'purchaseOrders', 'geOrders'));
    }
}
