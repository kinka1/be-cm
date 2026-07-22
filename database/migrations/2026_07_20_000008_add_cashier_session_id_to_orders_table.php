<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('cashier_session_id')->nullable()->after('employee_id')->constrained('cashier_sessions')->nullOnDelete();
            $table->index(['cashier_session_id', 'payment_method']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['cashier_session_id', 'payment_method']);
            $table->dropConstrainedForeignId('cashier_session_id');
        });
    }
};
