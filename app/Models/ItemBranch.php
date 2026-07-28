<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemBranch extends Model
{
    use HasFactory;

    protected $fillable = ['branch', 'item_id', 'current_stock', 'unit_cost', 'location', 'reorder_level', 'reorder_quantity'];

    public function item()
    {
        return $this->belongsTo(Item::class);
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
