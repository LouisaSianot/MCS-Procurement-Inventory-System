<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    public const TYPE_RECEIPT = 'receipt';

    public const TYPE_TRANSFER = 'transfer';

    protected $fillable = ['item_branch_id', 'purchase_receipt_item_id', 'type', 'quantity', 'unit_cost', 'stock_after', 'from_location_id', 'to_location_id', 'performed_by', 'destination_stock_after'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'stock_after' => 'decimal:2',
        'destination_stock_after' => 'decimal:2',
    ];

    public function itemBranch(): BelongsTo
    {
        return $this->belongsTo(ItemBranch::class);
    }

    public function purchaseReceiptItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceiptItem::class);
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
