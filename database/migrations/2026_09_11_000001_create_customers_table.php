<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->integer('id')->primary();
            $table->string('customer');
            $table->string('customer_type', 20);
            $table->string('email')->unique();
            $table->timestamps();
            $table->index('customer_type');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE customers ADD CONSTRAINT customers_id_range CHECK (id BETWEEN 4001 AND 4999)");
            DB::statement("ALTER TABLE customers ADD CONSTRAINT customers_type_check CHECK (customer_type IN ('STAFF', 'STUDENT', 'MCS'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
