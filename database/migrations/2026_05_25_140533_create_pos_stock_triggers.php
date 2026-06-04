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
        DB::unprepared('DROP TRIGGER IF EXISTS trg_stock_transactions_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_orders_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_orders_before_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_orders_before_update');

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_stock_transactions_after_insert
            AFTER INSERT ON stock_transactions
            FOR EACH ROW
            BEGIN
                IF NEW.transaction_type = 'in' THEN
                    UPDATE products
                    SET current_stock = current_stock + NEW.quantity
                    WHERE id = NEW.product_id;
                ELSEIF NEW.transaction_type = 'out' THEN
                    UPDATE products
                    SET current_stock = current_stock - NEW.quantity
                    WHERE id = NEW.product_id;
                END IF;
            END
            SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_orders_before_insert
            BEFORE INSERT ON orders
            FOR EACH ROW
            BEGIN
                IF NEW.order_type = 'dine_in_qr' THEN
                    IF NEW.table_id IS NULL THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'table_id is required for dine_in_qr';
                    END IF;
                    SET NEW.payment_method = 'qris';
                ELSEIF NEW.order_type IN ('dine_in_cashier', 'takeaway') THEN
                    IF NEW.employee_id IS NULL THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'employee_id is required for cashier/takeaway';
                    END IF;
                    IF NEW.payment_method IS NULL THEN
                        SET NEW.payment_method = 'cash';
                    ELSEIF NEW.payment_method NOT IN ('qris', 'cash') THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'payment_method must be qris or cash';
                    END IF;
                END IF;
            END
            SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_orders_before_update
            BEFORE UPDATE ON orders
            FOR EACH ROW
            BEGIN
                IF NEW.order_type = 'dine_in_qr' THEN
                    IF NEW.table_id IS NULL THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'table_id is required for dine_in_qr';
                    END IF;
                    SET NEW.payment_method = 'qris';
                ELSEIF NEW.order_type IN ('dine_in_cashier', 'takeaway') THEN
                    IF NEW.employee_id IS NULL THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'employee_id is required for cashier/takeaway';
                    END IF;
                    IF NEW.payment_method IS NULL THEN
                        SET NEW.payment_method = 'cash';
                    ELSEIF NEW.payment_method NOT IN ('qris', 'cash') THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'payment_method must be qris or cash';
                    END IF;
                END IF;
            END
            SQL);

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_stock_transactions_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_orders_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_orders_before_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_orders_before_update');
    }
};
