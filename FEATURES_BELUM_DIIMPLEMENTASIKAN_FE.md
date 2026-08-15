# Fitur Yang Belum Diimplementasikan Di Frontend

Dokumen ini berisi daftar fitur yang belum terlihat di frontend berdasarkan perbandingan dengan file `FEATURES yang sudah diimplementasikan di fe.md` dan kemampuan API/backend yang sudah tersedia saat ini.

## Ringkasan

Fitur frontend yang masih belum tersedia atau belum aktif penuh:

- Multi toko / store selection.
- Buka dan tutup kasir.
- Pengeluaran dan pemasukan kas pada sesi kasir.
- Ringkasan kasir per sesi.
- Detail transaksi per sesi kasir.
- Halaman khusus stock alerts.
- Restore dan force delete produk.
- Kartu stok per produk.
- Export stock report CSV.
- Recipe management.
- Table / QR table management.
- Asset dashboard summary berbasis endpoint backend.
- Role guard yang benar-benar aktif.
- Sinkronisasi role frontend dan backend.
- Endpoint akses toko user dan current store.
- Attendance / absensi karyawan.
- Audit trail master data.

## 1. Multi Toko / Store Selection

Status frontend: belum ada route/menu khusus.

Backend sudah tersedia:

- `GET /stores`
- `POST /stores`
- `GET /stores/:id`
- `PUT /stores/:id`
- `DELETE /stores/:id`
- `GET /me/stores`
- `POST /me/current-store`

Fitur yang perlu dibuat di frontend:

- Halaman atau dropdown pilih toko aktif.
- Ambil daftar toko yang bisa diakses user dari `GET /me/stores`.
- Simpan toko aktif melalui `POST /me/current-store`.
- Kirim `store_id` atau gunakan current store backend untuk modul:
  - POS kasir
  - Orders
  - Products
  - Categories
  - Stock
  - Employees
  - Suppliers
  - Purchase orders
  - Stock opname
  - Stock adjustment
  - Product batches
  - Dashboard
- CRUD toko untuk admin.

Endpoint yang perlu diintegrasikan:

- `GET /me/stores`
- `POST /me/current-store`
- `GET /stores`
- `POST /stores`
- `GET /stores/:id`
- `PUT /stores/:id`
- `DELETE /stores/:id`

## 2. Buka Kasir / Cashier Session

Status frontend: belum ada.

Backend sudah tersedia:

- `POST /pos/cashier-sessions/open`
- `GET /pos/cashier-sessions/current`
- `GET /pos/cashier-sessions`
- `GET /pos/cashier-sessions/:id`
- `POST /pos/cashier-sessions/:id/close`
- `GET /pos/cashier-sessions/:id/summary`
- `GET /pos/cashier-sessions/:id/orders`
- `GET /pos/cashier-sessions/:id/cash-movements`
- `POST /pos/cashier-sessions/:id/cash-movements`

Fitur yang perlu dibuat di frontend:

- Modal/form buka kasir sebelum operator menggunakan POS.
- Input uang kas awal atau modal kas.
- Cek sesi kasir aktif saat membuka halaman `/pos`.
- Blokir submit order kasir jika belum ada sesi kasir aktif.
- Tombol tutup kasir.
- Input uang fisik saat tutup kasir.
- Menampilkan selisih kas.
- Menampilkan histori sesi kasir.

Request buka kasir:

```json
{
  "store_id": 1,
  "opening_cash": 500000,
  "opening_notes": "Modal kas pagi"
}
```

Request tutup kasir:

```json
{
  "closing_cash": 1250000,
  "closing_notes": "Tutup shift sore"
}
```

## 3. Pengeluaran Dan Pemasukan Kas

Status frontend: belum ada.

Backend sudah tersedia:

- `POST /pos/cashier-sessions/:id/cash-movements`
- `GET /pos/cashier-sessions/:id/cash-movements`

Fitur yang perlu dibuat di frontend:

- Form pengeluaran kas.
- Form pemasukan kas manual.
- List mutasi kas per sesi.
- Kategori pengeluaran/pemasukan.
- Catatan transaksi kas.

Request contoh:

```json
{
  "type": "cash_out",
  "amount": 50000,
  "category": "operasional",
  "description": "Beli galon"
}
```

## 4. Ringkasan Kasir Per Sesi

Status frontend: belum ada.

Backend sudah tersedia:

- `GET /pos/cashier-sessions/:id/summary`

Data yang perlu ditampilkan:

- Opening cash.
- Cash sales.
- QRIS sales.
- Cash in.
- Cash out.
- Expected cash.
- Closing cash.
- Cash difference.
- Total orders.
- Waktu buka kasir.
- Waktu tutup kasir.

## 5. Detail Transaksi Per Sesi Kasir

Status frontend: belum ada.

Backend sudah tersedia:

- `GET /pos/cashier-sessions/:id/orders`
- `GET /pos/cashier-sessions/:id/cash-movements`

Fitur yang perlu dibuat di frontend:

- Tab daftar order pada sesi kasir.
- Tab mutasi kas manual.
- Filter atau pagination transaksi.
- Link ke detail order.

