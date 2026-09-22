<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adjustment extends Model
{
    use HasFactory;

    public const ID_RANGE = [5001, 5999];
    public const TYPES = ['Adjust-IN', 'Adjust-OUT'];

    protected $primaryKey = 'adjustment_number';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $fillable = ['adjustment_number', 'adjustment_type', 'branch_id', 'item_id', 'quantity', 'uom', 'date', 'purpose', 'user_id'];
    protected $casts = ['date' => 'date', 'quantity' => 'decimal:2'];

    public function item(): BelongsTo { return $this->belongsTo(Item::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class, 'branch_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    protected static function booted(): void
    {
        static::creating(function (Adjustment $adjustment): void {
            if ($adjustment->getKey() !== null) return;
            $next = max((int) static::query()->max('adjustment_number') + 1, self::ID_RANGE[0]);
            if ($next > self::ID_RANGE[1]) throw new \OverflowException('AdjustmentNumber range is exhausted.');
            $adjustment->setAttribute('adjustment_number', $next);
        });
    }
}
