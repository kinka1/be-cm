<?php

namespace App\Services\Pos;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class MidtransPaymentService
{
    public function createQrisTransaction(Order $order): array
    {
        $serverKey = config('services.midtrans.server_key');
        $baseUrl = config('services.midtrans.is_production')
            ? 'https://api.midtrans.com/v2/charge'
            : 'https://api.sandbox.midtrans.com/v2/charge';

        if (!$serverKey) {
            return [
                'gateway_order_id' => $order->order_number,
                'gateway_response' => [
                    'message' => 'MIDTRANS_SERVER_KEY is not configured',
                ],
            ];
        }

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->post($baseUrl, [
                'payment_type' => 'qris',
                'transaction_details' => [
                    'order_id' => $order->order_number,
                    'gross_amount' => (int) round($order->total_amount),
                ],
                'customer_details' => [
                    'first_name' => $order->customer_name ?: 'Customer',
                ],
            ]);

        return [
            'gateway_order_id' => $order->order_number,
            'gateway_transaction_id' => $response->json('transaction_id'),
            'gateway_response' => $response->json() ?: ['body' => $response->body()],
        ];
    }

    public function isValidSignature(array $payload): bool
    {
        $serverKey = config('services.midtrans.server_key');

        if (!$serverKey || empty($payload['signature_key'])) {
            return false;
        }

        $signature = hash('sha512', ($payload['order_id'] ?? '').($payload['status_code'] ?? '').($payload['gross_amount'] ?? '').$serverKey);

        return hash_equals($signature, $payload['signature_key']);
    }
}
