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
            $table->decimal('cash_paid_amount', 10, 2)->nullable()->after('amount_received');
            $table->decimal('qr_paid_amount', 10, 2)->nullable()->after('cash_paid_amount');
        });

        DB::table('orders')
            ->where('payment_method', 'cash')
            ->update([
                'cash_paid_amount' => DB::raw('COALESCE(amount_received, 0)'),
                'qr_paid_amount' => 0,
            ]);

        DB::table('orders')
            ->where('payment_method', 'qr')
            ->update([
                'cash_paid_amount' => 0,
                'qr_paid_amount' => DB::raw('COALESCE(total, 0)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cash_paid_amount', 'qr_paid_amount']);
        });
    }
};
