<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'po_number', 'ge_order_id', 'supplier_id', 'branch_id', 'user_id',
        'order_date', 'expected_delivery_date', 'notes', 'status',
        'total_amount', 'ordered_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'ordered_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ORDERED = 'ordered';

    public const STATUS_BACKORDER = 'backorder';

    public const STATUS_PARTIALLY_RECEIVED = 'partially received';

    public const STATUS_FULLY_RECEIVED = 'fully received';

    public const STATUS_CANCELLED = 'cancelled';

    public function geOrder(): BelongsTo
    {
        return $this->belongsTo(GEOrder::class, 'ge_order_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function isEditable(): bool
    {
        return ! in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_FULLY_RECEIVED], true);
    }

    public static function generateNumber(): string
    {
        return 'PO-'.str_pad((string) ((int) static::withTrashed()->max('id') + 1), 5, '0', STR_PAD_LEFT);
    }
}
