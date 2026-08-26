<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ge_order_items')) {
            Schema::table('ge_order_items', function (Blueprint $table) {
                $table->string('description')->nullable();
                $table->string('unit', 30)->nullable();
                $table->decimal('unit_price', 12, 2)->nullable();
                $table->decimal('total', 12, 2)->nullable();
            });

            DB::statement('UPDATE ge_order_items SET description = item_description, unit = uom, unit_price = unit_cost, total = total_cost WHERE description IS NULL');
            return;
        }

        Schema::create('ge_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ge_order_id')->constrained('ge_orders')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('description');
            $table->string('unit', 30)->nullable();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ge_order_items');
    }
};