## 6. Stock Alerts Sebagai Halaman Khusus

Status frontend: tersedia di layer API menurut catatan, tetapi belum terlihat sebagai route/menu khusus.

Backend sudah tersedia:

- `GET /stock-alerts`
- `GET /stock-alerts/summary`

Fitur yang perlu dibuat di frontend:

- Route khusus misalnya `/stock-alerts`.
- List produk low stock.
- Summary low stock dan out of stock.
- Filter berdasarkan toko dan kategori.
- CTA menuju halaman produk atau transaksi stok.

## 7. Restore Dan Force Delete Produk

Status frontend: belum ada.

Backend sudah tersedia:

- `GET /products/deleted`
- `POST /products/:id/restore`
- `DELETE /products/:id/force`

Fitur yang perlu dibuat di frontend:

- Halaman atau tab produk terhapus.
- Tombol restore produk.
- Tombol hapus permanen dengan konfirmasi.

## 8. Kartu Stok Per Produk

Status frontend: belum ada.

Backend sudah tersedia:

- `GET /products/:id/stock-card`

Fitur yang perlu dibuat di frontend:

- Tombol lihat kartu stok pada produk.
- Riwayat pergerakan stok produk.
- Running balance.
- Filter tanggal.
- Filter transaction type.
- Filter reference type.

## 9. Export Stock Report CSV

Status frontend: belum ada.

Backend sudah tersedia:

- `GET /stock-report/export`

Fitur yang perlu dibuat di frontend:

- Tombol export CSV pada halaman stock report.
- Kirim filter aktif saat export:
  - `store_id`
  - `category_id`
  - `product_id`
  - `low_stock_only`
  - `search`
- Download file `stock-report.csv`.

Catatan: backend saat ini mendukung CSV, belum XLSX.

## 10. Recipe Management

Status frontend: belum ada.

Backend sudah tersedia:

- `GET /recipes`
- `POST /recipes`
- `GET /recipes/:id`
- `PUT /recipes/:id`
- `DELETE /recipes/:id`
- `GET /products/:product/recipes`

Fitur yang perlu dibuat di frontend:

- Halaman recipe management.
- Tambah recipe bahan untuk menu.
- Edit quantity dan unit bahan.
- Hapus recipe item.
- Detail recipe per menu.
- Pilih produk menu dan produk bahan.

## 11. Table / QR Table Management

Status frontend: belum ada route/menu khusus.

Backend sudah tersedia:

- `GET /tables`
- `POST /tables`
- `GET /tables/:id`
- `PUT /tables/:id`
- `DELETE /tables/:id`
- `PATCH /tables/:id/status`

Fitur yang perlu dibuat di frontend:

- CRUD meja.
- Generate atau input QR code meja.
- Filter meja berdasarkan toko dan status.
- Update status meja.
- Tampilkan URL QR order untuk pelanggan.

## 12. Asset Dashboard Summary

Status frontend: dashboard masih menghitung dari beberapa endpoint lama.

Backend sudah tersedia:

- `GET /assets/summary`
- `GET /assets/low-stock-summary`
- `GET /assets/stock-movement-summary`

Fitur yang perlu dibuat di frontend:

- Gunakan endpoint summary untuk dashboard asset.
- Filter dashboard berdasarkan toko.
- Tampilkan nilai stok berdasarkan cost price.
- Tampilkan transaksi stok hari ini.
- Tampilkan summary movement stok.

## 13. Auth Guard Belum Aktif Penuh

Status frontend: sudah ada kode auth, tetapi menurut catatan masih dinonaktifkan.

Catatan dari file implementasi frontend:

- `AUTH_GUARD_DISABLED = true`.
- Semua menu admin/kasir terlihat tanpa login.
- Route `/login` diarahkan ke `/`.

Fitur yang perlu dibuat atau diaktifkan:

- Aktifkan protected route.
- Aktifkan login sebagai flow utama.
- Redirect berdasarkan role setelah login.
- Hide/show menu berdasarkan role.
- Handle token expired atau unauthenticated response.

## 14. Sinkronisasi Role Frontend Dan Backend

Status frontend: belum sinkron penuh.

Frontend menggunakan role:

- `admin`
- `kasir`
- `user`

Backend menggunakan role:

- `admin`
- `supervisor`
- `operator`

Fitur yang perlu disesuaikan:

- Tentukan mapping final role.
- Ubah `kasir` menjadi `operator`, atau buat mapping frontend `operator -> kasir`.
- Tambahkan akses supervisor jika dibutuhkan.
- Pastikan route guard cocok dengan role backend.

## 15. Store-Aware Form Dan Filter Di Semua Modul

Status frontend: belum ada pada daftar implementasi.

Backend sudah mendukung `store_id` pada banyak endpoint.

Fitur yang perlu dibuat di frontend:

- Filter `store_id` di list produk, kategori, order, stock, supplier, PO, opname, adjustment, batch.
- Field `store_id` saat membuat data master seperti produk, kategori, meja, supplier.
- Gunakan toko aktif sebagai default value.
- Cegah user memilih toko yang bukan aksesnya.

## 16. Health Check UI / Debug API

Status frontend: belum ada.

