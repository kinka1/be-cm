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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('quantity', 12, 4);
            $table->enum('adjustment_type', ['increase', 'decrease'])->default('increase');
            $table->foreignId('requested_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('reason')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('stock_transaction_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
