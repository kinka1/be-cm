Buatkan Entity Relationship Diagram (ERD) untuk sistem terintegrasi Asset Management dan Point of Sale (POS) Kafe dengan spesifikasi berikut:
A. MODUL ABSENSI
Entity: Attendance

Mencatat kehadiran karyawan dengan verifikasi foto
Atribut: attendance_id, employee_id, date, clock_in, clock_out, photo_url, status (hadir/izin/sakit/alpha), location_coordinates, notes
Relasi: Many-to-One dengan Employee

B. MODUL MANAJEMEN KARYAWAN
Entity: Employee

CRUD lengkap data karyawan
Atribut: employee_id (PK), full_name, email, phone, address, date_of_birth, join_date, position, role_id (FK), photo_url, status (active/inactive), created_at, updated_at
Relasi:

One-to-Many dengan Attendance
Many-to-One dengan Role
One-to-Many dengan StockTransaction (sebagai operator)



Entity: Role

Atribut: role_id (PK), role_name (operator/supervisor/admin), permissions (JSON field untuk akses menu), created_at
Relasi: One-to-Many dengan Employee

C. MODUL ASSET/STOCK MANAGEMENT
Entity: Product

Atribut: product_id (PK), product_name, sku, category_id (FK), description, unit_of_measure, minimum_stock, current_stock, cost_price, selling_price, is_active, created_at, updated_at
Relasi:

Many-to-One dengan Category
One-to-Many dengan StockTransaction
One-to-Many dengan OrderDetail



Entity: Category

Atribut: category_id (PK), category_name, description
Relasi: One-to-Many dengan Product

Entity: StockTransaction

Mencatat keluar-masuk barang
Atribut: transaction_id (PK), product_id (FK), transaction_type (in/out/adjustment), quantity, reference_type (purchase/sale/adjustment), reference_id, employee_id (FK), notes, transaction_date, created_at
Relasi:

Many-to-One dengan Product
Many-to-One dengan Employee
Trigger: Update current_stock di Product saat transaksi



Entity: StockReport

View/materialized view untuk rekap stock
Atribut: product_id, product_name, stock_in_total, stock_out_total, current_stock, last_transaction_date

D. MODUL POINT OF SALE (POS)
Entity: Table

Untuk pemesanan via QR Code
Atribut: table_id (PK), table_number, qr_code (unique), capacity, status (available/occupied/reserved), created_at
Relasi: One-to-Many dengan Order

Entity: Order

Atribut: order_id (PK), order_number (unique), table_id (FK) nullable, order_type (dine_in_qr/dine_in_cashier/takeaway), customer_name nullable, employee_id (FK) nullable (kasir), order_date, subtotal, tax, discount, total_amount, payment_method (qris/cash/null), payment_status (pending/paid/cancelled), order_status (pending/preparing/ready/completed/cancelled), created_at, updated_at
Logika:

Jika order_type = 'dine_in_qr' → table_id wajib ada, payment_method = 'qris'
Jika order_type = 'dine_in_cashier/takeaway' → employee_id wajib ada, payment_method bisa 'qris' atau 'cash'


Relasi:

Many-to-One dengan Table
Many-to-One dengan Employee (kasir)
One-to-Many dengan OrderDetail
One-to-One dengan Payment



Entity: OrderDetail

Atribut: order_detail_id (PK), order_id (FK), product_id (FK), quantity, unit_price, subtotal, notes, created_at
Relasi:

Many-to-One dengan Order
Many-to-One dengan Product


Trigger:

Setelah order status = 'completed', buat StockTransaction dengan type 'out' untuk setiap product
Update current_stock di Product (dikurangi sesuai quantity)



Entity: Payment

Atribut: payment_id (PK), order_id (FK), payment_method (qris/cash), amount_paid, change_amount, qris_transaction_id nullable, payment_date, payment_status (pending/success/failed), created_at
Relasi: One-to-One dengan Order

Entity: Recipe (optional tapi penting)

Untuk mendefinisikan komposisi bahan baku per menu
Atribut: recipe_id (PK), product_id (FK) menu, ingredient_id (FK) product, quantity_needed, unit
Relasi:

Many-to-One dengan Product (menu)
Many-to-One dengan Product (ingredient/bahan)


Logika: Saat OrderDetail dibuat, sistem kalkulasi pengurangan stock berdasarkan recipe

E. BUSINESS RULES & CONSTRAINTS

Role-Based Access:

Operator: Akses POS, input absensi, view stock
Supervisor: Semua akses operator + manage stock transaction, view reports
Admin: Full access semua modul


Stock Integration dengan POS:

Saat Order status berubah ke 'completed', trigger otomatis:

Cek Recipe untuk setiap OrderDetail
Buat StockTransaction type 'out' untuk setiap ingredient
Update Product.current_stock


Jika current_stock < minimum_stock, generate alert


Payment Logic:

Order dari QR (table) → payment_method hanya 'qris'
Order dari kasir → payment_method bisa 'qris' atau 'cash'
QRIS payment → payment_status 'pending' sampai konfirmasi
Cash payment → payment_status langsung 'success'


Absensi Foto:

Foto disimpan sebagai URL (cloud storage)
Validasi: 1 employee max 1 clock_in per day
Auto clock_out jika belum clock_out sebelum jam 00:00



F. INDEXES & OPTIMIZATIONS

Index: employee_id, date di Attendance
Index: product_id, transaction_date di StockTransaction
Index: order_date, payment_status di Order
Index: table_id, order_status di Order
Unique constraint: qr_code di Table

G. OUTPUT YANG DIHARAPKAN

Diagram ERD dengan notasi Crow's Foot atau Chen
2. List semua entities, attributes, primary keys, foreign keys
3. Deskripsi relasi (cardinality dan participation)
4. Business rules dan constraints tertulis
5. Trigger/stored procedure yang diperlukan
6. buatkan migrasinya untuk ini pada project ini