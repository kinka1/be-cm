# Panduan Integrasi BNI QRIS Dynamic

Dokumen ini menjelaskan kebutuhan dan langkah development integrasi QRIS Dynamic BNI ke backend Calon Mantu.

## Status Implementasi Backend

Backend sudah disiapkan untuk development BNI QRIS:

- Config `.env` untuk BNI QRIS.
- Service `app/Services/Pos/BniQrisPaymentService.php`.
- Webhook `POST /api/payments/bni-qris/webhook`.
- Endpoint development `POST /api/dev/bni-qris/create-test`.
- Flow order QRIS bisa memilih gateway melalui `QRIS_GATEWAY`.

Jika `QRIS_GATEWAY=midtrans`, sistem tetap memakai Midtrans.

Jika `QRIS_GATEWAY=bni`, sistem memakai BNI QRIS service.

## 1. Yang Dibutuhkan Dari BNI

Minta ke BNI atau portal BNI Digital Services:

- Akses QRIS Dynamic API, bukan QRIS statis.
- Sandbox base URL.
- Production base URL.
- Endpoint token, jika API memakai OAuth.
- Endpoint create QRIS.
- Endpoint inquiry status.
- Format callback/webhook.
- Client ID.
- Client Secret.
- Merchant ID.
- Terminal ID atau Store ID, jika ada.
- Private key, jika API memakai RSA signature.
- Public key BNI, jika callback perlu divalidasi RSA.
- Webhook secret, jika callback memakai HMAC.
- Dokumentasi signature request.
- Dokumentasi signature callback.
- Daftar status transaksi.
- Apakah perlu IP whitelist, VPN, atau mTLS.

## 2. Environment Variable

Tambahkan ke `.env` development:

```env
QRIS_GATEWAY=bni

BNI_QRIS_BASE_URL=https://sandbox-base-url-dari-bni
BNI_QRIS_TOKEN_PATH=/path/token-jika-ada
BNI_QRIS_CREATE_PATH=/path/create-qris
BNI_QRIS_INQUIRY_PATH=/path/inquiry
BNI_QRIS_CLIENT_ID=isi_dari_bni
BNI_QRIS_CLIENT_SECRET=isi_dari_bni
BNI_QRIS_MERCHANT_ID=isi_dari_bni
BNI_QRIS_TERMINAL_ID=isi_dari_bni
BNI_QRIS_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----..."
BNI_QRIS_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----..."
BNI_QRIS_WEBHOOK_SECRET=isi_jika_hmac
BNI_QRIS_SIGNATURE_MODE=none
BNI_QRIS_TIMEOUT=30
BNI_QRIS_IS_PRODUCTION=false
```

Pilihan `BNI_QRIS_SIGNATURE_MODE`:

- `none`: tidak menandatangani request. Gunakan hanya jika sandbox BNI belum butuh signature atau untuk development awal.
- `hmac_sha256`: memakai `client_secret` untuk signature header `X-SIGNATURE`.
- `rsa_sha256`: memakai `BNI_QRIS_PRIVATE_KEY` untuk signature header `X-SIGNATURE`.

Catatan: format signature BNI harus disesuaikan dengan dokumentasi resmi. Scaffold saat ini memakai format default:

```text
X-SIGNATURE = sign(timestamp + "." + json_body)
```

Jika dokumentasi BNI memakai string-to-sign berbeda, ubah method `signature()` di `BniQrisPaymentService`.

## 3. Callback URL Yang Didaftarkan Ke BNI

Untuk production:

```text
https://api.calon-mantoe.cloud/api/payments/bni-qris/webhook
```

Untuk development lokal gunakan tunnel:

```text
https://xxxx.ngrok-free.app/api/payments/bni-qris/webhook
```

## 4. Endpoint Development Untuk Hit API BNI

Endpoint ini dibuat agar kamu bisa test create QRIS langsung ke BNI tanpa membuat order POS.

```http
POST /api/dev/bni-qris/create-test
Authorization: Bearer <token>
Content-Type: application/json
```

Request body:

```json
{
  "order_id": "DEV-BNI-0001",
  "amount": 10000,
  "customer_name": "Customer Test",
  "description": "Test QRIS BNI",
  "currency": "IDR"
}
```

Response sukses dari backend:

```json
{
  "status": "sukses",
  "message": "ok",
  "data": {
    "payment_gateway": "bni_qris",
    "gateway_order_id": "DEV-BNI-0001",
    "gateway_transaction_id": "...",
    "qris_transaction_id": "...",
    "qr_string": "...",
    "qr_image_url": "...",
    "expires_at": "...",
    "gateway_response": {}
  }
}
```

Jika config belum lengkap, response akan tetap sukses dari backend tetapi `gateway_response.message` berisi config yang kurang.

