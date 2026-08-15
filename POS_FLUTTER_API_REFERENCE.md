# POS Flutter API Reference

Dokumen ini untuk aplikasi Flutter POS kasir internal Calon Mantu.

Scope saat ini:

- Kasir internal wajib login dengan Bearer token.
- Pembayaran POS bersifat manual/offline tanpa payment gateway.
- Metode pembayaran: `cash`, `qris`, `transfer`.
- QRIS di cashier order hanya pencatatan manual, bukan Midtrans/BNI.
- Transfer di cashier order hanya pencatatan manual.
- Endpoint QR meja customer tidak dibahas di dokumen ini.

## Base URL

```text
https://api.calon-mantoe.cloud/api
```

Header endpoint login:

```http
Accept: application/json
Content-Type: application/json
```

Header endpoint kasir:

```http
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

## Flutter Flow

```text
Login
-> simpan token
-> GET /api/me
-> GET /api/me/stores
-> pilih store
-> POST /api/me/current-store
-> GET /api/pos/cashier-sessions/current?store_id=1
-> jika data null: POST /api/pos/cashier-sessions/open
-> GET /api/pos/payment-methods
-> GET /api/pos/menu?store_id=1
-> simpan cart: POST /api/pos/cart/items
-> checkout cart: POST /api/pos/cart/checkout
-> tampilkan receipt
-> GET /api/pos/cashier-sessions/{id}/summary
-> tutup kasir: POST /api/pos/cashier-sessions/{id}/close
```

## Response Format

Sukses:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {}
}
```

Gagal:

```json
{
  "status": "gagal",
  "message": "pesan error",
  "data": null
}
```

Validasi Laravel:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field": ["Pesan error"]
  }
}
```

## Auth

### Login

```http
POST /api/auth/login
```

Request body:

```json
{
  "username": "operator",
  "password": "password"
}
```

Response `200`:

```json
{
  "status": "sukses",
  "message": "logged in",
  "data": {
    "user": {
      "id": 4,
      "employee_id": 4,
      "current_store_id": 1,
      "name": "Operator"
    },
    "token": "1|plain-text-token"
  }
}
```

### Current User

```http
GET /api/me
```

Response `200`:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "id": 4,
    "employee_id": 4,
    "current_store_id": 1,
    "employee": {
      "id": 4,
      "store_id": 1,
      "full_name": "Operator"
    },
    "current_store": {
      "id": 1,
      "store_name": "Calon Mantu Utama"
    }
  }
}
```

## Payment Methods

### List Payment Methods

```http
GET /api/pos/payment-methods
```

Auth: Bearer token.

Response `200`:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": [
    {
      "value": "cash",
      "label": "Tunai",
      "requires_amount_paid": true,
      "has_change": true
    },
    {
      "value": "qris",
      "label": "QRIS",
      "requires_amount_paid": false,
      "has_change": false
    },
    {
      "value": "transfer",
      "label": "Transfer",
      "requires_amount_paid": false,
      "has_change": false
    }
  ]
}
```

Catatan Flutter:

- `cash` butuh input uang diterima dan menghasilkan kembalian.
- `qris` tidak call gateway; backend mencatat pembayaran manual sukses.
- `transfer` tidak call gateway; backend mencatat pembayaran manual sukses.

## Cashier Session

### Check Current Session

```http
GET /api/pos/cashier-sessions/current?store_id=1
```

Jika belum buka kasir:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": null
}
```

