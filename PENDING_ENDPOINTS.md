# Pending Endpoints

Dokumen ini berisi endpoint yang tercantum di `PENDING_FEATURES.md` tetapi belum tersedia di backend saat ini.

Referensi pembanding:
- `PENDING_FEATURES.md`
- `php artisan route:list`
- Implementasi controller dan route di `routes/api.php`

## Ringkasan

Total endpoint pending berdasarkan dokumen fitur:
- Attendance / Absensi: 6 endpoint
- Recipe Management: 6 endpoint
- Table / QR Table Management: 6 endpoint
- Payment Detail / Midtrans Sync: 3 endpoint
- POS Order Item Management: 4 endpoint
- Stock Alert / Opname / Export: 4 endpoint
- Dashboard API: 4 endpoint

## 1. Attendance / Absensi

Status: belum ada controller/API

Endpoint pending:
- `GET /api/attendances`
- `POST /api/attendances/clock-in`
- `POST /api/attendances/clock-out`
- `GET /api/attendances/{id}`
- `PATCH /api/attendances/{id}`
- `GET /api/attendance-report`

Prioritas: tinggi

Catatan:
- Tabel `attendances` sudah tersedia dari migration.
- Belum ada model/controller/route absensi.
- Perlu flow clock-in, clock-out, validasi 1 attendance per employee per hari, dan report.

## 2. Recipe Management

Status: tabel dan model ada, endpoint belum ada

Endpoint pending:
- `GET /api/recipes`
- `POST /api/recipes`
- `GET /api/recipes/{id}`
- `PUT /api/recipes/{id}`
- `DELETE /api/recipes/{id}`
- `GET /api/products/{product}/recipes`

Prioritas: tinggi

Catatan:
- Tabel `recipes` dan model `Recipe` sudah ada.
- Recipe sudah dipakai oleh POS untuk deduksi stok.
- Belum ada CRUD untuk mengatur komposisi menu.

## 3. Table / QR Table Management

Status: tabel dan model ada, CRUD belum ada

Endpoint pending:
- `GET /api/tables`
- `POST /api/tables`
- `GET /api/tables/{id}`
- `PUT /api/tables/{id}`
- `DELETE /api/tables/{id}`
- `PATCH /api/tables/{id}/status`

Prioritas: tinggi

Yang sudah ada terkait QR meja:
- `GET /api/pos/tables/{qr_code}/menu`

Catatan:
- Tabel yang digunakan untuk meja adalah `calon_mantu`.
- Model `CalonMantu` sudah ada.
- Belum ada endpoint CRUD meja, generate QR, dan update status meja.

## 4. Payment Detail / Midtrans Sync

Status: webhook sudah ada, detail/sync belum ada

Endpoint pending:
- `GET /api/payments/{payment}`
- `GET /api/orders/{order}/payment`
- `POST /api/payments/{payment}/sync-midtrans`

Prioritas: sedang

Yang sudah ada:
- `POST /api/payments/midtrans/webhook`

Catatan:
- Webhook Midtrans sudah tersedia.
- Belum ada endpoint manual untuk melihat detail payment atau sinkronisasi ulang status payment ke Midtrans.

## 5. POS Order Item Management

Status: create order sudah ada, edit item belum ada

Endpoint pending:
- `POST /api/pos/orders/{order}/items`
- `PATCH /api/pos/orders/{order}/items/{item}`
- `DELETE /api/pos/orders/{order}/items/{item}`
- `PATCH /api/pos/orders/{order}/cancel`

Prioritas: sedang-tinggi

Yang sudah ada:
- `POST /api/pos/qr-orders`
- `POST /api/pos/cashier-orders`
- `GET /api/pos/orders`
- `GET /api/pos/orders/{order}`
- `PATCH /api/pos/orders/{order}/status`

Catatan:
- Saat ini item hanya dibuat saat order dibuat.
- Belum ada flow tambah/update/hapus item setelah order terbentuk.
- Perlu aturan jelas apakah item boleh diubah setelah payment sukses.

## 6. Stock Alert / Opname / Export

Status: stock report dan stock transaction sudah ada, fitur lanjutan belum ada

Endpoint pending:
- `GET /api/stock-alerts`
- `POST /api/stock-opname`
- `GET /api/stock-opname`
- `GET /api/stock-report/export`

Prioritas: sedang

Yang sudah ada:
- `GET /api/stock-report`
- `GET /api/stock-transactions`
- `POST /api/stock-transactions`

Catatan:
- `minimum_stock` sudah ada di `products`.
- Low stock masih dihitung secara manual dari report/frontend.
- Belum ada flow stock opname dan export report.

## 7. Dashboard API

Status: frontend masih menghitung dari beberapa endpoint

Endpoint pending:
- `GET /api/dashboard/summary`
- `GET /api/dashboard/sales`
- `GET /api/dashboard/low-stock`
- `GET /api/dashboard/recent-orders`

Prioritas: sedang

Catatan:
- Frontend saat ini mengambil data dari `products`, `pos/orders`, dan `stock-report`.
- Endpoint dashboard khusus akan mengurangi beban frontend dan menyatukan business logic summary di backend.

## Endpoint Yang Sudah Ada

Endpoint utama yang sudah tersedia saat dokumen ini dibuat:

### Auth

- `POST /api/auth/register`
- `POST /api/auth/login`
- `GET /api/me`
- `POST /api/auth/logout`

### Roles

- `GET /api/roles`
- `POST /api/roles`
- `GET /api/roles/{role}`
- `PUT/PATCH /api/roles/{role}`
- `DELETE /api/roles/{role}`

### Employees

- `GET /api/employees`
- `POST /api/employees`
- `GET /api/employees/{employee}`
- `PUT/PATCH /api/employees/{employee}`
- `DELETE /api/employees/{employee}`

### Categories

- `GET /api/categories`
- `POST /api/categories`
- `GET /api/categories/{category}`
- `PUT/PATCH /api/categories/{category}`
- `DELETE /api/categories/{category}`

### Products

- `GET /api/products`
- `POST /api/products`
- `GET /api/products/{product}`
- `PUT/PATCH /api/products/{product}`
- `DELETE /api/products/{product}`

### Stock

- `GET /api/stock-transactions`
- `POST /api/stock-transactions`
- `GET /api/stock-report`

### POS

- `GET /api/pos/menu`
- `GET /api/pos/tables/{qr_code}/menu`
- `POST /api/pos/qr-orders`
- `POST /api/pos/cashier-orders`
- `GET /api/pos/orders`
- `GET /api/pos/orders/{order}`
- `PATCH /api/pos/orders/{order}/status`

### Payments

- `POST /api/payments/midtrans/webhook`

## Rekomendasi Urutan Implementasi

1. Recipe Management
2. Table / QR Table Management
3. Attendance / Absensi
4. Stock Alerts
5. POS Order Item Management
6. Dashboard API
7. Payment Detail / Midtrans Sync
8. Stock Opname / Export
