<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        $branchTable = $driver === 'pgsql' ? 'BRANCH' : 'branches';
        $itemBranchTable = $driver === 'pgsql' ? 'ITEMBRANCH' : 'item_branches';

        Schema::create('locations', function (Blueprint $table) use ($branchTable): void {
            $table->id();
            $table->foreignId('branch_id')->constrained($branchTable)->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['branch_id', 'name']);
            $table->unique(['id', 'branch_id']);
        });

        Schema::table($itemBranchTable, function (Blueprint $table): void {
            $table->foreignId('location_id')->nullable();
        });

        $branchIdsByName = DB::table($branchTable)->pluck('id', 'name');
        $fallbackBranchId = DB::table($branchTable)->min('id');
        $locationsByKey = [];

        DB::table($itemBranchTable)
            ->select(['id', 'branch_id', 'branch', 'location'])
            ->orderBy('id')
            ->each(function (object $itemBranch) use ($branchIdsByName, $fallbackBranchId, &$locationsByKey, $itemBranchTable): void {
                $branchId = $itemBranch->branch_id
                    ?? $branchIdsByName->get($itemBranch->branch)
                    ?? $fallbackBranchId;

                if (! $branchId) {
                    throw new RuntimeException('Cannot backfill inventory locations because no branches exist.');
                }

                $name = trim((string) $itemBranch->location) ?: 'Unassigned';
                $key = $branchId.':'.mb_strtolower($name);

                if (! isset($locationsByKey[$key])) {
                    $now = now();
                    $locationsByKey[$key] = DB::table('locations')->insertGetId([
                        'branch_id' => $branchId,
                        'name' => $name,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                DB::table($itemBranchTable)->where('id', $itemBranch->id)->update([
                    'branch_id' => $branchId,
                    'location_id' => $locationsByKey[$key],
                ]);
            });

        $duplicate = DB::table($itemBranchTable)
            ->select(['item_id', 'location_id'])
            ->selectRaw('COUNT(*) AS row_count')
            ->groupBy('item_id', 'location_id')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicate) {
            throw new RuntimeException('Duplicate item inventory rows map to the same branch location. Resolve those rows before rerunning this migration.');
        }

        Schema::table($itemBranchTable, function (Blueprint $table) use ($branchTable): void {
            $table->dropUnique('item_branches_item_branch_unique');
            $table->dropForeign('item_branches_branch_id_foreign');
            $table->unsignedBigInteger('branch_id')->nullable(false)->change();
            $table->unsignedBigInteger('location_id')->nullable(false)->change();
            $table->decimal('current_stock', 12, 2)->default(0)->change();
            $table->foreign('branch_id', 'item_branches_branch_id_foreign')
                ->references('id')->on($branchTable)->restrictOnDelete();
            $table->foreign(['location_id', 'branch_id'], 'item_branches_location_branch_foreign')
                ->references(['id', 'branch_id'])->on('locations')->restrictOnDelete();
            $table->unique(['item_id', 'location_id'], 'item_branches_item_location_unique');
        });

        Schema::table('inventory_movements', function (Blueprint $table) use ($driver): void {
            $userTable = $driver === 'pgsql' ? 'USERS' : 'users';
            $table->foreignId('from_location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $table->foreignId('performed_by')->nullable()->constrained($userTable)->nullOnDelete();
            $table->decimal('destination_stock_after', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        $branchTable = $driver === 'pgsql' ? 'BRANCH' : 'branches';
        $itemBranchTable = $driver === 'pgsql' ? 'ITEMBRANCH' : 'item_branches';
        $duplicate = DB::table($itemBranchTable)
            ->select(['item_id', 'branch_id'])
            ->selectRaw('COUNT(*) AS row_count')
            ->groupBy('item_id', 'branch_id')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicate) {
            throw new RuntimeException('Cannot roll back location tracking while an item has stock in multiple locations in the same branch. Consolidate inventory first.');
        }

        if (DB::table('inventory_movements')->where('type', 'transfer')->exists()) {
            throw new RuntimeException('Cannot roll back location tracking while transfer audit records exist. Export or archive the transfer history first.');
        }

        if (DB::table($itemBranchTable)->whereRaw('current_stock <> CAST(current_stock AS INTEGER)')->exists()) {
            throw new RuntimeException('Cannot roll back location tracking while inventory contains fractional stock. Adjust it to whole units first.');
        }

        Schema::table($itemBranchTable, function (Blueprint $table) use ($branchTable): void {
            $table->dropUnique('item_branches_item_location_unique');
            $table->dropForeign('item_branches_location_branch_foreign');
            $table->dropForeign('item_branches_branch_id_foreign');
            $table->dropColumn('location_id');
            $table->unsignedBigInteger('branch_id')->nullable()->change();
            $table->integer('current_stock')->default(0)->change();
            $table->foreign('branch_id', 'item_branches_branch_id_foreign')
                ->references('id')->on($branchTable)->nullOnDelete();
            $table->unique(['item_id', 'branch_id'], 'item_branches_item_branch_unique');
        });

        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('from_location_id');
            $table->dropConstrainedForeignId('to_location_id');
            $table->dropConstrainedForeignId('performed_by');
            $table->dropColumn('destination_stock_after');
        });

        Schema::dropIfExists('locations');
    }
};
