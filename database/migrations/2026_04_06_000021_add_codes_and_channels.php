<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'code')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('code')->nullable()->after('name');
            });
        }

        if (!Schema::hasColumn('users', 'order_channel')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('order_channel', ['table', 'delivery'])
                    ->default('table')
                    ->after('role');
            });
        }

        $usedCodes = [];
        $categories = DB::table('categories')->select('id', 'name')->orderBy('id')->get();

        foreach ($categories as $category) {
            $baseCode = Str::slug((string) $category->name, '_');
            $baseCode = $baseCode !== '' ? $baseCode : 'categoria';
            $candidate = $baseCode;
            $suffix = 2;

            while (in_array($candidate, $usedCodes, true)) {
                $candidate = $baseCode . '_' . $suffix;
                $suffix++;
            }

            $usedCodes[] = $candidate;

            DB::table('categories')
                ->where('id', $category->id)
                ->update(['code' => $candidate]);
        }

        DB::table('users')
            ->where('role', 'mesero')
            ->whereRaw('LOWER(name) LIKE ?', ['%delivery%'])
            ->update(['order_channel' => User::ORDER_CHANNEL_DELIVERY]);

        if (!$this->indexExists('categories', 'categories_code_unique')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unique('code');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('categories', 'categories_code_unique')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropUnique('categories_code_unique');
            });
        }

        if (Schema::hasColumn('categories', 'code')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }

        if (Schema::hasColumn('users', 'order_channel')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('order_channel');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
};
