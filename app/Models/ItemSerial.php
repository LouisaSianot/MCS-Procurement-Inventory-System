<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemSerial extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = ['item_id', 'item_branch_id', 'serial_number', 'status'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function itemBranch(): BelongsTo
    {
        return $this->belongsTo(ItemBranch::class);
    }
}
