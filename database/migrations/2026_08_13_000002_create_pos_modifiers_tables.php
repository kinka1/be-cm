<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price_delta', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['store_id', 'name']);
        });

        Schema::create('pos_category_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('modifier_id')->constrained('pos_modifiers')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['category_id', 'modifier_id']);
        });

        Schema::create('pos_cart_item_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_item_id')->constrained('pos_cart_items')->cascadeOnDelete();
            $table->foreignId('modifier_id')->nullable()->constrained('pos_modifiers')->nullOnDelete();
            $table->string('name');
            $table->decimal('price_delta', 15, 2)->default(0);
            $table->decimal('quantity', 12, 4)->default(1);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['cart_item_id', 'modifier_id']);
        });

        Schema::create('order_detail_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_detail_id')->constrained('order_details')->cascadeOnDelete();
            $table->foreignId('modifier_id')->nullable()->constrained('pos_modifiers')->nullOnDelete();
            $table->string('name');
            $table->decimal('price_delta', 15, 2)->default(0);
            $table->decimal('quantity', 12, 4)->default(1);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_detail_modifiers');
        Schema::dropIfExists('pos_cart_item_modifiers');
        Schema::dropIfExists('pos_category_modifiers');
        Schema::dropIfExists('pos_modifiers');
    }
};
