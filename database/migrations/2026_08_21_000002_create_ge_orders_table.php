<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
