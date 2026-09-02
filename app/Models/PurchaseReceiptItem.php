<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = ['purchase_receipt_id', 'purchase_order_item_id', 'quantity_received', 'unit_cost'];

    protected $casts = ['quantity_received' => 'decimal:2', 'unit_cost' => 'decimal:2'];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function inventoryMovement(): HasOne
    {
        return $this->hasOne(InventoryMovement::class);
    }
}
