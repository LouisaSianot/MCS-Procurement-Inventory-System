<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ge_orders', function (Blueprint $table) {
            $table->id();
            $table->enum('inventory_flag', ['STOCK', 'NON-STOCK']);
            $table->string('po_number')->nullable();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->date('date');
            $table->foreignId('originator_id')->constrained('users');
            $table->foreignId('approver_id')->nullable()->constrained('users');
            $table->string('branch');
            $table->string('account_code');
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Cancelled', 'Ordered', 'Received', 'Complete'])
              ->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ge_orders');
    }
};
