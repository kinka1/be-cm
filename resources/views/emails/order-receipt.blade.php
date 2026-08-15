<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk Pembayaran</title>
</head>
<body style="font-family: Arial, sans-serif; color: #222; line-height: 1.5;">
    <h2 style="margin-bottom: 4px;">Struk Pembayaran</h2>
    <p style="margin-top: 0;">{{ $order->store?->store_name ?? 'Calon Mantu' }}</p>

    <table style="width: 100%; max-width: 620px; border-collapse: collapse; margin-bottom: 16px;">
        <tr>
            <td style="padding: 4px 0;">Nomor Order</td>
            <td style="padding: 4px 0; text-align: right;"><strong>{{ $order->order_number }}</strong></td>
        </tr>
        <tr>
            <td style="padding: 4px 0;">Tanggal</td>
            <td style="padding: 4px 0; text-align: right;">{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d M Y H:i') : '-' }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0;">Customer</td>
            <td style="padding: 4px 0; text-align: right;">{{ $order->customer_name ?: '-' }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0;">Email</td>
            <td style="padding: 4px 0; text-align: right;">{{ $order->customer_email ?: '-' }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0;">Pembayaran</td>
            <td style="padding: 4px 0; text-align: right;">{{ strtoupper((string) $order->payment_method) }}{{ $order->payment?->payment_gateway ? ' - '.strtoupper($order->payment->payment_gateway) : '' }}</td>
        </tr>
    </table>

    <table style="width: 100%; max-width: 620px; border-collapse: collapse; margin-bottom: 16px;">
        <thead>
            <tr>
                <th style="border-bottom: 1px solid #ddd; padding: 8px 0; text-align: left;">Item</th>
                <th style="border-bottom: 1px solid #ddd; padding: 8px 0; text-align: right;">Qty</th>
                <th style="border-bottom: 1px solid #ddd; padding: 8px 0; text-align: right;">Harga</th>
                <th style="border-bottom: 1px solid #ddd; padding: 8px 0; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->details as $detail)
                <tr>
                    <td style="border-bottom: 1px solid #eee; padding: 8px 0;">{{ $detail->product?->product_name ?? 'Item' }}</td>
                    <td style="border-bottom: 1px solid #eee; padding: 8px 0; text-align: right;">{{ (float) $detail->quantity }}</td>
                    <td style="border-bottom: 1px solid #eee; padding: 8px 0; text-align: right;">Rp {{ number_format((float) $detail->unit_price, 0, ',', '.') }}</td>
                    <td style="border-bottom: 1px solid #eee; padding: 8px 0; text-align: right;">Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; max-width: 620px; border-collapse: collapse;">
        <tr>
            <td style="padding: 4px 0;">Subtotal</td>
            <td style="padding: 4px 0; text-align: right;">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0;">Diskon</td>
            <td style="padding: 4px 0; text-align: right;">Rp {{ number_format((float) $order->discount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0;">Biaya Pembayaran</td>
            <td style="padding: 4px 0; text-align: right;">Rp {{ number_format((float) $order->payment_fee, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-size: 18px;"><strong>Total</strong></td>
            <td style="padding: 8px 0; text-align: right; font-size: 18px;"><strong>Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <p style="margin-top: 24px;">Terima kasih telah melakukan pembayaran di {{ $order->store?->store_name ?? 'Calon Mantu' }}.</p>
</body>
</html>
