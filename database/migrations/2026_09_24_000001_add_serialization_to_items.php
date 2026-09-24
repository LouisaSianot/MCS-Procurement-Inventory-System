<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $itemTable = Schema::getConnection()->getDriverName() === 'pgsql' ? 'ITEM' : 'items';
        $itemBranchTable = Schema::getConnection()->getDriverName() === 'pgsql' ? 'ITEMBRANCH' : 'item_branches';

        Schema::table($itemTable, function (Blueprint $table): void {
            $table->string('model_number')->nullable();
            $table->boolean('is_serialized')->default(false);
        });

        Schema::create('item_serials', function (Blueprint $table) use ($itemTable, $itemBranchTable): void {
            $table->id();
            $table->foreignId('item_id')->constrained($itemTable)->cascadeOnDelete();
            $table->foreignId('item_branch_id')->constrained($itemBranchTable)->cascadeOnDelete();
            $table->string('serial_number');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['item_id', 'serial_number']);
            $table->index(['item_branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_serials');

        $itemTable = Schema::getConnection()->getDriverName() === 'pgsql' ? 'ITEM' : 'items';

        Schema::table($itemTable, function (Blueprint $table): void {
            $table->dropColumn(['model_number', 'is_serialized']);
        });
    }
};
