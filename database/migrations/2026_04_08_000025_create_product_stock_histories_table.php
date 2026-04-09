<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_stock_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('stock_date');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('change_type', 50)->default('daily_set');
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['stock_date', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stock_histories');
    }
};
