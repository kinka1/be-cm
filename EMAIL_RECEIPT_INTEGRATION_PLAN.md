# Email Receipt Integration Plan

## Objective

Mengirimkan struk pembayaran ke email customer setelah pembayaran berhasil.

Untuk tahap awal, flow yang direkomendasikan adalah mengirim email struk otomatis saat webhook payment sukses diterima backend, terutama untuk QR order Xendit.

## Current Condition

Endpoint QR order saat ini:

```http
POST /api/pos/qr-orders
```

Request saat ini belum menerima email customer.

Contoh request saat ini:

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
    }
  ]
}
```

Tabel `orders` saat ini juga belum punya field:

```text
customer_email
```

Jadi backend belum punya tujuan email untuk mengirim struk.

## Recommended Flow

1. Customer membuat QR order dari FE.
2. FE mengirim `customer_email` sebagai optional field.
3. Backend menyimpan `customer_email` ke tabel `orders`.
4. Customer melakukan pembayaran QRIS.
5. Payment gateway mengirim webhook sukses ke backend.
6. Backend update payment dan order menjadi paid.
7. Backend mengirim email struk ke `orders.customer_email`.
8. Jika `customer_email` kosong, proses email dilewati dan webhook tetap sukses.

## Proposed QR Order Request

```json
{
  "qr_code": "TABLE-QR-001",
  "customer_name": "Budi",
  "customer_email": "budi@example.com",
  "discount": 0,
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "notes": "Less ice"
    }
  ]
}
```

## Request Field Changes

| Field | Type | Required | Description |
|---|---|---:|---|
| `customer_email` | string/email | Tidak | Email customer untuk menerima struk pembayaran |

Validation:

```php
'customer_email' => ['nullable', 'email', 'max:255']
```

## Database Change

Create migration:

```text
database/migrations/yyyy_mm_dd_hhmmss_add_customer_email_to_orders_table.php
```

Column:

```php
$table->string('customer_email')->nullable()->after('customer_name');
```

Rollback:

```php
$table->dropColumn('customer_email');
```

## Model Change

File:

```text
app/Models/Order.php
```

Add to `$fillable`:

```php
'customer_email',
```

## QR Order Service Change

File:

```text
app/Services/Pos/CreateQrOrderService.php
```

Add when creating order:

```php
'customer_email' => $data['customer_email'] ?? null,
```

## Mail Class

Create file:

```text
app/Mail/OrderReceiptMail.php
```

Purpose:

- Build email receipt.
- Load order relations if needed.
- Render Blade email template.

Suggested data:

```php
public function __construct(public Order $order)
{
}
```

Subject example:

```text
Struk Pembayaran Calon Mantu - {order_number}
```

## Email Template

Create file:

```text
resources/views/emails/order-receipt.blade.php
```

Recommended content:

- Store name
- Order number
- Customer name
- Customer email
- Order date
- Payment method
- Payment gateway
- Payment status
- Item list
- Quantity
- Unit price
- Item subtotal
- Discount
- Tax
- Payment fee
- Total amount
- Thank you message

## Receipt Email Service

Create file:

```text
app/Services/Pos/ReceiptEmailService.php
```

Responsibilities:

- Skip if `customer_email` empty.
- Skip if order is not paid.
- Send email after DB transaction commits.
- Prevent webhook failure if mail sending fails.

Recommended implementation behavior:

```php
public function send(Order $order): void
{
    if (!$order->customer_email || $order->payment_status !== 'paid') {
        return;
    }

    DB::afterCommit(function () use ($order): void {
        Mail::to($order->customer_email)->send(new OrderReceiptMail($order));
    });
}
```

Optional hardening:

- Wrap mail sending with `try/catch`.
- Log mail errors instead of throwing.
- Use queue later if queue workers are available.

## Webhook Integration Points

Send receipt after payment success in these files:

```text
app/Http/Controllers/Api/Payments/XenditWebhookController.php
app/Http/Controllers/Api/Payments/MidtransWebhookController.php
app/Http/Controllers/Api/Payments/BniQrisWebhookController.php
```

Recommended first implementation:

- Integrate with Xendit webhook first.
- Extend to Midtrans and BNI QRIS after the Xendit flow is verified.

Xendit success block currently updates:

```php
$payment->payment_status = 'success';
$payment->payment_date = now();
$order->update(['payment_status' => 'paid', 'order_status' => 'preparing']);
$stockDeduction->deduct($order);
```

After stock deduction, call:

```php
$receiptEmail->send($order->fresh(['store', 'details.product', 'payment']));
```

## Cashier Order Consideration

Cashier/manual payment currently marks payment as paid immediately.

If email receipt is also needed for cashier/cart checkout, add optional `customer_email` to:

```text
POST /api/pos/cashier-orders
POST /api/pos/cart/checkout
POST /api/pos/carts/{cart}/checkout
```

Then send receipt after manual payment success.

Recommended phased approach:

1. Phase 1: QR order + Xendit payment receipt.
2. Phase 2: Midtrans/BNI webhook receipt.
3. Phase 3: Cashier/manual payment receipt.

## Mail Environment

Development safe default:

```env
MAIL_MAILER=log
```

With this config, email is written to logs and not sent to a real inbox.

Production SMTP example:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@calon-mantoe.cloud
MAIL_FROM_NAME="Calon Mantu"
```

After changing mail env:

```bash
php artisan optimize:clear
```

## Response Impact

After adding `customer_email`, QR order response will include:

```json
{
  "customer_name": "Budi",
  "customer_email": "budi@example.com"
}
```

Existing FE can ignore this field if not needed.

## Error Handling Recommendation

Email sending should not fail the payment webhook.

If email fails:

- Payment should remain successful.
- Order should remain paid.
- Error should be logged.
- Webhook should still return success to payment gateway.

## Verification Checklist

After implementation:

```bash
php -l app/Mail/OrderReceiptMail.php
php -l app/Services/Pos/ReceiptEmailService.php
php -l app/Http/Controllers/Api/Payments/XenditWebhookController.php
php artisan migrate
php artisan l5-swagger:generate
php artisan test
```

Manual test flow:

1. Set `MAIL_MAILER=log`.
2. Create QR order with `customer_email`.
3. Simulate or receive Xendit webhook success.
4. Confirm order becomes `payment_status=paid`.
5. Confirm email receipt appears in Laravel logs.

## Security Notes

- Never expose SMTP password in FE.
- Never expose Xendit secret key in FE.
- Customer email should only be stored if customer provides it.
- Public status endpoint should not expose customer email unless explicitly needed.
