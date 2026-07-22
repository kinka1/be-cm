<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_session_id')->constrained('cashier_sessions')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->enum('type', ['cash_in', 'cash_out']);
            $table->decimal('amount', 15, 2);
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'type']);
            $table->index(['cashier_session_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashier_cash_movements');
    }
};
