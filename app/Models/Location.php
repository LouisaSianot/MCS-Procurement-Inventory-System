<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'name', 'description'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function itemBranches(): HasMany
    {
        return $this->hasMany(ItemBranch::class);
    }

    public function outboundMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'from_location_id');
    }

    public function inboundMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'to_location_id');
    }
}
