<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
            $table->index('completed_at');
            $table->index(['table_id', 'status']);
            $table->unique(['order_date', 'daily_sequence']);
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->index(['order_id', 'product_id']);
        });

        Schema::table('order_audits', function (Blueprint $table) {
            $table->index(['order_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });

        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
            $table->index('opened_at');
            $table->index('closed_at');
        });

        Schema::table('beverage_stock_entries', function (Blueprint $table) {
            $table->index(['product_id', 'created_at']);
            $table->index(['movement_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['order_date', 'daily_sequence']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['completed_at']);
            $table->dropIndex(['table_id', 'status']);
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'product_id']);
        });

        Schema::table('order_audits', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'created_at']);
            $table->dropIndex(['action', 'created_at']);
        });

        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['opened_at']);
            $table->dropIndex(['closed_at']);
        });

        Schema::table('beverage_stock_entries', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'created_at']);
            $table->dropIndex(['movement_type', 'created_at']);
        });
    }
};
