<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function geOrders(): HasMany
    {
        return $this->hasMany(GEOrder::class, 'branch_id');
    }
}
