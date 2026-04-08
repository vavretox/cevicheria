<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beverage_stock_entries', function (Blueprint $table) {
            $table->enum('movement_type', ['entry', 'exit'])->default('entry')->after('user_id');
        });

        DB::table('beverage_stock_entries')
            ->whereNull('movement_type')
            ->update(['movement_type' => 'entry']);
    }

    public function down(): void
    {
        Schema::table('beverage_stock_entries', function (Blueprint $table) {
            $table->dropColumn('movement_type');
        });
    }
};
