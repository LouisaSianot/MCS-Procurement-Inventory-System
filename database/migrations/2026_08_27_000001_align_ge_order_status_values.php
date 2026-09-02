<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ge_orders')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE ge_orders DROP CONSTRAINT IF EXISTS ge_orders_status_check');
        }

        DB::statement("UPDATE ge_orders SET status = CASE LOWER(status) WHEN 'pending' THEN 'pending' WHEN 'approved' THEN 'approved' WHEN 'rejected' THEN 'rejected' WHEN 'cancelled' THEN 'cancelled' WHEN 'ordered' THEN 'approved' WHEN 'received' THEN 'approved' WHEN 'complete' THEN 'approved' ELSE 'draft' END");

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE ge_orders ADD CONSTRAINT ge_orders_status_check CHECK (status IN ('draft', 'pending', 'approved', 'rejected', 'cancelled'))");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('ge_orders')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE ge_orders DROP CONSTRAINT IF EXISTS ge_orders_status_check');
            DB::statement("ALTER TABLE ge_orders ADD CONSTRAINT ge_orders_status_check CHECK (status IN ('Pending', 'Approved', 'Rejected', 'Cancelled', 'Ordered', 'Received', 'Complete'))");
        }
    }
};
