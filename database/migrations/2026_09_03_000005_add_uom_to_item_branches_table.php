<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_branches', function (Blueprint $table) {
            $table->string('uom', 30)->nullable();
        });

        DB::table('item_branches')
            ->select(['id', 'item_id'])
            ->whereNull('uom')
            ->orderBy('id')
            ->each(function (object $itemBranch): void {
                $uom = DB::table('items')->where('id', $itemBranch->item_id)->value('uom');

                if ($uom) {
                    DB::table('item_branches')->where('id', $itemBranch->id)->update(['uom' => $uom]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('item_branches', function (Blueprint $table) {
            $table->dropColumn('uom');
        });
    }
};
