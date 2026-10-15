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
        $itemBranchTable = $driver === 'pgsql' ? 'ITEMBRANCH' : 'item_branches';
        $userTable = $driver === 'pgsql' ? 'USERS' : 'users';

        Schema::create('inventory_issues', function (Blueprint $table) use ($itemBranchTable, $userTable): void {
            $table->id();
            $table->foreignId('item_branch_id')->constrained($itemBranchTable)->restrictOnDelete();
            $table->string('type', 20);
            $table->decimal('quantity', 12, 2);
            $table->decimal('returned_quantity', 12, 2)->default(0);
            $table->foreignId('recipient_user_id')->nullable()->constrained($userTable)->nullOnDelete();
            $table->string('recipient_name')->nullable();
            $table->text('purpose');
            $table->foreignId('issued_by')->nullable()->constrained($userTable)->nullOnDelete();
            $table->timestamps();
            $table->index(['item_branch_id', 'created_at']);
        });

        Schema::create('inventory_issue_serials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_issue_id')->constrained('inventory_issues')->cascadeOnDelete();
            $table->foreignId('item_serial_id')->constrained('item_serials')->restrictOnDelete();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
            $table->unique(['inventory_issue_id', 'item_serial_id']);
        });

        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->foreignId('inventory_issue_id')->nullable()->constrained('inventory_issues')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('inventory_issue_id');
        });
        Schema::dropIfExists('inventory_issue_serials');
        Schema::dropIfExists('inventory_issues');
    }
};