Backend sudah tersedia:

- `GET /health`

Fitur opsional yang bisa dibuat:

- Debug status API pada halaman admin/developer.
- Indikator API online/offline.

## 17. Attendance / Absensi Karyawan

Status frontend: belum ada.

Backend sudah tersedia:

- `GET /attendances`
- `GET /attendances/summary`
- `GET /attendances/today`
- `POST /attendances/clock-in`
- `POST /attendances/clock-out`
- `POST /attendances`
- `GET /attendances/:id`
- `PUT /attendances/:id`
- `DELETE /attendances/:id`

Fitur yang perlu dibuat di frontend:

- Halaman absensi karyawan.
- Tombol clock-in dan clock-out untuk operator/user login.
- Upload foto saat clock-in jika diperlukan.
- Input lokasi/koordinat saat clock-in atau clock-out.
- List attendance dengan filter toko, employee, status, dan tanggal.
- Summary attendance per periode.
- CRUD manual attendance untuk admin/supervisor.

Status attendance:

- `hadir`
- `izin`
- `sakit`
- `alpha`

## 18. Audit Trail Master Data

Status frontend: belum ada.

Backend saat ini baru memiliki tabel/model audit, endpoint audit belum tersedia.

Fitur frontend yang bisa dibuat setelah backend endpoint tersedia:

- List audit trail.
- Filter berdasarkan entity, user, action, tanggal.
- Detail perubahan old/new values.

## 19. Product Batch CRUD Lengkap

Status frontend: sebagian ada.

Frontend sudah ada:

- List batch.
- Create batch.
- Filter expiring soon.

Yang belum ada:

- Detail batch.
- Edit batch.
- Delete batch.

Catatan: backend saat ini juga baru menyediakan list, create, dan expiring soon.

## 20. Purchase Order Dan Supplier Store Validation UI

Status frontend: belum terlihat pada daftar implementasi.

Karena backend sekarang memiliki `store_id`, frontend perlu memastikan:

- Supplier yang dipilih berasal dari toko aktif.
- Produk item PO berasal dari toko aktif.
- PO dikirim dengan `store_id`.
- Filter list PO berdasarkan toko aktif.

## 21. Cashier Order Dengan Sesi Kasir Aktif

Status frontend: POS kasir sudah ada, tetapi belum disesuaikan dengan rule baru.

Backend sekarang mewajibkan order kasir memiliki sesi kasir `open` berdasarkan:

- `store_id`
- `employee_id`
- `status = open`

Perubahan frontend yang perlu dilakukan:

- Saat masuk `/pos`, panggil `GET /pos/cashier-sessions/current?store_id=<active_store_id>`.
- Jika tidak ada sesi open, tampilkan form buka kasir.
- Setelah buka kasir sukses, izinkan transaksi POS.
- Saat submit order, tetap kirim `store_id` dan `employee_id`.
- Tampilkan pesan error jika backend mengembalikan validasi `cashier_session`.

## Prioritas Implementasi Frontend Berikutnya

Prioritas 1:

- Multi toko / store selection.
- Aktifkan auth guard dan role guard.
- Buka kasir sebelum POS.
- Tutup kasir dan summary kasir.
- Integrasi POS dengan sesi kasir aktif.

Prioritas 2:

- Cash movements kasir.
- Detail transaksi per sesi kasir.
- Store-aware filter di semua modul.
- Attendance / absensi karyawan.
- Kartu stok per produk.
- Stock alerts page.

Prioritas 3:

- Recipe management.
- Table / QR table management.
- Restore deleted products.
- Export stock report CSV.
- Asset dashboard summary endpoint.

Prioritas 4:

- Audit trail UI.
- Product batch CRUD lengkap.
- Health/debug page.

## Lampiran Detail Endpoint Untuk Implementasi Frontend

Semua endpoint menggunakan base URL API frontend, contoh:

```env
VITE_API_BASE_URL=https://api.calon-mantoe.cloud/api
```

Response JSON umum:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {}
}
```

Response error bisnis umum:

```json
{
  "status": "gagal",
  "message": "pesan error",
  "data": null
}
```

Response list yang memakai pagination Laravel umumnya berada di `data.data`:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "current_page": 1,
    "data": [],
    "per_page": 15,
    "total": 0
  }
}
```

### A. Multi Toko Dan Toko Aktif User

#### `GET /me/stores`

Kebutuhan frontend: dropdown toko yang boleh diakses user login.

Auth: Bearer token.

Query parameter: tidak ada.

Request body: tidak ada.

