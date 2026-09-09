<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\UsesV4TableName;

class Supplier extends Model
{
    use HasFactory;
    use UsesV4TableName;

    protected $table = 'suppliers';

    protected $fillable = ['name', 'address', 'contact', 'payment_term', 'currency'];

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
