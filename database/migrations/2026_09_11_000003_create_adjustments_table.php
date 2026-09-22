<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adjustments', function (Blueprint $table): void {
            $table->integer('adjustment_number')->primary();
            $table->string('adjustment_type', 20);
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('item_id')->constrained('items');
            $table->decimal('quantity', 12, 2);
            $table->string('uom', 30);
            $table->date('date');
            $table->string('purpose', 500);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            $table->index(['item_id', 'branch_id']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE adjustments ADD CONSTRAINT adjustments_number_range CHECK (adjustment_number BETWEEN 5001 AND 5999)");
            DB::statement("ALTER TABLE adjustments ADD CONSTRAINT adjustments_type_check CHECK (adjustment_type IN ('Adjust-IN', 'Adjust-OUT'))");
            DB::statement('ALTER TABLE adjustments ADD CONSTRAINT adjustments_quantity_positive CHECK (quantity > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('adjustments');
    }
};
