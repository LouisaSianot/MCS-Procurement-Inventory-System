<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 20)->unique();
            $table->foreignId('ge_order_id')->unique()->constrained('ge_orders');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('user_id')->constrained('users');
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('draft');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamp('ordered_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
