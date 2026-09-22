<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    public const ID_RANGE = [5001, 5999];

    protected $fillable = ['name', 'address', 'contact', 'payment_term', 'currency'];

    public function getSupplierIDAttribute(): int
    {
        return (int) $this->getKey();
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