Response `200`:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": [
    {
      "id": 1,
      "store_name": "Calon Mantu Utama",
      "code": "MAIN",
      "address": null,
      "phone": null,
      "is_active": true
    }
  ]
}
```

#### `POST /me/current-store`

Kebutuhan frontend: menyimpan toko aktif user.

Auth: Bearer token.

Query parameter: tidak ada.

Request body:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `store_id` | integer | yes | ID toko aktif yang dipilih user. |

Example:

```json
{
  "store_id": 1
}
```

Response `200`:

```json
{
  "status": "sukses",
  "message": "updated",
  "data": {
    "id": 1,
    "current_store_id": 1,
    "current_store": {
      "id": 1,
      "store_name": "Calon Mantu Utama",
      "code": "MAIN"
    }
  }
}
```

Response `403` jika user tidak punya akses toko:

```json
{
  "status": "gagal",
  "message": "tidak memiliki akses ke toko ini",
  "data": null
}
```

#### `GET /stores`

Kebutuhan frontend: halaman master toko untuk admin.

Auth: Bearer token disarankan.

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `is_active` | boolean | no | Filter toko aktif/nonaktif. |
| `search` | string | no | Cari berdasarkan nama toko atau kode. |
| `per_page` | integer | no | Jumlah data per halaman. |

Request body: tidak ada.

Response `200`: pagination stores.

#### `POST /stores`

Kebutuhan frontend: tambah toko.

Auth: Bearer token disarankan.

Request body:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `store_name` | string | yes | Nama toko. |
| `code` | string | yes | Kode toko, harus unik. |
| `address` | string | no | Alamat toko. |
| `phone` | string | no | Nomor telepon toko. |
| `is_active` | boolean | no | Status aktif toko. |

Example:

```json
{
  "store_name": "Calon Mantu Cabang 2",
  "code": "BRANCH-2",
  "address": "Jl. Contoh No. 2",
  "phone": "08123456789",
  "is_active": true
}
```

Response `201`: data toko baru.

#### `GET /stores/:id`

Kebutuhan frontend: detail toko.

Path parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `id` | integer | yes | ID toko. |

Response `200`: detail toko.

#### `PUT /stores/:id`

Kebutuhan frontend: edit toko.

Path parameter: `id`.

Request body sama seperti `POST /stores`, field bersifat opsional sesuai data yang diedit.

Response `200`: data toko setelah update.

#### `DELETE /stores/:id`

Kebutuhan frontend: hapus toko.

Path parameter: `id`.

Request body: tidak ada.

Response `200`:

```json
{
  "status": "sukses",
  "message": "deleted",
  "data": null
}
```

Response `422` jika toko masih memiliki produk/order:

```json
{
  "status": "gagal",
  "message": "store masih memiliki data produk atau order",
  "data": null
}
```

### B. Buka Kasir, Tutup Kasir, Dan Mutasi Kas

#### `POST /pos/cashier-sessions/open`

Kebutuhan frontend: membuka sesi kasir sebelum transaksi POS.

Auth: Bearer token.

Request body:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `store_id` | integer | yes | ID toko tempat kasir dibuka. |
| `opening_cash` | number | yes | Modal kas awal. |
| `opening_notes` | string | no | Catatan buka kasir. |

Example:

```json
{
  "store_id": 1,
  "opening_cash": 500000,
  "opening_notes": "Modal kas pagi"
}
```

Response `201`:

```json
{
  "status": "sukses",
  "message": "opened",
  "data": {
    "id": 1,
    "store_id": 1,
    "employee_id": 3,
    "opening_cash": "500000.00",
    "status": "open",
    "opened_at": "2026-07-21T09:00:00.000000Z"
  }
}
```

Response `422` jika sesi masih open untuk operator dan toko yang sama:

```json
{
  "status": "gagal",
  "message": "sesi kasir masih terbuka untuk operator dan toko ini",
  "data": null
}
```

#### `GET /pos/cashier-sessions/current`

Kebutuhan frontend: cek sesi open saat user masuk halaman POS.

Auth: Bearer token.

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `store_id` | integer | no | Toko aktif. Jika tidak dikirim, backend dapat memakai current store user. |

Response `200` jika ada sesi:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "id": 1,
    "store_id": 1,
    "employee_id": 3,
    "status": "open",
    "opening_cash": "500000.00"
  }
}
```

Response `200` jika tidak ada sesi:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": null
}
```

#### `GET /pos/cashier-sessions`

Kebutuhan frontend: histori sesi kasir.

Auth: Bearer token.

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `store_id` | integer | no | Filter toko. |
| `employee_id` | integer | no | Filter operator. |
| `status` | string | no | `open` atau `closed`. |
| `from_date` | date | no | Tanggal awal opened_at. |
| `to_date` | date | no | Tanggal akhir opened_at. |
| `per_page` | integer | no | Jumlah data per halaman. |

Response `200`: pagination cashier sessions.

#### `GET /pos/cashier-sessions/:id`

Kebutuhan frontend: detail sesi kasir.

Path parameter: `id` cashier session.

Response `200`: detail session dengan `store`, `employee`, `orders`, dan `cash_movements`.

#### `POST /pos/cashier-sessions/:id/cash-movements`

Kebutuhan frontend: input pemasukan/pengeluaran kas manual.

Path parameter: `id` cashier session.

Request body:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `type` | string | yes | `cash_in` atau `cash_out`. |
| `amount` | number | yes | Nominal mutasi kas. |
| `category` | string | no | Kategori mutasi, contoh `operasional`. |
| `description` | string | no | Catatan mutasi. |

Example:

```json
{
  "type": "cash_out",
  "amount": 50000,
  "category": "operasional",
  "description": "Beli galon"
}
```

Response `201`: data mutasi kas yang dibuat.

Response `422` jika sesi sudah closed:

```json
{
  "status": "gagal",
  "message": "sesi kasir sudah ditutup",
  "data": null
}
```

#### `POST /pos/cashier-sessions/:id/close`

Kebutuhan frontend: tutup kasir.

Path parameter: `id` cashier session.

Request body:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `closing_cash` | number | yes | Uang fisik saat tutup kasir. |
| `closing_notes` | string | no | Catatan tutup kasir. |

Example:

```json
{
  "closing_cash": 1250000,
  "closing_notes": "Tutup shift sore"
}
```

Response `200`: data session dengan status `closed`.

#### `GET /pos/cashier-sessions/:id/summary`

Kebutuhan frontend: ringkasan kasir.

Path parameter: `id` cashier session.

Response `200`:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "cashier_session_id": 1,
    "store_id": 1,
    "employee_id": 3,
    "status": "open",
    "opening_cash": 500000,
    "cash_sales": 850000,
    "qris_sales": 300000,
    "cash_in": 100000,
    "cash_out": 200000,
    "expected_cash": 1250000,
    "closing_cash": null,
    "cash_difference": null,
    "total_orders": 24
  }
}
```

