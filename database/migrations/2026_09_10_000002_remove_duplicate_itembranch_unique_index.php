<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "ITEMBRANCH" DROP CONSTRAINT IF EXISTS itembranch_item_id_branch_id_unique');
        } else {
            DB::statement('DROP INDEX IF EXISTS itembranch_item_id_branch_id_unique');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "ITEMBRANCH" ADD CONSTRAINT itembranch_item_id_branch_id_unique UNIQUE (item_id, branch_id)');
        } else {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS itembranch_item_id_branch_id_unique ON item_branches (item_id, branch_id)');
        }
    }
};