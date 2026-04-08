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
            $table->date('order_date')->nullable()->after('table_number');
            $table->unsignedInteger('daily_sequence')->nullable()->after('order_date');
        });

        $orders = DB::table('orders')
            ->select('id', 'created_at')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $sequencesByDate = [];

        foreach ($orders as $order) {
            $orderDate = $order->created_at
                ? \Illuminate\Support\Carbon::parse($order->created_at)->toDateString()
                : now()->toDateString();

            $sequencesByDate[$orderDate] = ($sequencesByDate[$orderDate] ?? 0) + 1;

            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'order_date' => $orderDate,
                    'daily_sequence' => $sequencesByDate[$orderDate],
                ]);
        }

    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_date', 'daily_sequence']);
        });
    }
};