## 5. Cara Test Dengan Curl

Login dulu untuk mendapatkan token:

```bash
curl -X POST "http://127.0.0.1:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -d "{\"username\":\"admin\",\"password\":\"password\"}"
```

Copy token dari response, lalu test BNI QRIS:

```bash
curl -X POST "http://127.0.0.1:8000/api/dev/bni-qris/create-test" \
  -H "Authorization: Bearer TOKEN_ANDA" \
  -H "Content-Type: application/json" \
  -d "{\"order_id\":\"DEV-BNI-0001\",\"amount\":10000,\"customer_name\":\"Customer Test\",\"description\":\"Test QRIS BNI\"}"
```

## 6. Payload Yang Dikirim Ke BNI Saat Create QRIS

Scaffold saat ini mengirim payload default berikut:

```json
{
  "merchant_id": "BNI_QRIS_MERCHANT_ID",
  "terminal_id": "BNI_QRIS_TERMINAL_ID",
  "order_id": "ORD-...",
  "amount": 10000,
  "currency": "IDR",
  "customer_name": "Customer",
  "description": "Calon Mantu QRIS Payment",
  "callback_url": "https://api.calon-mantoe.cloud/api/payments/bni-qris/webhook"
}
```

Jika dokumentasi BNI memakai field berbeda, ubah method `createPayload()` di:

```text
app/Services/Pos/BniQrisPaymentService.php
```

Contoh field yang mungkin perlu disesuaikan:

- `merchant_id` menjadi `merchantId`.
- `terminal_id` menjadi `terminalId`.
- `order_id` menjadi `partnerReferenceNo`.
- `amount` menjadi object `{ "value": "10000.00", "currency": "IDR" }`.
- `callback_url` menjadi `notificationUrl`.

Ikuti dokumentasi resmi BNI.

## 7. Header Yang Dikirim Ke BNI

Scaffold mengirim header:

```text
X-CLIENT-ID: <BNI_QRIS_CLIENT_ID>
X-TIMESTAMP: <ISO timestamp>
X-EXTERNAL-ID: <UUID>
X-SIGNATURE: <signature jika mode bukan none>
Authorization: Bearer <token jika token_path diisi>
```

Jika BNI memakai nama header berbeda, ubah method `headers()` di `BniQrisPaymentService`.

## 8. Webhook BNI

Endpoint backend:

```http
POST /api/payments/bni-qris/webhook
```

Webhook akan:

- Validasi signature callback sesuai `BNI_QRIS_SIGNATURE_MODE`.
- Cari order berdasarkan `order_id`, `gateway_order_id`, atau `gateway_transaction_id`.
- Update `payments` dan `orders`.
- Deduct stock saat payment sukses.

Status sukses yang saat ini dikenali:

- `paid`
- `success`
- `settlement`
- `settled`
- `completed`

Status gagal yang saat ini dikenali:

- `failed`
- `expire`
- `expired`
- `cancel`
- `cancelled`
- `deny`
- `denied`

Jika status BNI berbeda, update mapping di:

```text
app/Http/Controllers/Api/Payments/BniQrisWebhookController.php
```

## 9. Mengaktifkan BNI Untuk Order POS

Set `.env`:

```env
QRIS_GATEWAY=bni
```

Lalu clear config:

```bash
php artisan config:clear
```

Order QRIS dari endpoint berikut akan memakai BNI:

- `POST /api/pos/qr-orders`
- `POST /api/pos/cashier-orders` dengan `payment_method=qris`

Jika ingin balik ke Midtrans:

```env
QRIS_GATEWAY=midtrans
```

## 10. Hal Yang Harus Disesuaikan Setelah Mendapat Dokumentasi Resmi BNI

Checklist penyesuaian:

- Sesuaikan `BNI_QRIS_CREATE_PATH`.
- Sesuaikan `BNI_QRIS_TOKEN_PATH` jika ada OAuth.
- Sesuaikan payload di `createPayload()`.
- Sesuaikan header di `headers()`.
- Sesuaikan signature di `signature()`.
- Sesuaikan normalisasi response di `normalizeCreateResponse()`.
- Sesuaikan validasi webhook di `isValidWebhookSignature()`.
- Sesuaikan mapping status di `BniQrisWebhookController`.

## 11. Testing Checklist

- Test `POST /api/dev/bni-qris/create-test` sampai mendapat QR string atau QR image.
- Scan QR sandbox dari BNI.
- Pastikan webhook masuk ke backend.
- Pastikan order berubah menjadi `payment_status=paid`.
- Pastikan payment berubah menjadi `payment_status=success`.
- Pastikan stok hanya berkurang satu kali.
- Test status expired/gagal.
- Test inquiry jika endpoint BNI sudah diberikan.
- Test production dengan nominal kecil setelah UAT disetujui BNI.
