<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeOrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['ge_order_id', 'item_id', 'item_description', 'uom', 'quantity', 'unit_cost', 'total_cost'];

    public function order()
    {
        return $this->belongsTo(GeOrder::class, 'ge_order_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    protected static function booted()
    {
        static::saving(function ($line) {
            $line->total_cost = $line->quantity * $line->unit_cost;

            if ($line->quantity < 0) {
                throw new \InvalidArgumentException('Quantity cannot be negative.');
            }
        });
    }
}