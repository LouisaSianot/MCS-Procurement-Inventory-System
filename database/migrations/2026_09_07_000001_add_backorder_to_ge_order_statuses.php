<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ge_orders') || DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE ge_orders DROP CONSTRAINT IF EXISTS ge_orders_status_check');
        DB::statement("ALTER TABLE ge_orders ADD CONSTRAINT ge_orders_status_check CHECK (status IN ('draft', 'pending', 'approved', 'rejected', 'cancelled', 'ordered', 'received', 'backorder', 'complete'))");
    }

    public function down(): void
    {
        if (! Schema::hasTable('ge_orders') || DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE ge_orders DROP CONSTRAINT IF EXISTS ge_orders_status_check');
        DB::statement("ALTER TABLE ge_orders ADD CONSTRAINT ge_orders_status_check CHECK (status IN ('draft', 'pending', 'approved', 'rejected', 'cancelled'))");
    }
};
