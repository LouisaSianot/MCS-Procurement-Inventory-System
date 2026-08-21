<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GEOrderItem extends Model
{
    use HasFactory;

    protected $table = 'ge_order_items';

    protected $fillable = [
        'ge_order_id',
        'item_id',
        'description',
        'unit',
        'quantity',
        'unit_price',
        'total',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total'      => 'decimal:2',
    ];

    public function geOrder(): BelongsTo
    {
        return $this->belongsTo(GEOrder::class, 'ge_order_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function recalcTotal(): void
    {
        $this->total = (float) $this->quantity * (float) $this->unit_price;
    }
}
