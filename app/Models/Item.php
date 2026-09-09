<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\UsesV4TableName;

class Item extends Model
{
    use HasFactory;
    use UsesV4TableName;

    protected $table = 'items';

    protected $fillable = ['description', 'uom', 'category', 'sub_category', 'supplier_id'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branches()
    {
        return $this->hasMany(ItemBranch::class);
    }
}
