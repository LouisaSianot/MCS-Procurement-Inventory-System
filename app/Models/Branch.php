<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    public const ID_RANGE = [201, 299];

    protected $fillable = ['name'];

    public function getBranchIDAttribute(): int
    {
        return (int) $this->getKey();
    }

    public function geOrders(): HasMany
    {
        return $this->hasMany(GEOrder::class, 'branch_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function itemBranches(): HasMany
    {
        return $this->hasMany(ItemBranch::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
