<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_branches', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
        });

        $branchIdsByName = DB::table('branches')->pluck('id', 'name');

        DB::table('item_branches')
            ->select(['id', 'branch'])
            ->whereNull('branch_id')
            ->orderBy('id')
            ->each(function (object $itemBranch) use ($branchIdsByName): void {
                $branchId = $branchIdsByName->get($itemBranch->branch);

                if ($branchId) {
                    DB::table('item_branches')->where('id', $itemBranch->id)->update(['branch_id' => $branchId]);
                }
            });

        Schema::table('item_branches', function (Blueprint $table) {
            $table->unique(['item_id', 'branch_id'], 'item_branches_item_branch_unique');
        });
    }

    public function down(): void
    {
        Schema::table('item_branches', function (Blueprint $table) {
            $table->dropUnique('item_branches_item_branch_unique');
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
