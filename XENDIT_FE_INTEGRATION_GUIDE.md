# Xendit QRIS Frontend Integration Guide

## Overview

Backend sudah mendukung pembayaran QRIS via Xendit untuk flow QR order.

Frontend tidak perlu mengirim nama gateway. Gateway dipilih dari konfigurasi backend:

```env
QRIS_GATEWAY=xendit
```

Integrasi awal ini berlaku untuk endpoint QR order:

```http
POST /api/pos/qr-orders
```

Cashier order dengan `payment_method = qris` masih mengikuti flow manual paid seperti sebelumnya.

## Backend Environment

Backend development/sandbox menggunakan konfigurasi berikut:

```env
QRIS_GATEWAY=xendit
XENDIT_SECRET_KEY=xnd_development_xxx
XENDIT_WEBHOOK_TOKEN=xxx
XENDIT_BASE_URL=https://api.xendit.co
XENDIT_CALLBACK_URL=https://api.calon-mantoe.cloud/api/payments/xendit/webhook
XENDIT_QRIS_EXPIRES_MINUTES=30
XENDIT_IS_PRODUCTION=false
```

Secret key dan webhook token hanya untuk backend. Jangan simpan atau expose key ini di frontend.

## Webhook URL

Daftarkan webhook URL berikut di dashboard Xendit development/sandbox:

```text
https://api.calon-mantoe.cloud/api/payments/xendit/webhook
```

Jika backend berjalan lokal, gunakan public tunnel seperti ngrok atau Cloudflare Tunnel:

```text
https://xxxx.ngrok-free.app/api/payments/xendit/webhook
```

## Create QR Order

### Endpoint

```http
POST /api/pos/qr-orders
```

### Headers

```http
Accept: application/json
Content-Type: application/json
```

Jika environment membutuhkan auth, tambahkan:

```http
Authorization: Bearer {token}
```

### Request Body

```json
{
  "qr_code": "TABLE-QR-001",
  "customer_name": "Budi",
  "discount": 0,
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "notes": "Less ice"
    },
    {
      "product_id": 2,
      "quantity": 1,
      "notes": null
    }
  ]
}
```

### Request Fields

| Field | Type | Required | Description |
|---|---|---:|---|
| `qr_code` | string | Ya | QR code meja |
| `customer_name` | string | Tidak | Nama customer |
| `discount` | number | Tidak | Diskon order, default `0` |
| `items` | array | Ya | Daftar item menu |
| `items[].product_id` | integer | Ya | ID produk/menu |
| `items[].quantity` | number | Ya | Jumlah item |
| `items[].notes` | string/null | Tidak | Catatan item |

## Success Response

```json
{
  "status": "sukses",
  "message": "created",
  "data": {
    "id": 100,
    "store_id": 3,
    "order_number": "ORD-20260813120000-1234",
    "table_id": 1,
    "order_type": "dine_in_qr",
    "customer_name": "Budi",
    "order_date": "2026-08-13 12:00:00",
    "subtotal": "52000.00",
    "tax": "0.00",
    "discount": "0.00",
    "payment_fee": "0.00",
    "total_amount": "52000.00",
    "payment_method": "qris",
    "payment_status": "pending",
    "order_status": "pending",
    "details": [
      {
        "id": 1,
        "order_id": 100,
        "product_id": 1,
        "quantity": "2.0000",
        "unit_price": "18000.00",
        "subtotal": "36000.00",
        "notes": "Less ice",
        "product": {
          "id": 1,
          "product_name": "Americano",
          "sku": "CM-AMERICANO",
          "selling_price": "18000.00"
        }
      }
    ],
    "payment": {
      "id": 10,
      "order_id": 100,
      "payment_method": "qris",
      "payment_gateway": "xendit",
      "amount_paid": "52000.00",
      "change_amount": "0.00",
      "qris_transaction_id": "qr_xxx",
      "gateway_order_id": "ORD-20260813120000-1234",
      "gateway_transaction_id": "qr_xxx",
      "gateway_response": {
        "id": "qr_xxx",
        "reference_id": "ORD-20260813120000-1234",
        "type": "DYNAMIC",
        "currency": "IDR",
        "amount": 52000,
        "status": "ACTIVE",
        "qr_string": "000201010212..."
      },
      "payment_fee": "0.00",
      "payment_status": "pending"
    }
  }
}
```

## Render QR Code

Frontend mengambil QR string dari:

```js
const qrString = response.data.payment.gateway_response.qr_string;
```

Kemudian render `qrString` menggunakan QR code library di frontend.

Contoh pengecekan gateway:

```js
const order = response.data;
const payment = order.payment;

if (payment.payment_gateway === "xendit") {
  const qrString = payment.gateway_response?.qr_string;
  // Render qrString as QR code
}
```

## Payment Status Flow

Saat QR order dibuat:

```text
order.payment_status = pending
order.order_status = pending
payment.payment_status = pending
payment.payment_gateway = xendit
```

Setelah customer membayar dan Xendit mengirim webhook sukses:

```text
order.payment_status = paid
order.order_status = preparing
payment.payment_status = success
```

Jika pembayaran gagal/expired/cancelled:

```text
order.payment_status = cancelled
order.order_status = cancelled
payment.payment_status = failed
```

## Polling Status Order

Frontend bisa cek status order dengan endpoint:

```http
GET /api/pos/orders/{order_id}
```

Contoh response setelah payment sukses:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "id": 100,
    "payment_status": "paid",
    "order_status": "preparing",
    "payment": {
      "payment_gateway": "xendit",
      "payment_status": "success"
    }
  }
}
```

Rekomendasi polling:

```text
Interval: 3-5 detik
Stop polling jika payment_status = paid/cancelled atau order_status = preparing/cancelled
```

## Validation Error Example

Jika stok tidak cukup:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "stock": [
      "Stok produk tidak mencukupi"
    ]
  }
}
```

Jika `qr_code` tidak ditemukan, backend akan mengembalikan error Laravel `404`.

## Important Notes For FE

- Jangan menyimpan `XENDIT_SECRET_KEY` di frontend.
- Jangan menyimpan `XENDIT_WEBHOOK_TOKEN` di frontend.
- Frontend hanya perlu memakai `qr_string` dari response backend.
- Frontend tidak perlu call API Xendit langsung.
- Webhook Xendit diproses backend.
- Status paid/preparing baru berubah setelah webhook sukses diterima backend.
- Jika sedang development lokal, webhook butuh tunnel publik.

## Minimal FE Flow

1. Customer pilih menu.
2. FE submit ke `POST /api/pos/qr-orders`.
3. FE ambil `data.payment.gateway_response.qr_string`.
4. FE render QR code.
5. FE polling `GET /api/pos/orders/{id}`.
6. Jika `payment_status = paid`, arahkan customer ke halaman order diproses.
7. Jika `payment_status = cancelled`, tampilkan pembayaran gagal/expired.
