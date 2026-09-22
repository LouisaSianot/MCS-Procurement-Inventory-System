<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;

trait UsesV4TableName
{
    public function getTable(): string
    {
        $table = parent::getTable();

        if (DB::connection()->getDriverName() !== 'pgsql') {
            return $table;
        }

        return [
            'suppliers' => 'SUPPLIER',
            'items' => 'ITEM',
            'branches' => 'BRANCH',
            'item_branches' => 'ITEMBRANCH',
            'purchase_orders' => 'ORDERS',
            'purchase_receipts' => 'RECEIPT',
            'users' => 'USERS',
        ][$table] ?? $table;
    }
}
