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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->enum('payment_method', ['qris', 'cash']);
            $table->decimal('amount_paid', 15, 2);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->string('qris_transaction_id')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->enum('payment_status', ['pending', 'success', 'failed'])->default('pending');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
