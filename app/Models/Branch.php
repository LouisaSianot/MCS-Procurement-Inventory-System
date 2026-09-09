<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\UsesV4TableName;

class Branch extends Model
{
    use HasFactory;
    use UsesV4TableName;

    protected $table = 'branches';

    protected $fillable = ['name'];

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
