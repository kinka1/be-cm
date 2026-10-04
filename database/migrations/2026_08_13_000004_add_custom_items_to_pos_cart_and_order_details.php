<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_cart_items', function (Blueprint $table): void {
            $table->string('item_type', 20)->default('menu')->after('pos_cart_id');
            $table->string('custom_name')->nullable()->after('product_id');
            $table->decimal('custom_unit_price', 15, 2)->nullable()->after('custom_name');
        });

        Schema::table('pos_cart_items', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->foreignId('product_id')->nullable()->change();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::table('order_details', function (Blueprint $table): void {
            $table->string('item_type', 20)->default('menu')->after('order_id');
            $table->string('item_name')->nullable()->after('product_id');
        });

        Schema::table('order_details', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->foreignId('product_id')->nullable()->change();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });

        DB::table('pos_cart_items')->whereNull('item_type')->update(['item_type' => 'menu']);
        DB::table('order_details')->whereNull('item_type')->update(['item_type' => 'menu']);
    }

    public function down(): void
    {
        DB::table('pos_cart_items')->where('item_type', 'custom')->delete();
        DB::table('order_details')->where('item_type', 'custom')->delete();

        Schema::table('order_details', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->foreignId('product_id')->nullable(false)->change();
            $table->foreign('product_id')->references('id')->on('products');
            $table->dropColumn(['item_type', 'item_name']);
        });

        Schema::table('pos_cart_items', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->foreignId('product_id')->nullable(false)->change();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->dropColumn(['item_type', 'custom_name', 'custom_unit_price']);
        });
    }
};
