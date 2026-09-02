<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemBranch extends Model
{
    use HasFactory;

    public const STATUS_IN_STOCK = 'in_stock';
    public const STATUS_LOW_STOCK = 'low_stock';
    public const STATUS_OUT_OF_STOCK = 'out_of_stock';

    protected $fillable = ['branch', 'branch_id', 'item_id', 'uom', 'current_stock', 'unit_cost', 'location', 'reorder_level', 'reorder_quantity'];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'reorder_quantity' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function branchRecord(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function inventoryValue(): float
    {
        return (float) $this->current_stock * (float) $this->unit_cost;
    }

    public function stockStatus(): string
    {
        if ((float) $this->current_stock === 0.0) {
            return self::STATUS_OUT_OF_STOCK;
        }

        return (float) $this->current_stock <= (float) $this->reorder_level
            ? self::STATUS_LOW_STOCK
            : self::STATUS_IN_STOCK;
    }

    public function stockStatusLabel(): string
    {
        return match ($this->stockStatus()) {
            self::STATUS_OUT_OF_STOCK => 'Out of Stock',
            self::STATUS_LOW_STOCK => 'Low Stock',
            default => 'In Stock',
        };
    }

    protected static function booted()
    {
        static::saving(function ($itemBranch) {
            if ($itemBranch->current_stock < 0) {
                throw new \InvalidArgumentException('Current stock cannot be negative.');
            }
        });
    }
}
