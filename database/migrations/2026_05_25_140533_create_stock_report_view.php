<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("DROP VIEW IF EXISTS stock_report");
        DB::statement(<<<'SQL'
            CREATE VIEW stock_report AS
            SELECT
                p.id AS product_id,
                p.product_name,
                COALESCE(SUM(CASE WHEN st.transaction_type = 'in' THEN st.quantity END), 0) AS stock_in_total,
                COALESCE(SUM(CASE WHEN st.transaction_type = 'out' THEN st.quantity END), 0) AS stock_out_total,
                p.current_stock,
                MAX(st.transaction_date) AS last_transaction_date
            FROM products p
            LEFT JOIN stock_transactions st ON st.product_id = p.id
            GROUP BY p.id, p.product_name, p.current_stock
            SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS stock_report");
    }
};
