<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryIndexRequest;
use App\Models\Branch;
use App\Models\Item;
use App\Models\ItemBranch;

class InventoryController extends Controller
{
    public function index(InventoryIndexRequest $request)
    {
        $filters = $request->validated();
        $inventory = ItemBranch::query()
            ->with(['item.supplier', 'branchRecord'])
            ->whereNotNull('branch_id')
            ->whereHas('item')
            ->whereHas('branchRecord')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery->where('item_branches.location', 'like', "%{$search}%")
                        ->orWhere('item_branches.item_id', $search)
                        ->orWhereHas('item', fn ($itemQuery) => $itemQuery->where('description', 'like', "%{$search}%"));
                });
            })
            ->when($filters['branch'] ?? null, fn ($query, int $branchId) => $query->where('branch_id', $branchId))
            ->when($filters['category'] ?? null, fn ($query, string $category) => $query->whereHas('item', fn ($itemQuery) => $itemQuery->where('category', $category)))
            ->when($filters['status'] ?? null, function ($query, string $status): void {
                match ($status) {
                    ItemBranch::STATUS_OUT_OF_STOCK => $query->where('current_stock', 0),
                    ItemBranch::STATUS_LOW_STOCK => $query->where('current_stock', '>', 0)->whereColumn('current_stock', '<=', 'reorder_level'),
                    ItemBranch::STATUS_IN_STOCK => $query->whereColumn('current_stock', '>', 'reorder_level'),
                };
            })
            ->orderBy('item_id')
            ->orderBy('branch_id')
            ->paginate(15)
            ->withQueryString();

        return view('inventory.index', [
            'inventory' => $inventory,
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'categories' => Item::query()->distinct()->orderBy('category')->pluck('category'),
            'filters' => $filters,
        ]);
    }

    public function show(ItemBranch $itemBranch)
    {
        $this->authorize('view', $itemBranch);

        abort_unless($itemBranch->branch_id && $itemBranch->item && $itemBranch->branchRecord, 404);

        $itemBranch->load(['item.supplier', 'branchRecord']);
        $movements = $itemBranch->movements()
            ->with('purchaseReceiptItem.receipt.purchaseOrder.supplier')
            ->latest()
            ->paginate(20);

        return view('inventory.show', compact('itemBranch', 'movements'));
    }
}
