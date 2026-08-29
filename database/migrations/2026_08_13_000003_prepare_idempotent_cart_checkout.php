<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_cart_items', function (Blueprint $table): void {
            $table->index('pos_cart_id', 'pos_cart_items_pos_cart_id_index');
            $table->dropUnique('pos_cart_items_pos_cart_id_product_id_unique');
        });

        Schema::create('checkout_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('cart_id')->nullable()->constrained('pos_carts')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('idempotency_key');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->enum('status', ['processing', 'success', 'failed'])->default('processing');
            $table->string('request_hash')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'user_id', 'idempotency_key']);
            $table->index(['cart_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_requests');

        Schema::table('pos_cart_items', function (Blueprint $table): void {
            $table->unique(['pos_cart_id', 'product_id']);
            $table->dropIndex('pos_cart_items_pos_cart_id_index');
        });
    }
};
