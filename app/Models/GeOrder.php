<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeOrder extends Model
{
    use HasFactory;

    protected $fillable = ['inventory_flag', 'po_number', 'supplier_id', 'date', 'originator_id', 'approver_id', 'branch', 'account_code', 'status'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function originator()
    {
        return $this->belongsTo(User::class, 'originator_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function items()
    {
        return $this->hasMany(GeOrderItem::class);
    }

    protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->approver_id)) {
                $hos = User::role('HoS')->first();
                if ($hos) {
                    $order->approver_id = $hos->id;
                }
            }
        });
    }
}