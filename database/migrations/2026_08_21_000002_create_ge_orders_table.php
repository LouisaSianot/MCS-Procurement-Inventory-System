<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ge_orders')) {
            Schema::table('ge_orders', function (Blueprint $table) {
                if (! Schema::hasColumn('ge_orders', 'order_number')) $table->string('order_number', 20)->nullable();
                if (! Schema::hasColumn('ge_orders', 'user_id')) $table->foreignId('user_id')->nullable();
                if (! Schema::hasColumn('ge_orders', 'branch_id')) $table->foreignId('branch_id')->nullable();
                if (! Schema::hasColumn('ge_orders', 'order_date')) $table->date('order_date')->nullable();
                if (! Schema::hasColumn('ge_orders', 'description')) $table->string('description', 500)->nullable();
                if (! Schema::hasColumn('ge_orders', 'notes')) $table->text('notes')->nullable();
                if (! Schema::hasColumn('ge_orders', 'approval_status')) $table->string('approval_status', 40)->default('not submitted');
                if (! Schema::hasColumn('ge_orders', 'total_amount')) $table->decimal('total_amount', 12, 2)->default(0);
                if (! Schema::hasColumn('ge_orders', 'rejection_reason')) $table->text('rejection_reason')->nullable();
                if (! Schema::hasColumn('ge_orders', 'submitted_at')) $table->timestamp('submitted_at')->nullable();
                if (! Schema::hasColumn('ge_orders', 'approved_at')) $table->timestamp('approved_at')->nullable();
                if (! Schema::hasColumn('ge_orders', 'approved_by')) $table->foreignId('approved_by')->nullable();
                if (! Schema::hasColumn('ge_orders', 'cancelled_at')) $table->timestamp('cancelled_at')->nullable();
                if (! Schema::hasColumn('ge_orders', 'deleted_at')) $table->softDeletes();
            });

            DB::statement("UPDATE ge_orders SET order_number = 'GE-' || LPAD(id::text, 5, '0'), user_id = originator_id, branch_id = 201, order_date = date, description = 'Legacy GE order', inventory_flag = CASE WHEN inventory_flag = 'NON-STOCK' THEN 'NON-STOCK' ELSE inventory_flag END, status = LOWER(status) WHERE order_number IS NULL");
            DB::statement('CREATE UNIQUE INDEX ge_orders_order_number_unique ON ge_orders (order_number)');
            return;
        }

        Schema::create('ge_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('account_code', 50);
            $table->string('inventory_flag', 20);
            $table->string('po_number', 50)->nullable();
            $table->date('order_date');
            $table->string('description', 500);
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('draft');
            $table->string('approval_status', 40)->default('not submitted');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('cancelled_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ge_orders');
    }
};