#### `GET /pos/cashier-sessions/:id/orders`

Kebutuhan frontend: daftar order dalam sesi kasir.

Path parameter: `id` cashier session.

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `per_page` | integer | no | Jumlah data per halaman. |

Response `200`: pagination order dengan `details.product` dan `payment`.

#### `GET /pos/cashier-sessions/:id/cash-movements`

Kebutuhan frontend: daftar mutasi kas manual dalam sesi.

Path parameter: `id` cashier session.

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `per_page` | integer | no | Jumlah data per halaman. |

Response `200`: pagination cashier cash movements.

### C. POS Kasir Dengan Sesi Kasir Aktif

#### `POST /pos/cashier-orders`

Kebutuhan frontend: submit order kasir setelah sesi kasir open.

Request body:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `order_type` | string | yes | `dine_in_cashier` atau `takeaway`. |
| `store_id` | integer | yes | Toko aktif. |
| `table_id` | integer | no | Wajib relevan jika dine-in. |
| `employee_id` | integer | yes | Employee operator kasir. |
| `customer_name` | string | no | Nama customer. |
| `payment_method` | string | yes | `cash` atau `qris`. |
| `amount_paid` | number | conditional | Wajib jika `payment_method = cash`. |
| `discount` | number | no | Diskon order. |
| `items` | array | yes | Minimal 1 item. |
| `items.*.product_id` | integer | yes | Produk menu. |
| `items.*.quantity` | number | yes | Quantity item. |
| `items.*.notes` | string | no | Catatan item. |

Example:

```json
{
  "order_type": "takeaway",
  "store_id": 1,
  "employee_id": 3,
  "customer_name": "Budi",
  "payment_method": "cash",
  "amount_paid": 50000,
  "discount": 0,
  "items": [
    {
      "product_id": 7,
      "quantity": 1,
      "notes": "Less ice"
    }
  ]
}
```

Response `201`: data order dengan `details.product` dan `payment`.

Response `422` jika belum buka kasir:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "cashier_session": [
      "Operator belum membuka kasir untuk toko ini"
    ]
  }
}
```

### D. Stock Alerts

#### `GET /stock-alerts`

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `store_id` | integer | no | Filter toko. |
| `category_id` | integer | no | Filter kategori. |
| `per_page` | integer | no | Jumlah data per halaman. |

Response `200`: pagination produk low stock.

#### `GET /stock-alerts/summary`

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `store_id` | integer | no | Filter toko. |

Response `200`:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "low_stock_count": 5,
    "out_of_stock_count": 2
  }
}
```

### E. Restore Dan Force Delete Produk

#### `GET /products/deleted`

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `per_page` | integer | no | Jumlah data per halaman. |

Response `200`: pagination produk yang sudah soft deleted.

#### `POST /products/:id/restore`

Path parameter: `id` produk deleted.

Request body: tidak ada.

Response `200`:

```json
{
  "status": "sukses",
  "message": "restored",
  "data": {}
}
```

#### `DELETE /products/:id/force`

Path parameter: `id` produk deleted.

Request body: tidak ada.

Response `200`:

```json
{
  "status": "sukses",
  "message": "force deleted",
  "data": null
}
```

### F. Kartu Stok Per Produk

#### `GET /products/:id/stock-card`

Path parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `id` | integer | yes | ID produk. |

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `from_date` | date | no | Tanggal awal transaksi. |
| `to_date` | date | no | Tanggal akhir transaksi. |
| `transaction_type` | string | no | `in`, `out`, atau `adjustment`. |
| `reference_type` | string | no | Contoh `purchase`, `sale`, `adjustment`. |

