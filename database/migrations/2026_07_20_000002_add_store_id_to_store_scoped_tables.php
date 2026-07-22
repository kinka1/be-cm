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

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->nullOnDelete();
        });
        DB::table('products')->whereNull('store_id')->update(['store_id' => $defaultStoreId]);

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->nullOnDelete();
            $table->index(['store_id', 'order_date']);
        });
        DB::table('orders')->whereNull('store_id')->update(['store_id' => $defaultStoreId]);

        Schema::table('calon_mantu', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->nullOnDelete();
            $table->index(['store_id', 'status']);
        });
        DB::table('calon_mantu')->whereNull('store_id')->update(['store_id' => $defaultStoreId]);

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->nullOnDelete();
        });
        DB::table('employees')->whereNull('store_id')->update(['store_id' => $defaultStoreId]);

        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->nullOnDelete();
            $table->index(['store_id', 'transaction_date']);
        });
        DB::table('stock_transactions')->whereNull('store_id')->update(['store_id' => $defaultStoreId]);
    }

    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'transaction_date']);
            $table->dropConstrainedForeignId('store_id');
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_id');
        });
        Schema::table('calon_mantu', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'status']);
            $table->dropConstrainedForeignId('store_id');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'order_date']);
            $table->dropConstrainedForeignId('store_id');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_id');
        });
    }
};
