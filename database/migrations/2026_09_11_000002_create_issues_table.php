<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table): void {
            $table->integer('issue_number')->primary();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('item_id')->constrained('items');
            $table->decimal('quantity', 12, 2);
            $table->string('uom', 30);
            $table->foreignId('customer_id')->constrained('customers');
            $table->string('purpose', 500);
            $table->foreignId('user_id')->constrained('users');
            $table->date('date');
            $table->timestamps();
            $table->index(['item_id', 'branch_id']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE issues ADD CONSTRAINT issues_number_range CHECK (issue_number BETWEEN 7001 AND 7999)");
            DB::statement('ALTER TABLE issues ADD CONSTRAINT issues_quantity_positive CHECK (quantity > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
