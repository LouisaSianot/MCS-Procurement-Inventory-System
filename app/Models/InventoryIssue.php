<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryIssue extends Model
{
    use HasFactory;

    public const TYPE_CONSUMABLE = 'consumable';

    public const TYPE_CHECKOUT = 'checkout';

    protected $fillable = [
        'item_branch_id',
        'type',
        'quantity',
        'returned_quantity',
        'recipient_user_id',
        'recipient_name',
        'purpose',
        'issued_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'returned_quantity' => 'decimal:2',
    ];

    public function itemBranch(): BelongsTo
    {
        return $this->belongsTo(ItemBranch::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function serials(): BelongsToMany
    {
        return $this->belongsToMany(ItemSerial::class, 'inventory_issue_serials')
            ->withPivot(['returned_at'])
            ->withTimestamps();
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
