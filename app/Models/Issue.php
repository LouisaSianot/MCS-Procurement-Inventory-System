<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Issue extends Model
{
    use HasFactory;

    public const ID_RANGE = [7001, 7999];

    protected $primaryKey = 'issue_number';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $fillable = ['issue_number', 'branch_id', 'item_id', 'quantity', 'uom', 'customer_id', 'purpose', 'user_id', 'date'];
    protected $casts = ['date' => 'date', 'quantity' => 'decimal:2'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function item(): BelongsTo { return $this->belongsTo(Item::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class, 'branch_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    protected static function booted(): void
    {
        static::creating(function (Issue $issue): void {
            if ($issue->getKey() !== null) return;
            $next = max((int) static::query()->max('issue_number') + 1, self::ID_RANGE[0]);
            if ($next > self::ID_RANGE[1]) throw new \OverflowException('IssueNumber range is exhausted.');
            $issue->setAttribute('issue_number', $next);
        });
    }
}