Response `200`:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "product": {},
    "transactions": [
      {
        "id": 1,
        "transaction_type": "in",
        "quantity": "10.00",
        "reference_type": "purchase",
        "running_balance": 10
      }
    ]
  }
}
```

### G. Export Stock Report CSV

#### `GET /stock-report/export`

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `store_id` | integer | no | Filter toko. |
| `category_id` | integer | no | Filter kategori. |
| `product_id` | integer | no | Filter produk. |
| `low_stock_only` | boolean | no | Hanya low stock. |
| `search` | string | no | Cari nama produk atau SKU. |

Request body: tidak ada.

Response `200`: file download `stock-report.csv`.

### H. Recipe Management

#### `GET /recipes`

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `product_id` | integer | no | Filter menu/produk utama. |
| `ingredient_id` | integer | no | Filter bahan. |
| `per_page` | integer | no | Jumlah data per halaman. |

Response `200`: pagination recipes.

#### `POST /recipes`

Request body:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `product_id` | integer | yes | Produk/menu utama. |
| `ingredient_id` | integer | yes | Produk bahan. |
| `quantity_needed` | number | yes | Jumlah bahan. |
| `unit` | string | yes | Satuan bahan. |

Example:

```json
{
  "product_id": 7,
  "ingredient_id": 1,
  "quantity_needed": 18,
  "unit": "gram"
}
```

Response `201`: recipe baru.

#### `GET /recipes/:id`

Path parameter: `id` recipe.

Response `200`: detail recipe.

#### `PUT /recipes/:id`

Path parameter: `id` recipe.

Request body sama seperti `POST /recipes`.

Response `200`: recipe setelah update.

#### `DELETE /recipes/:id`

Path parameter: `id` recipe.

Response `200`:

```json
{
  "status": "sukses",
  "message": "deleted",
  "data": null
}
```

#### `GET /products/:product/recipes`

Path parameter: `product` ID produk/menu.

Response `200`: daftar recipe untuk produk.

### I. Table / QR Table Management

#### `GET /tables`

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `store_id` | integer | no | Filter toko. |
| `status` | string | no | `available`, `occupied`, atau `reserved`. |
| `search` | string | no | Cari nomor meja atau QR code. |
| `per_page` | integer | no | Jumlah data per halaman. |

Response `200`: pagination meja.

#### `POST /tables`

Request body:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `store_id` | integer | yes | ID toko. |
| `table_number` | string | yes | Nomor/nama meja. |
| `qr_code` | string | no | QR code custom, jika kosong backend generate. |
| `capacity` | integer | yes | Kapasitas meja. |
| `status` | string | no | `available`, `occupied`, atau `reserved`. |

Response `201`: data meja baru.

#### `GET /tables/:id`

Path parameter: `id` meja.

Response `200`: detail meja.

#### `PUT /tables/:id`

Path parameter: `id` meja.

Request body:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `store_id` | integer | yes | ID toko. |
| `table_number` | string | yes | Nomor/nama meja. |
| `qr_code` | string | yes | QR code meja. |
| `capacity` | integer | yes | Kapasitas meja. |
| `status` | string | yes | `available`, `occupied`, atau `reserved`. |

Response `200`: data meja setelah update.

#### `DELETE /tables/:id`

Path parameter: `id` meja.

Response `200`: `{ "status": "sukses", "message": "deleted", "data": null }`.

#### `PATCH /tables/:id/status`

Request body:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `status` | string | yes | `available`, `occupied`, atau `reserved`. |

Response `200`: data meja setelah status berubah.

### J. Asset Dashboard Summary

#### `GET /assets/summary`

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `store_id` | integer | no | Filter toko. |

Response `200`:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "active_products": 10,
    "low_stock_items": 2,
    "stock_value": "1500000.00",
    "today_transactions": 4
  }
}
```

#### `GET /assets/low-stock-summary`

Query parameter: `store_id` optional.

Response `200`: list produk low stock.

#### `GET /assets/stock-movement-summary`

Query parameter: `store_id` optional.

Response `200`: agregasi movement stok per produk dan tipe transaksi.

### K. Store-Aware Endpoint Existing

Endpoint berikut sudah digunakan frontend, tetapi perlu ditambahkan `store_id` di query/body ketika implementasi multi toko aktif.

#### `GET /products`

Query parameter penting:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `store_id` | integer | no | Filter toko aktif. |
| `category_id` | integer | no | Filter kategori. |
| `is_active` | boolean | no | Filter produk aktif/nonaktif. |
| `search` | string | no | Cari nama produk atau SKU. |

#### `POST /products` dan `PUT /products/:id`

Tambahkan field `store_id` pada form produk.

Request body minimal:

```json
{
  "store_id": 1,
  "product_name": "Cafe Latte",
  "sku": "MENU-LATTE",
  "category_id": 1,
  "unit_of_measure": "cup",
  "minimum_stock": 0,
  "current_stock": 0,
  "cost_price": 10000,
  "selling_price": 25000,
  "is_active": true
}
```

#### `GET /categories`

Query parameter: `store_id` optional.

#### `POST /categories` dan `PUT /categories/:id`

Request body:

```json
{
  "store_id": 1,
  "category_name": "Coffee",
  "description": "Menu kopi"
}
```

#### `GET /employees`

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `store_id` | integer | no | Filter toko. |
| `role_id` | integer | no | Filter role. |
| `status` | string | no | `active` atau `inactive`. |
| `search` | string | no | Cari nama/email. |

#### `POST /employees`

