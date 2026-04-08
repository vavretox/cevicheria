<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->decimal('opening_amount', 10, 2);
            $table->text('opening_note')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->decimal('expected_amount', 10, 2)->nullable();
            $table->decimal('counted_amount', 10, 2)->nullable();
            $table->decimal('difference_amount', 10, 2)->nullable();
            $table->text('closing_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_sessions');
    }
};
