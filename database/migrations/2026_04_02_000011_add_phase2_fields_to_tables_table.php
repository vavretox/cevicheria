<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->string('zone')->nullable()->after('name');
            $table->string('reservation_name')->nullable()->after('active');
            $table->timestamp('reservation_at')->nullable()->after('reservation_name');
            $table->text('reservation_notes')->nullable()->after('reservation_at');
        });
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn([
                'zone',
                'reservation_name',
                'reservation_at',
                'reservation_notes',
            ]);
        });
    }
};