Jika sudah buka kasir:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "id": 7,
    "store_id": 1,
    "employee_id": 4,
    "opening_cash": "500000.00",
    "status": "open"
  }
}
```

### Open Session

```http
POST /api/pos/cashier-sessions/open
```

Request body:

```json
{
  "store_id": 1,
  "opening_cash": 500000,
  "opening_notes": "Shift pagi"
}
```

Response `201`:

```json
{
  "status": "sukses",
  "message": "opened",
  "data": {
    "id": 7,
    "store_id": 1,
    "employee_id": 4,
    "opening_cash": 500000,
    "status": "open"
  }
}
```

## Menu

### List Menu

```http
GET /api/pos/menu?store_id=1&per_page=100&search=kopi
```

Query parameter:

| Parameter | Required | Keterangan |
|---|---:|---|
| `store_id` | Ya | Toko aktif |
| `category_id` | Tidak | Filter kategori |
| `search` | Tidak | Search produk |
| `per_page` | Tidak | Default `15`, max `100` |

Response `200` berisi pagination produk pada `data.data`.

## Create Cashier Order

Flutter bisa langsung checkout lewat endpoint ini, atau memakai endpoint cart di bawah. Jika memakai cart backend, gunakan `POST /api/pos/cart/checkout`.

```http
POST /api/pos/cashier-orders
```

Auth: Bearer token.

Request body umum:

| Field | Required | Keterangan |
|---|---:|---|
| `order_type` | Ya | `dine_in_cashier` atau `takeaway` |
| `store_id` | Ya | Toko aktif |
| `table_id` | Tidak | ID meja legacy jika ingin relasi ke tabel `calon_mantu` |
| `table_label` | Tidak | Label meja bebas tanpa relasi database, contoh `Meja 01` |
| `employee_id` | Ya | Ambil dari `GET /api/me` |
| `customer_name` | Tidak | Nama customer |
| `payment_method` | Ya | `cash`, `qris`, atau `transfer` |
| `amount_paid` | Wajib untuk cash | Uang diterima |
| `discount` | Tidak | Nominal diskon |
| `items` | Ya | Minimal 1 item |

Item body:

```json
{
  "product_id": 10,
  "quantity": 2,
  "notes": "Less ice"
}
```

### Cash Request

```json
{
  "order_type": "takeaway",
  "store_id": 1,
  "employee_id": 4,
  "customer_name": "Andi",
  "payment_method": "cash",
  "amount_paid": 50000,
  "discount": 0,
  "items": [
    {
      "product_id": 10,
      "quantity": 2,
      "notes": "Less ice"
    }
  ]
}
```

### QRIS Manual Request

```json
{
  "order_type": "takeaway",
  "store_id": 1,
  "employee_id": 4,
  "customer_name": "Andi",
  "payment_method": "qris",
  "discount": 0,
  "items": [
    {
      "product_id": 10,
      "quantity": 2
    }
  ]
}
```

### Transfer Manual Request

```json
{
  "order_type": "takeaway",
  "store_id": 1,
  "employee_id": 4,
  "customer_name": "Andi",
  "payment_method": "transfer",
  "discount": 0,
  "items": [
    {
      "product_id": 10,
      "quantity": 2
    }
  ]
}
```

Response `201`:

```json
{
  "status": "sukses",
  "message": "created",
  "data": {
    "id": 101,
    "order_number": "ORD-20260731140000-1234",
    "store_id": 1,
    "employee_id": 4,
    "cashier_session_id": 7,
    "subtotal": 36000,
    "discount": 0,
    "payment_fee": 0,
    "total_amount": 36000,
    "payment_method": "qris",
    "payment_status": "paid",
    "order_status": "preparing",
    "details": [],
    "payment": {
      "payment_method": "qris",
      "payment_gateway": null,
      "amount_paid": "36000.00",
      "change_amount": "0.00",
      "payment_status": "success"
    }
  }
}
```

Backend behavior:

- Semua metode membuat order `payment_status=paid`.
- Semua metode membuat order `order_status=preparing`.
- Semua metode membuat payment `payment_status=success`.
- Semua metode langsung mengurangi stok.
- Tidak ada field gateway yang diisi untuk cashier order.

## Cashier Summary

## POS Cart

Cart baru mendukung banyak card/keranjang bernama per `user + store_id`.

### List Cart Cards

```http
GET /api/pos/carts?store_id=1
```

Query parameter:

| Parameter | Required | Keterangan |
|---|---:|---|
| `store_id` | Ya | ID toko |
| `status` | Tidak | `active`, `checked_out`, atau `cancelled`. Default `active` |

Response `200`:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": [
    {
      "id": 1,
      "user_id": 4,
      "store_id": 1,
      "name": "Meja 01",
      "status": "active",
      "items": [],
      "subtotal": 0,
      "total_items": 0
    }
  ]
}
```

