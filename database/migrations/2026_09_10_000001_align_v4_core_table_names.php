<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        $renames = [
            'suppliers' => 'SUPPLIER',
            'items' => 'ITEM',
            'branches' => 'BRANCH',
            'item_branches' => 'ITEMBRANCH',
            'purchase_orders' => 'ORDERS',
            'purchase_receipts' => 'RECEIPT',
            'users' => 'USERS',
        ];

        foreach ($renames as $from => $to) {
            if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        $renames = [
            'SUPPLIER' => 'suppliers',
            'ITEM' => 'items',
            'BRANCH' => 'branches',
            'ITEMBRANCH' => 'item_branches',
            'ORDERS' => 'purchase_orders',
            'RECEIPT' => 'purchase_receipts',
            'USERS' => 'users',
        ];

        foreach ($renames as $from => $to) {
            if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }
    }
};
