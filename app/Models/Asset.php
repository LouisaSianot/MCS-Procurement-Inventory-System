<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory;

    public const ID_RANGE = [3001, 3999];
    public const STATUSES = ['new', 'used', 'under repair', 'out-of-service'];

    protected $primaryKey = 'asset_id';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $fillable = ['asset_id', 'asset', 'serial_number', 'brand', 'model', 'date', 'unit_cost', 'location', 'branch_id', 'status', 'po_number', 'item_id'];
    protected $casts = ['date' => 'date', 'unit_cost' => 'decimal:2'];

    public function item(): BelongsTo { return $this->belongsTo(Item::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class, 'branch_id'); }

    protected static function booted(): void
    {
        static::creating(function (Asset $asset): void {
            if ($asset->getKey() !== null) return;
            $next = max((int) static::query()->max('asset_id') + 1, self::ID_RANGE[0]);
            if ($next > self::ID_RANGE[1]) throw new \OverflowException('AssetID range is exhausted.');
            $asset->setAttribute('asset_id', $next);
        });
    }
}
