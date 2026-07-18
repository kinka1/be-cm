<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('system_stock', 12, 4)->default(0);
            $table->decimal('physical_stock', 12, 4)->default(0);
            $table->decimal('difference', 12, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['stock_opname_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
    }
};
