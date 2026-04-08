<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('table_id')->nullable()->after('cashier_id')->constrained('tables');
        });

        $existingNames = DB::table('orders')
            ->whereNotNull('table_number')
            ->whereRaw("TRIM(table_number) <> ''")
            ->distinct()
            ->pluck('table_number');

        foreach ($existingNames as $name) {
            DB::table('tables')->updateOrInsert(
                ['name' => $name],
                [
                    'active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $orders = DB::table('orders')
            ->select('id', 'table_number')
            ->whereNotNull('table_number')
            ->whereRaw("TRIM(table_number) <> ''")
            ->get();

        foreach ($orders as $order) {
            $tableId = DB::table('tables')
                ->where('name', $order->table_number)
                ->value('id');

            if ($tableId) {
                DB::table('orders')
                    ->where('id', $order->id)
                    ->update(['table_id' => $tableId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('table_id');
        });
    }
};
