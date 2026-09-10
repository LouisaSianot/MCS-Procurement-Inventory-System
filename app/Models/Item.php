<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    public const ID_RANGE = [1001, 1999];

    protected $fillable = ['description', 'uom', 'category', 'sub_category', 'supplier_id'];

    public function getItemIDAttribute(): int
    {
        return (int) $this->getKey();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branches()
    {
        return $this->hasMany(ItemBranch::class);
    }
}
