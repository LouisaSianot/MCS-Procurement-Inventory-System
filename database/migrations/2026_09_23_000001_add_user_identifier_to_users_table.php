<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = Schema::hasTable('USERS') ? 'USERS' : 'users';

        Schema::table($tableName, function (Blueprint $table) {
            $table->string('user_identifier', 36)->nullable()->after('id');
        });

        DB::table($tableName)->whereNull('user_identifier')->orderBy('id')->eachById(function (object $user) use ($tableName): void {
            DB::table($tableName)->where('id', $user->id)->update([
                'user_identifier' => (string) Str::uuid(),
            ]);
        });

        Schema::table($tableName, function (Blueprint $table) {
            $table->string('user_identifier', 36)->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        $tableName = Schema::hasTable('USERS') ? 'USERS' : 'users';

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropUnique(['user_identifier']);
            $table->dropColumn('user_identifier');
        });
    }
};
