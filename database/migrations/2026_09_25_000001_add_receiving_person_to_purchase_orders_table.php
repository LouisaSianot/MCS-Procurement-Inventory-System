<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = Schema::getConnection()->getDriverName() == 'pgsql'
            ? 'ORDERS'
            : 'purchase_orders';

        Schema::table($tableName, function (Blueprint $table) {
            $table->string('receiving_person_name')->nullable();
            $table->string('receiving_person_position')->nullable();
            $table->string('receiving_person_branch')->nullable();
            $table->string('receiving_person_phone')->nullable();
            $table->string('receiving_person_email')->nullable();
        });
    }

    public function down(): void
    {
        $tableName = Schema::getConnection()->getDriverName() == 'pgsql'
            ? 'ORDERS'
            : 'purchase_orders';

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn([
                'receiving_person_name',
                'receiving_person_position',
                'receiving_person_branch',
                'receiving_person_phone',
                'receiving_person_email',
            ]);
        });
    }
};