### Create Cart Card

```http
POST /api/pos/carts
```

Request body:

```json
{
  "store_id": 1,
  "name": "Meja 01"
}
```

Response `201`:

```json
{
  "status": "sukses",
  "message": "created",
  "data": {
    "id": 1,
    "user_id": 4,
    "store_id": 1,
    "name": "Meja 01",
    "status": "active",
    "items": [],
    "subtotal": 0,
    "total_items": 0
  }
}
```

### Get Cart Card

```http
GET /api/pos/carts/{cart}
```

### Rename Cart Card

```http
PATCH /api/pos/carts/{cart}
```

Request body:

```json
{
  "name": "Takeaway Budi"
}
```

### Delete Cart Card

```http
DELETE /api/pos/carts/{cart}
```

### Add Item To Cart Card

```http
POST /api/pos/carts/{cart}/items
```

Request body:

```json
{
  "product_id": 10,
  "quantity": 2,
  "notes": "Less ice"
}
```

Jika produk sudah ada di cart card, quantity akan ditambahkan.

### Update Item In Cart Card

```http
PATCH /api/pos/carts/{cart}/items/{item}
```

Request body:

```json
{
  "quantity": 3,
  "notes": "No sugar"
}
```

### Remove Item From Cart Card

```http
DELETE /api/pos/carts/{cart}/items/{item}
```

### Clear Cart Card Items

```http
DELETE /api/pos/carts/{cart}/items
```

### Checkout Cart Card

```http
POST /api/pos/carts/{cart}/checkout
```

Request body cash:

```json
{
  "order_type": "dine_in_cashier",
  "table_label": "Meja 01",
  "customer_name": "Andi",
  "payment_method": "cash",
  "amount_paid": 50000,
  "discount": 0
}
```

Gunakan `table_label` jika frontend hanya butuh menyimpan nama/nomor meja tanpa relasi ke tabel meja. Jangan kirim `table_id` jika tidak ingin order terkait ke data `calon_mantu`.

Request body QRIS manual:

```json
{
  "order_type": "dine_in_cashier",
  "table_label": "Meja 01",
  "customer_name": "Andi",
  "payment_method": "qris",
  "discount": 0
}
```

Jika `customer_name` kosong, checkout memakai nama cart card sebagai nama customer. Setelah checkout sukses, cart card berubah menjadi `checked_out`.

### Legacy Single Cart

Endpoint lama masih tersedia untuk satu cart otomatis per `user + store_id`.

### Get Cart

```http
GET /api/pos/cart?store_id=1
```

Response `200`:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "id": 1,
    "user_id": 4,
    "store_id": 1,
    "items": [],
    "subtotal": 0,
    "total_items": 0
  }
}
```

### Add Item

```http
POST /api/pos/cart/items
```

Request body:

```json
{
  "store_id": 1,
  "product_id": 10,
  "quantity": 2,
  "notes": "Less ice"
}
```

Jika produk sudah ada di cart, quantity akan ditambahkan.

### Update Item

```http
PATCH /api/pos/cart/items/{item}
```

Request body:

```json
{
  "quantity": 3,
  "notes": "No sugar"
}
```

### Remove Item

```http
DELETE /api/pos/cart/items/{item}
```

### Clear Cart

```http
DELETE /api/pos/cart?store_id=1
```

### Checkout Cart

```http
POST /api/pos/cart/checkout
```

Request body cash:

```json
{
  "store_id": 1,
  "order_type": "dine_in_cashier",
  "table_label": "Meja 01",
  "customer_name": "Andi",
  "payment_method": "cash",
  "amount_paid": 50000,
  "discount": 0
}
```

Gunakan `table_label` jika frontend hanya butuh menyimpan nama/nomor meja tanpa relasi ke tabel meja. Jangan kirim `table_id` jika tidak ingin order terkait ke data `calon_mantu`.

Request body QRIS manual:

```json
{
  "store_id": 1,
  "order_type": "dine_in_cashier",
  "table_label": "Meja 01",
  "customer_name": "Andi",
  "payment_method": "qris",
  "discount": 0
}
```

Request body transfer:

```json
{
  "store_id": 1,
  "order_type": "dine_in_cashier",
  "table_label": "Meja 01",
  "customer_name": "Andi",
  "payment_method": "transfer",
  "discount": 0
}
```

Checkout cart akan membuat order cashier, mengurangi stok, lalu mengosongkan cart jika sukses.

```http
GET /api/pos/cashier-sessions/{cashierSession}/summary
```

Response `200`:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "cashier_session_id": 7,
    "store_id": 1,
    "employee_id": 4,
    "status": "open",
    "opening_cash": 500000,
    "cash_sales": 100000,
    "qris_sales": 50000,
    "transfer_sales": 25000,
    "cash_in": 0,
    "cash_out": 0,
    "expected_cash": 600000,
    "closing_cash": null,
    "cash_difference": null,
    "total_orders": 3
  }
}
```

