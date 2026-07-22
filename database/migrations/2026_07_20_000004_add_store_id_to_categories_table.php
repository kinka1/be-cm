<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $defaultStoreId = DB::table('stores')->where('code', 'MAIN')->value('id');

        $hasCategoryNameUnique = collect(DB::select("SHOW INDEX FROM categories WHERE Key_name = 'categories_category_name_unique'"))->isNotEmpty();

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->nullOnDelete();
        });

        if ($hasCategoryNameUnique) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropUnique(['category_name']);
            });
        }

        DB::table('categories')->whereNull('store_id')->update(['store_id' => $defaultStoreId]);

        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['store_id', 'category_name']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['store_id', 'category_name']);
            $table->dropConstrainedForeignId('store_id');
            $table->unique('category_name');
        });
    }
};
