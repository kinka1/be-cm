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
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->enum('transaction_type', ['in', 'out', 'adjustment']);
            $table->decimal('quantity', 12, 4);
            $table->enum('reference_type', ['purchase', 'sale', 'adjustment']);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->dateTime('transaction_date')->useCurrent();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