Tambahkan field `store_id` pada multipart form-data.

Field penting:

| Field | Type | Required |
| --- | --- | --- |
| `store_id` | integer | yes |
| `full_name` | string | yes |
| `email` | string | yes |
| `join_date` | date | yes |
| `role_id` | integer | yes |
| `username` | string | yes |
| `password` | string | yes |
| `password_confirmation` | string | yes |
| `ktp` | file | yes |
| `kk` | file | yes |

#### `GET /stock-transactions`

Tambahkan `store_id` pada filter query.

Query parameter lengkap yang relevan:

| Field | Type | Required |
| --- | --- | --- |
| `store_id` | integer | no |
| `product_id` | integer | no |
| `category_id` | integer | no |
| `employee_id` | integer | no |
| `transaction_type` | string | no |
| `reference_type` | string | no |
| `from_date` | date | no |
| `to_date` | date | no |
| `search` | string | no |
| `per_page` | integer | no |

#### `POST /stock-transactions`

Request body:

```json
{
  "store_id": 1,
  "product_id": 1,
  "transaction_type": "in",
  "quantity": 100,
  "reference_type": "purchase",
  "reference_id": 1,
  "employee_id": 3,
  "notes": "Restock manual",
  "transaction_date": "2026-07-21 10:00:00"
}
```

#### `GET /stock-report`

Query parameter:

| Field | Type | Required |
| --- | --- | --- |
| `store_id` | integer | no |
| `category_id` | integer | no |
| `product_id` | integer | no |
| `low_stock_only` | boolean | no |
| `search` | string | no |
| `per_page` | integer | no |

#### `GET /suppliers`

Query parameter:

| Field | Type | Required |
| --- | --- | --- |
| `store_id` | integer | no |
| `status` | string | no |
| `search` | string | no |
| `per_page` | integer | no |

#### `POST /suppliers` dan `PUT /suppliers/:id`

Request body:

```json
{
  "store_id": 1,
  "supplier_name": "Supplier Kopi",
  "contact_name": "Andi",
  "phone": "08123456789",
  "email": "supplier@example.com",
  "address": "Jl. Supplier",
  "status": "active"
}
```

#### `GET /purchase-orders`

Query parameter:

| Field | Type | Required |
| --- | --- | --- |
| `store_id` | integer | no |
| `status` | string | no |
| `supplier_id` | integer | no |
| `per_page` | integer | no |

#### `POST /purchase-orders`

Request body:

```json
{
  "store_id": 1,
  "supplier_id": 1,
  "employee_id": 3,
  "order_date": "2026-07-21",
  "notes": "Restock mingguan",
  "items": [
    {
      "product_id": 1,
      "quantity": 10,
      "unit_cost": 25000,
      "notes": "Coffee beans"
    }
  ]
}
```

#### `PUT /purchase-orders/:id`

Request body:

```json
{
  "store_id": 1,
  "supplier_id": 1,
  "employee_id": 3,
  "order_date": "2026-07-21",
  "status": "ordered",
  "notes": "Update PO"
}
```

#### `POST /purchase-orders/:id/receive`

Request body: tidak ada.

Response `200`: PO menjadi `received` dan stok masuk dibuat.

#### `POST /purchase-orders/:id/cancel`

Request body: tidak ada.

Response `200`: PO menjadi `cancelled`.

#### `GET /stock-adjustments`

Query parameter:

| Field | Type | Required |
| --- | --- | --- |
| `store_id` | integer | no |
| `status` | string | no |
| `per_page` | integer | no |

#### `POST /stock-adjustments`

Request body:

```json
{
  "product_id": 1,
  "quantity": 10,
  "adjustment_type": "increase",
  "requested_by": 3,
  "reason": "Koreksi stok fisik"
}
```

Catatan: `store_id` diambil backend dari produk.

#### `POST /stock-adjustments/:id/approve`

Request body:

```json
{
  "approved_by": 2,
  "approval_notes": "Disetujui"
}
```

#### `POST /stock-adjustments/:id/reject`

Request body:

```json
{
  "approved_by": 2,
  "approval_notes": "Ditolak karena data tidak sesuai"
}
```

#### `GET /stock-opnames`

Query parameter:

| Field | Type | Required |
| --- | --- | --- |
| `store_id` | integer | no |
| `status` | string | no |
| `per_page` | integer | no |

#### `POST /stock-opnames`

Request body:

```json
{
  "store_id": 1,
  "employee_id": 3,
  "opname_date": "2026-07-21",
  "notes": "Opname shift pagi"
}
```

#### `POST /stock-opnames/:id/items`

Request body:

```json
{
  "product_id": 1,
  "physical_stock": 120,
  "notes": "Stok fisik rak depan"
}
```

#### `POST /stock-opnames/:id/submit`

Request body: tidak ada.

Response `200`: status opname menjadi `submitted`.

#### `POST /stock-opnames/:id/approve`

Request body:

```json
{
  "approved_by": 2
}
```

Response `200`: status opname menjadi `approved` dan adjustment stok dibuat.

#### `GET /product-batches`

Query parameter:

| Field | Type | Required |
| --- | --- | --- |
| `store_id` | integer | no |
| `product_id` | integer | no |
| `per_page` | integer | no |

