<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GEOrder extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ge_orders';

    protected $fillable = [
        'order_number',
        'user_id',
        'supplier_id',
        'branch_id',
        'account_code',
        'inventory_flag',
        'po_number',
        'order_date',
        'description',
        'notes',
        'status',
        'approval_status',
        'total_amount',
        'rejection_reason',
        'submitted_at',
        'approved_at',
        'approved_by',
        'cancelled_at',
    ];

    protected $casts = [
        'order_date'     => 'date',
        'submitted_at'   => 'datetime',
        'approved_at'    => 'datetime',
        'cancelled_at'   => 'datetime',
        'total_amount'   => 'decimal:2',
    ];

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PENDING   = 'pending';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public const APPROVAL_NOT_SUBMITTED   = 'not submitted';
    public const APPROVAL_PENDING_APPROVAL = 'pending approval';
    public const APPROVAL_APPROVED        = 'approved';
    public const APPROVAL_REJECTED        = 'rejected';

    public const INVENTORY_FLAG_STOCK    = 'STOCK';
    public const INVENTORY_FLAG_NONSTOCK = 'NONSTOCK';

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GEOrderItem::class, 'ge_order_id');
    }

    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPendingApproval(): bool
    {
        return $this->approval_status === self::APPROVAL_PENDING_APPROVAL;
    }

    public function recalcTotal(): void
    {
        $this->total_amount = $this->items()->sum('total');
        $this->save();
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'GE-';
        $next = (int) static::withTrashed()->max('id') + 1;

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
