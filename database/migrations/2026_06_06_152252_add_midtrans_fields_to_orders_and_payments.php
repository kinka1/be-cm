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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('payment_fee', 15, 2)->default(0)->after('discount');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_gateway')->nullable()->after('payment_method');
            $table->string('gateway_order_id')->nullable()->after('qris_transaction_id');
            $table->string('gateway_transaction_id')->nullable()->after('gateway_order_id');
            $table->json('gateway_response')->nullable()->after('gateway_transaction_id');
            $table->decimal('payment_fee', 15, 2)->default(0)->after('gateway_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_gateway',
                'gateway_order_id',
                'gateway_transaction_id',
                'gateway_response',
                'payment_fee',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_fee');
        });
    }
};