#### `GET /product-batches/expiring-soon`

Query parameter:

| Field | Type | Required |
| --- | --- | --- |
| `store_id` | integer | no |
| `days` | integer | no |
| `per_page` | integer | no |

#### `POST /product-batches`

Request body:

```json
{
  "product_id": 1,
  "batch_number": "BATCH-001",
  "expired_date": "2026-12-31",
  "quantity": 100,
  "received_date": "2026-07-21",
  "notes": "Batch awal"
}
```

Catatan: `store_id` diambil backend dari produk.

### L. Health Check

#### `GET /health`

Kebutuhan frontend: debug status API online/offline.

Query parameter: tidak ada.

Request body: tidak ada.

Response `200`:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "service": "Calon Mantu",
    "timestamp": "2026-07-21T10:00:00.000000Z"
  }
}
```

### M. Attendance / Absensi Karyawan

Semua endpoint attendance memakai Bearer token.

#### `GET /attendances`

Kebutuhan frontend: list absensi karyawan.

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `employee_id` | integer | no | Filter employee. |
| `store_id` | integer | no | Filter toko. |
| `status` | string | no | `hadir`, `izin`, `sakit`, atau `alpha`. |
| `from_date` | date | no | Tanggal awal. |
| `to_date` | date | no | Tanggal akhir. |
| `search` | string | no | Cari nama/email employee. |
| `per_page` | integer | no | Jumlah data per halaman. |

Request body: tidak ada.

Response `200`: pagination attendance dengan relasi `employee` dan `store`.

#### `GET /attendances/summary`

Kebutuhan frontend: ringkasan absensi periode tertentu.

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `employee_id` | integer | no | Filter employee. |
| `store_id` | integer | no | Filter toko. |
| `from_date` | date | no | Tanggal awal. |
| `to_date` | date | no | Tanggal akhir. |

Response `200`:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "total": 20,
    "hadir": 15,
    "izin": 2,
    "sakit": 1,
    "alpha": 2,
    "clocked_in": 15,
    "clocked_out": 14
  }
}
```

#### `GET /attendances/today`

Kebutuhan frontend: status absensi hari ini.

Query parameter:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `employee_id` | integer | no | Optional untuk admin/supervisor mengecek employee lain. |

Response `200` jika sudah ada attendance:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "id": 1,
    "employee_id": 3,
    "store_id": 1,
    "date": "2026-07-21T00:00:00.000000Z",
    "clock_in": "08:00:00",
    "clock_out": null,
    "status": "hadir"
  }
}
```

Response `200` jika belum ada attendance:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": null
}
```

#### `POST /attendances/clock-in`

Kebutuhan frontend: clock-in user login.

Content type: `multipart/form-data`.

Request body:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `employee_id` | integer | no | Optional; user login otomatis memakai `employee_id` token. |
| `photo` | file | no | Foto absensi. |
| `location_coordinates` | string | no | Koordinat lokasi. |
| `notes` | string | no | Catatan. |

Response `201`: attendance hari ini dengan `clock_in` terisi.

Response `422` jika sudah clock-in:

```json
{
  "status": "gagal",
  "message": "sudah clock in hari ini",
  "data": {}
}
```

#### `POST /attendances/clock-out`

Kebutuhan frontend: clock-out user login.

Request body:

```json
{
  "employee_id": 3,
  "location_coordinates": "-6.200000,106.816666",
  "notes": "Pulang shift sore"
}
```

Field `employee_id` optional untuk user login, tapi bisa dipakai admin/supervisor.

Response `200`: attendance hari ini dengan `clock_out` terisi.

Response `422` jika belum clock-in:

```json
{
  "status": "gagal",
  "message": "belum clock in hari ini",
  "data": null
}
```

#### `POST /attendances`

Kebutuhan frontend: input attendance manual oleh admin/supervisor.

Content type: `multipart/form-data`.

Request body:

| Field | Type | Required | Keterangan |
| --- | --- | --- | --- |
| `employee_id` | integer | yes | Employee yang diabsenkan. |
| `store_id` | integer | no | Jika kosong, backend mengambil dari employee. |
| `date` | date | yes | Tanggal attendance. |
| `clock_in` | time | no | Format `HH:mm:ss`. |
| `clock_out` | time | no | Format `HH:mm:ss`. |
| `photo` | file | no | Foto attendance. |
| `photo_url` | string | no | URL foto jika tidak upload file. |
| `status` | string | yes | `hadir`, `izin`, `sakit`, atau `alpha`. |
| `location_coordinates` | string | no | Koordinat lokasi. |
| `notes` | string | no | Catatan. |

Response `201`: attendance baru.

#### `GET /attendances/:id`

Path parameter: `id` attendance.

Response `200`: detail attendance.

#### `PUT /attendances/:id`

Content type: `multipart/form-data`.

Request body sama seperti `POST /attendances`.

Response `200`: attendance setelah update.

#### `DELETE /attendances/:id`

Path parameter: `id` attendance.

Response `200`:

```json
{
  "status": "sukses",
  "message": "deleted",
  "data": null
}
```
