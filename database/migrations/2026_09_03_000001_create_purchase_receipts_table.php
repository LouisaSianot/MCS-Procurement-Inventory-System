<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 30)->unique();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders');
            $table->foreignId('received_by')->constrained('users');
            $table->date('received_at');
            $table->string('supplier_delivery_reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipts');
    }
};
