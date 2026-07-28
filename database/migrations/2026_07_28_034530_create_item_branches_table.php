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
        Schema::create('item_branches', function (Blueprint $table) {
            $table->id();
            $table->string('branch')->default('MCS');
            $table->foreignId('item_id')->constrained('items');
            $table->integer('current_stock')->default(0); // derived — don't allow direct input in the UI
            $table->decimal('unit_cost', 10, 2)->default(0); // recalculated average cost
            $table->string('location')->nullable();
            $table->integer('reorder_level')->default(0);
            $table->integer('reorder_quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_branches');
    }
};
