<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['ge_order_items', 'purchase_order_items'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'description')) {
                continue;
            }

            DB::table($table)
                ->where(function ($query) {
                    $query->whereNull('description')->orWhere('description', '');
                })
                ->whereNotNull('item_id')
                ->orderBy('id')
                ->eachById(function ($line) use ($table) {
                    $description = DB::table('items')->where('id', $line->item_id)->value('description') ?: 'Item';
                    DB::table($table)->where('id', $line->id)->update(['description' => $description]);
                });
        }
    }

    public function down(): void
    {
        // Descriptions are retained when rolling back this data migration.
    }
};
