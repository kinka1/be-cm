<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('qris', 'cash', 'transfer') NULL");
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('qris', 'cash', 'transfer') NOT NULL");

        DB::unprepared('DROP TRIGGER IF EXISTS trg_orders_before_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_orders_before_update');

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
                    ELSEIF NEW.payment_method NOT IN ('qris', 'cash', 'transfer') THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'payment_method must be cash, qris, or transfer';
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
                    ELSEIF NEW.payment_method NOT IN ('qris', 'cash', 'transfer') THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'payment_method must be cash, qris, or transfer';
                    END IF;
                END IF;
            END
            SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_orders_before_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_orders_before_update');

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

        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('qris', 'cash') NOT NULL");
        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('qris', 'cash') NULL");
    }
};
