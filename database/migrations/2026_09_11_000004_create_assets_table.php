<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table): void {
            $table->integer('asset_id')->primary();
            $table->string('asset');
            $table->string('serial_number', 100);
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->date('date');
            $table->decimal('unit_cost', 12, 2);
            $table->string('location', 255)->nullable();
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('status', 30);
            $table->string('po_number', 50);
            $table->foreignId('item_id')->constrained('items');
            $table->timestamps();
            $table->unique(['item_id', 'branch_id', 'serial_number']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE assets ADD CONSTRAINT assets_id_range CHECK (asset_id BETWEEN 3001 AND 3999)");
            DB::statement("ALTER TABLE assets ADD CONSTRAINT assets_status_check CHECK (status IN ('new', 'used', 'under repair', 'out-of-service'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
