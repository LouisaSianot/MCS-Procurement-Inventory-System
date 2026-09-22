<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    public const ID_RANGE = [4001, 4999];
    public const TYPES = ['STAFF', 'STUDENT', 'MCS'];

    protected $fillable = ['customer', 'customer_type', 'email'];

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function getCustomerIDAttribute(): int
    {
        return (int) $this->getKey();
    }

    protected static function booted(): void
    {
        static::creating(function (Customer $customer): void {
            if ($customer->getKey() !== null) {
                return;
            }

            $next = max((int) static::query()->max('id') + 1, self::ID_RANGE[0]);
            if ($next > self::ID_RANGE[1]) {
                throw new \OverflowException('CustomerID range is exhausted.');
            }

            $customer->setAttribute($customer->getKeyName(), $next);
        });
    }
}
