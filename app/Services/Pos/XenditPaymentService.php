<?php

namespace App\Services\Pos;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class XenditPaymentService
{
    public function createQrisTransaction(Order $order): array
    {
        $secretKey = config('services.xendit.secret_key');
        $baseUrl = rtrim((string) config('services.xendit.base_url'), '/');

        if (!$secretKey) {
            return [
                'payment_gateway' => 'xendit',
                'gateway_order_id' => $order->order_number,
                'gateway_response' => [
                    'message' => 'XENDIT_SECRET_KEY is not configured',
                ],
            ];
        }

        $response = Http::withBasicAuth($secretKey, '')
            ->acceptJson()
            ->post($baseUrl.'/qr_codes', [
                'reference_id' => $order->order_number,
                'type' => 'DYNAMIC',
                'currency' => 'IDR',
                'amount' => (int) round($order->total_amount),
                'expires_at' => now()->addMinutes((int) config('services.xendit.qris_expires_minutes', 30))->toISOString(),
            ]);

        $json = $response->json() ?: ['body' => $response->body()];

        return [
            'payment_gateway' => 'xendit',
            'gateway_order_id' => data_get($json, 'reference_id') ?: $order->order_number,
            'gateway_transaction_id' => data_get($json, 'id'),
            'qris_transaction_id' => data_get($json, 'id'),
            'gateway_response' => $json,
        ];
    }

    public function isValidWebhookToken(?string $token): bool
    {
        $expected = config('services.xendit.webhook_token');

        if (!$expected) {
            return true;
        }

        return is_string($token) && hash_equals((string) $expected, $token);
    }
}
