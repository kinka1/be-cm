<?php

namespace App\Services\Pos;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

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
                'external_id' => $order->order_number,
                'type' => 'DYNAMIC',
                'amount' => (int) round($order->total_amount),
                'callback_url' => config('services.xendit.callback_url') ?: url('/api/payments/xendit/webhook'),
                'currency' => 'IDR',
                'expires_at' => now()->addMinutes((int) config('services.xendit.qris_expires_minutes', 30))->toISOString(),
            ]);

        $json = $response->json() ?: ['body' => $response->body()];

        if (!$response->successful() || !$this->qrisString($json)) {
            throw ValidationException::withMessages([
                'xendit' => [data_get($json, 'message') ?: 'gagal membuat QRIS Xendit'],
            ]);
        }

        return [
            'payment_gateway' => 'xendit',
            'gateway_order_id' => data_get($json, 'external_id') ?: $order->order_number,
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

    private function qrisString(array $response): ?string
    {
        return data_get($response, 'qr_string')
            ?: data_get($response, 'qr_code.qr_string')
            ?: data_get($response, 'data.qr_string');
    }
}
