<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\ItemBranch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockTransactionService
{
    public function decrease(int $itemId, int $branchId, float $quantity, string $type, ?string $uom = null): ItemBranch
    {
        return DB::transaction(function () use ($itemId, $branchId, $quantity, $type): ItemBranch {
            $itemBranch = ItemBranch::query()
                ->where('item_id', $itemId)
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->first();

            if (! $itemBranch) {
                throw ValidationException::withMessages(['item_id' => 'The item is not stocked at the selected branch.']);
            }
            if ($uom !== null && strcasecmp((string) $itemBranch->uom, $uom) !== 0) {
                throw ValidationException::withMessages(['uom' => 'UOM must match the selected ItemBranch stock record.']);
            }
            if ($quantity <= 0 || (float) $itemBranch->current_stock < $quantity) {
                throw ValidationException::withMessages(['quantity' => 'Quantity exceeds available stock.']);
            }

            $itemBranch->current_stock = (float) $itemBranch->current_stock - $quantity;
            $itemBranch->save();
            InventoryMovement::create([
                'item_branch_id' => $itemBranch->id,
                'type' => $type,
                'quantity' => $quantity,
                'unit_cost' => $itemBranch->unit_cost,
                'stock_after' => $itemBranch->current_stock,
            ]);

            return $itemBranch;
        });
    }

    public function increase(int $itemId, int $branchId, float $quantity, string $type, ?string $uom = null): ItemBranch
    {
        return DB::transaction(function () use ($itemId, $branchId, $quantity, $type): ItemBranch {
            $itemBranch = ItemBranch::query()->where('item_id', $itemId)->where('branch_id', $branchId)->lockForUpdate()->firstOrFail();
            if ($uom !== null && strcasecmp((string) $itemBranch->uom, $uom) !== 0) {
                throw ValidationException::withMessages(['uom' => 'UOM must match the selected ItemBranch stock record.']);
            }
            if ($quantity <= 0) throw ValidationException::withMessages(['quantity' => 'Quantity must be greater than zero.']);
            $itemBranch->current_stock = (float) $itemBranch->current_stock + $quantity;
            $itemBranch->save();
            InventoryMovement::create(['item_branch_id' => $itemBranch->id, 'type' => $type, 'quantity' => $quantity, 'unit_cost' => $itemBranch->unit_cost, 'stock_after' => $itemBranch->current_stock]);
            return $itemBranch;
        });
    }
}
