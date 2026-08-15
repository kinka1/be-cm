# POS Orders Paginated API

## Endpoint

```http
GET /api/pos/orders/paginated
```

Endpoint ini digunakan untuk mengambil list POS orders dengan pagination.

Endpoint ini berbeda dari:

```http
GET /api/pos/orders
```

`GET /api/pos/orders` mengembalikan seluruh data tanpa pagination, sedangkan endpoint ini mengembalikan data dengan pagination Laravel.

## Headers

```http
Authorization: Bearer {token}
Accept: application/json
```

## Query Params

| Param | Type | Required | Description |
|---|---|---:|---|
| `store_id` | integer | Tidak | Filter order berdasarkan store |
| `order_status` | string | Tidak | Filter berdasarkan status order |
| `payment_status` | string | Tidak | Filter berdasarkan status pembayaran |
| `date` | date `YYYY-MM-DD` | Tidak | Filter order berdasarkan tanggal `order_date` |
| `page` | integer | Tidak | Nomor halaman pagination |
| `per_page` | integer | Tidak | Jumlah item per halaman, default `15`, min `1`, max `100` |

## Allowed Values

### order_status

| Value | Description |
|---|---|
| `pending` | Order dibuat, belum diproses |
| `preparing` | Order sedang diproses |
| `ready` | Order siap |
| `completed` | Order selesai |
| `cancelled` | Order dibatalkan |

### payment_status

| Value | Description |
|---|---|
| `pending` | Pembayaran belum selesai |
| `paid` | Pembayaran berhasil |
| `cancelled` | Pembayaran gagal/dibatalkan |

## Example Requests

Ambil order paginated default:

```http
GET /api/pos/orders/paginated
```

Filter berdasarkan store:

```http
GET /api/pos/orders/paginated?store_id=3
```

Filter berdasarkan tanggal:

```http
GET /api/pos/orders/paginated?date=2026-08-13
```

Pagination:

```http
GET /api/pos/orders/paginated?page=1&per_page=10
```

Filter lengkap:

```http
GET /api/pos/orders/paginated?store_id=3&date=2026-08-13&payment_status=paid&order_status=completed&page=1&per_page=10
```

## Success Response 200

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 147,
        "store_id": 3,
        "order_number": "ORD-20260813151418-4907",
        "table_id": 2,
        "table_label": null,
        "order_type": "dine_in_qr",
        "customer_name": "sa",
        "employee_id": null,
        "cashier_session_id": null,
        "order_date": "2026-08-13T22:14:18.000000Z",
        "subtotal": "18000.00",
        "tax": "0.00",
        "discount": "0.00",
        "payment_fee": "0.00",
        "total_amount": "18000.00",
        "payment_method": "qris",
        "payment_status": "pending",
        "order_status": "pending",
        "created_at": "2026-08-13T22:14:18.000000Z",
        "updated_at": "2026-08-13T22:14:18.000000Z",
        "store": {
          "id": 3,
          "store_name": "Calon Mantu",
          "code": "CM",
          "address": "Alamat store",
          "phone": "0812345678",
          "is_active": true,
          "created_at": "2026-07-17T12:29:10.000000Z",
          "updated_at": "2026-07-17T12:29:10.000000Z"
        },
        "details": [
          {
            "id": 456,
            "order_id": 147,
            "product_id": 9,
            "quantity": "1.0000",
            "unit_price": "18000.00",
            "subtotal": "18000.00",
            "notes": "Biasa",
            "created_at": "2026-08-13T22:14:18.000000Z",
            "product": {
              "id": 9,
              "store_id": 3,
              "product_name": "French Fries",
              "sku": "CM-FRENCH-FRIES",
              "category_id": 3,
              "product_type": "menu",
              "description": null,
              "unit_of_measure": "pcs",
              "minimum_stock": "0.0000",
              "current_stock": "1000000.0000",
              "cost_price": "0.00",
              "selling_price": "18000.00",
              "is_active": 1,
              "created_at": "2026-07-19T03:23:25.000000Z",
              "updated_at": "2026-07-19T03:23:25.000000Z",
              "deleted_at": null
            }
          }
        ],
        "payment": {
          "id": 147,
          "order_id": 147,
          "payment_method": "qris",
          "payment_gateway": "xendit",
          "amount_paid": "18000.00",
          "change_amount": "0.00",
          "qris_transaction_id": "qr_xxx",
          "gateway_order_id": "ORD-20260813151418-4907",
          "gateway_transaction_id": "qr_xxx",
          "gateway_response": {
            "id": "qr_xxx",
            "external_id": "ORD-20260813151418-4907",
            "amount": 18000,
            "status": "ACTIVE",
            "qr_string": "000201010212..."
          },
          "payment_fee": "0.00",
          "payment_date": null,
          "payment_status": "pending",
          "created_at": "2026-08-13T22:14:18.000000Z"
        }
      }
    ],
    "first_page_url": "https://api.calon-mantoe.cloud/api/pos/orders/paginated?page=1",
    "from": 1,
    "last_page": 5,
    "last_page_url": "https://api.calon-mantoe.cloud/api/pos/orders/paginated?page=5",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "https://api.calon-mantoe.cloud/api/pos/orders/paginated?page=1",
        "label": "1",
        "active": true
      },
      {
        "url": "https://api.calon-mantoe.cloud/api/pos/orders/paginated?page=2",
        "label": "2",
        "active": false
      },
      {
        "url": "https://api.calon-mantoe.cloud/api/pos/orders/paginated?page=2",
        "label": "Next &raquo;",
        "active": false
      }
    ],
    "next_page_url": "https://api.calon-mantoe.cloud/api/pos/orders/paginated?page=2",
    "path": "https://api.calon-mantoe.cloud/api/pos/orders/paginated",
    "per_page": 10,
    "prev_page_url": null,
    "to": 10,
    "total": 50
  }
}
```

## Empty Response 200

Jika data tidak ditemukan, response tetap sukses dengan pagination kosong:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "current_page": 1,
    "data": [],
    "first_page_url": "https://api.calon-mantoe.cloud/api/pos/orders/paginated?page=1",
    "from": null,
    "last_page": 1,
    "last_page_url": "https://api.calon-mantoe.cloud/api/pos/orders/paginated?page=1",
    "links": [],
    "next_page_url": null,
    "path": "https://api.calon-mantoe.cloud/api/pos/orders/paginated",
    "per_page": 15,
    "prev_page_url": null,
    "to": null,
    "total": 0
  }
}
```

## Validation Error 422

### Invalid date format

Request:

```http
GET /api/pos/orders/paginated?date=13-08-2026
```

Response:

```json
{
  "message": "The date field must match the format Y-m-d.",
  "errors": {
    "date": [
      "The date field must match the format Y-m-d."
    ]
  }
}
```

### Invalid per_page

Request:

```http
GET /api/pos/orders/paginated?per_page=200
```

Response:

```json
{
  "message": "The per page field must not be greater than 100.",
  "errors": {
    "per_page": [
      "The per page field must not be greater than 100."
    ]
  }
}
```

## Notes

- Endpoint ini menggunakan pagination Laravel.
- Sorting default adalah order terbaru lebih dulu berdasarkan `order_date DESC`.
- Filter `date` memakai kolom `order_date`.
- Format `date` wajib `YYYY-MM-DD`.
- Timezone aplikasi saat ini memakai `Asia/Jakarta` jika `APP_TIMEZONE=Asia/Jakarta` aktif di backend.
- Response tetap memuat relasi `store`, `details.product`, dan `payment`.
