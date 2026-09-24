<?php

namespace App\Models;

use App\Models\Concerns\UsesV4TableName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    use UsesV4TableName;

    protected $table = 'items';

    public const ID_RANGE = [1001, 1999];

    protected $fillable = ['description', 'uom', 'category', 'sub_category', 'supplier_id', 'model_number', 'is_serialized'];

    protected $casts = ['is_serialized' => 'boolean'];

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

    public function itemBranches()
    {
        return $this->hasMany(ItemBranch::class);
    }

    public function serials()
    {
        return $this->hasMany(ItemSerial::class);
    }
}
