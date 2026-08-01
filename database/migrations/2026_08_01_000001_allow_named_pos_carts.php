<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_carts', function (Blueprint $table) {
            $table->index('user_id', 'pos_carts_user_id_cart_index');
        });

        Schema::table('pos_carts', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'store_id']);
            $table->string('name')->default('Keranjang')->after('store_id');
            $table->string('status', 20)->default('active')->after('name');
            $table->index(['user_id', 'store_id', 'status']);
        });

        DB::table('pos_carts')->whereNull('name')->update(['name' => 'Keranjang']);
    }

    public function down(): void
    {
        Schema::table('pos_carts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'store_id', 'status']);
            $table->dropColumn(['name', 'status']);
            $table->unique(['user_id', 'store_id']);
            $table->dropIndex('pos_carts_user_id_cart_index');
        });
    }
};
