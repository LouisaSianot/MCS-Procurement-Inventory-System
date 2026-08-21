<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ge_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ge_order_id')->constrained('ge_orders');
            $table->foreignId('item_id')->nullable()->constrained('items'); // null for NON-STOCK / service lines
            $table->string('item_description'); // for STOCK: snapshot from Item; for NON-STOCK: free-text service description
            $table->string('uom')->nullable(); // typically not meaningful for services
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->timestamps();
        });
        DB::statement('ALTER TABLE ge_order_items ADD CONSTRAINT chk_quantity_non_negative CHECK (quantity >= 0)');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ge_order_items');
    }
};