Catatan:

- `expected_cash` hanya menghitung uang fisik.
- `qris_sales` dan `transfer_sales` tidak menambah `expected_cash`.

## Close Cashier

```http
POST /api/pos/cashier-sessions/{cashierSession}/close
```

Request body:

```json
{
  "closing_cash": 600000,
  "closing_notes": "Sesuai"
}
```

## Orders

List:

```http
GET /api/pos/orders?store_id=1&payment_status=paid
```

Detail:

```http
GET /api/pos/orders/{order}
```

Update status:

```http
PATCH /api/pos/orders/{order}/status
```

Request body:

```json
{
  "order_status": "ready"
}
```

Status valid:

```text
preparing
ready
completed
cancelled
```

## Sales Report

Endpoint laporan penjualan dengan rentang waktu dan filter:

```http
GET /api/revenue/sales
```

Query parameter:

| Parameter | Required | Keterangan |
|---|---:|---|
| `from_date` | Ya | Tanggal mulai, format `YYYY-MM-DD` |
| `to_date` | Ya | Tanggal akhir, format `YYYY-MM-DD` |
| `store_id` | Tidak | Filter toko |
| `category_id` | Tidak | Filter kategori produk |
| `product_id` | Tidak | Filter produk |
| `payment_method` | Tidak | `cash`, `qris`, atau `transfer` |
| `group_by` | Tidak | `day`, `store`, `category`, `product`, atau `payment_method` |
| `include_orders` | Tidak | `true` untuk menyertakan pagination order |
| `per_page` | Tidak | Default `15`, max `100` |

Contoh laporan per produk:

```http
GET /api/revenue/sales?from_date=2026-07-01&to_date=2026-07-31&store_id=1&group_by=product
```

Contoh filter kategori dan metode pembayaran:

```http
GET /api/revenue/sales?from_date=2026-07-01&to_date=2026-07-31&category_id=2&payment_method=qris&group_by=day
```

Response `200`:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "filters": {
      "from_date": "2026-07-01",
      "to_date": "2026-07-31",
      "store_id": 1,
      "category_id": null,
      "product_id": null,
      "payment_method": null,
      "group_by": "product"
    },
    "summary": {
      "total_orders": 12,
      "total_items": 40,
      "gross_sales": 720000,
      "discount": 25000,
      "net_sales": 695000,
      "cash_revenue": 400000,
      "qris_revenue": 200000,
      "transfer_revenue": 95000
    },
    "breakdown": [
      {
        "product_id": 10,
        "product_name": "Kopi Susu",
        "sku": "MENU-KOPI-SUSU",
        "category_id": 2,
        "category_name": "Minuman",
        "total_orders": 10,
        "total_items": 20,
        "gross_sales": 360000,
        "discount": 0,
        "net_sales": 360000
      }
    ],
    "orders": null
  }
}
```

## Common Errors

Belum buka kasir:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "cashier_session": ["Operator belum membuka kasir untuk toko ini"]
  }
}
```

Cash kurang:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "amount_paid": ["Jumlah pembayaran kurang dari total order"]
  }
}
```

Stok tidak cukup:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "stock": ["Stok tidak mencukupi"]
  }
}
```
