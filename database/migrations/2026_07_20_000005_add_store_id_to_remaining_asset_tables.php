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

        foreach (['suppliers', 'purchase_orders', 'stock_opnames', 'stock_adjustments', 'product_batches'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->nullOnDelete();
                $table->index('store_id');
            });

            DB::table($tableName)->whereNull('store_id')->update(['store_id' => $defaultStoreId]);
        }
    }

    public function down(): void
    {
        foreach (['product_batches', 'stock_adjustments', 'stock_opnames', 'purchase_orders', 'suppliers'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex(['store_id']);
                $table->dropConstrainedForeignId('store_id');
            });
        }
    }
};
