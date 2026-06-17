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
        DB::unprepared('DROP TRIGGER IF EXISTS trg_orders_after_update');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_orders_after_update
            AFTER UPDATE ON orders
            FOR EACH ROW
            BEGIN
                IF NEW.order_status = 'completed' AND OLD.order_status <> 'completed' THEN
                    INSERT INTO stock_transactions (product_id, transaction_type, quantity, reference_type, reference_id, employee_id, notes, transaction_date, created_at)
                    SELECT
                        r.ingredient_id AS product_id,
                        'out' AS transaction_type,
                        (r.quantity_needed * od.quantity) AS quantity,
                        'sale' AS reference_type,
                        NEW.id AS reference_id,
                        NEW.employee_id AS employee_id,
                        CONCAT('Auto deduct from order ', NEW.order_number) AS notes,
                        NOW() AS transaction_date,
                        NOW() AS created_at
                    FROM order_details od
                    INNER JOIN recipes r ON r.product_id = od.product_id
                    WHERE od.order_id = NEW.id;
                END IF;
            END
            SQL);
    }
};
