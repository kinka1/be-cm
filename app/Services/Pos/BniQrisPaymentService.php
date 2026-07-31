<?php

namespace App\Services\Pos;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BniQrisPaymentService
{
    public function createQrisTransaction(Order $order): array
    {
        return $this->createRawQris([
            'order_id' => $order->order_number,
            'amount' => (int) round($order->total_amount),
            'customer_name' => $order->customer_name ?: 'Customer',
        ]);
    }

    public function createRawQris(array $data): array
    {
        $baseUrl = rtrim((string) config('services.bni_qris.base_url'), '/');
        $path = (string) config('services.bni_qris.create_qris_path');

        if (!$baseUrl || !$path) {
            return $this->notConfiguredResponse($data['order_id'] ?? null, 'BNI_QRIS_BASE_URL or BNI_QRIS_CREATE_PATH is not configured');
        }

        $payload = $this->createPayload($data);
        $response = $this->request('post', $baseUrl.'/'.ltrim($path, '/'), $payload);
        $json = $response->json() ?: ['body' => $response->body()];

        return $this->normalizeCreateResponse($data['order_id'] ?? null, $json, $response->status());
    }

    public function inquiry(string $gatewayOrderId): array
    {
        $baseUrl = rtrim((string) config('services.bni_qris.base_url'), '/');
        $path = (string) config('services.bni_qris.inquiry_path');

        if (!$baseUrl || !$path) {
            return ['status_code' => null, 'gateway_response' => ['message' => 'BNI_QRIS_BASE_URL or BNI_QRIS_INQUIRY_PATH is not configured']];
        }

        $payload = ['order_id' => $gatewayOrderId, 'merchant_id' => config('services.bni_qris.merchant_id')];
        $response = $this->request('post', $baseUrl.'/'.ltrim($path, '/'), $payload);

        return [
            'status_code' => $response->status(),
            'gateway_response' => $response->json() ?: ['body' => $response->body()],
        ];
    }

    public function isValidWebhookSignature(array $payload, ?string $signature): bool
    {
        $mode = config('services.bni_qris.signature_mode', 'none');

        if ($mode === 'none') {
            return true;
        }

        if (!$signature) {
            return false;
        }

        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($mode === 'hmac_sha256') {
            $secret = (string) config('services.bni_qris.webhook_secret');
            return $secret !== '' && hash_equals(hash_hmac('sha256', $body, $secret), $signature);
        }

        if ($mode === 'rsa_sha256') {
            $publicKey = (string) config('services.bni_qris.public_key');
            return $publicKey !== '' && openssl_verify($body, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256) === 1;
        }

        return false;
    }

    private function request(string $method, string $url, array $payload)
    {
        $headers = $this->headers($payload);
        $token = $this->accessToken();

        $request = Http::timeout((int) config('services.bni_qris.timeout', 30))->acceptJson()->withHeaders($headers);

        if ($token) {
            $request = $request->withToken($token);
        }

        return $request->{$method}($url, $payload);
    }

    private function accessToken(): ?string
    {
        $baseUrl = rtrim((string) config('services.bni_qris.base_url'), '/');
        $path = (string) config('services.bni_qris.token_path');

        if (!$baseUrl || !$path) {
            return null;
        }

        $response = Http::timeout((int) config('services.bni_qris.timeout', 30))
            ->asForm()
            ->post($baseUrl.'/'.ltrim($path, '/'), [
                'grant_type' => 'client_credentials',
                'client_id' => config('services.bni_qris.client_id'),
                'client_secret' => config('services.bni_qris.client_secret'),
            ]);

        return $response->json('access_token');
    }

    private function headers(array $payload): array
    {
        $timestamp = now()->toIso8601String();
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return array_filter([
            'X-CLIENT-ID' => config('services.bni_qris.client_id'),
            'X-TIMESTAMP' => $timestamp,
            'X-EXTERNAL-ID' => Str::uuid()->toString(),
            'X-SIGNATURE' => $this->signature($body, $timestamp),
        ]);
    }

    private function signature(string $body, string $timestamp): ?string
    {
        $mode = config('services.bni_qris.signature_mode', 'none');
        $stringToSign = $timestamp.'.'.$body;

        if ($mode === 'hmac_sha256') {
            $secret = (string) config('services.bni_qris.client_secret');
            return $secret === '' ? null : hash_hmac('sha256', $stringToSign, $secret);
        }

        if ($mode === 'rsa_sha256') {
            $privateKey = (string) config('services.bni_qris.private_key');
            if ($privateKey === '') {
                return null;
            }

            openssl_sign($stringToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            return base64_encode($signature);
        }

        return null;
    }

    private function createPayload(array $data): array
    {
        return [
            'merchant_id' => config('services.bni_qris.merchant_id'),
            'terminal_id' => config('services.bni_qris.terminal_id'),
            'order_id' => $data['order_id'],
            'amount' => (int) round((float) $data['amount']),
            'currency' => $data['currency'] ?? 'IDR',
            'customer_name' => $data['customer_name'] ?? 'Customer',
            'description' => $data['description'] ?? 'Calon Mantu QRIS Payment',
            'callback_url' => $data['callback_url'] ?? url('/api/payments/bni-qris/webhook'),
        ];
    }

    private function normalizeCreateResponse(?string $orderId, array $json, int $statusCode): array
    {
        return [
            'payment_gateway' => 'bni_qris',
            'gateway_order_id' => data_get($json, 'order_id') ?: data_get($json, 'data.order_id') ?: $orderId,
            'gateway_transaction_id' => data_get($json, 'transaction_id') ?: data_get($json, 'data.transaction_id') ?: data_get($json, 'data.reference_no'),
            'qris_transaction_id' => data_get($json, 'transaction_id') ?: data_get($json, 'data.transaction_id') ?: data_get($json, 'data.reference_no'),
            'qr_string' => data_get($json, 'qr_string') ?: data_get($json, 'data.qr_string') ?: data_get($json, 'data.qrContent'),
            'qr_image_url' => data_get($json, 'qr_image_url') ?: data_get($json, 'data.qr_image_url') ?: data_get($json, 'data.qrUrl'),
            'expires_at' => data_get($json, 'expires_at') ?: data_get($json, 'data.expires_at') ?: data_get($json, 'data.expired_at'),
            'gateway_response' => array_merge(['http_status' => $statusCode], $json),
        ];
    }

    private function notConfiguredResponse(?string $orderId, string $message): array
    {
        return [
            'payment_gateway' => 'bni_qris',
            'gateway_order_id' => $orderId,
            'gateway_transaction_id' => null,
            'qris_transaction_id' => null,
            'gateway_response' => ['message' => $message],
        ];
    }
}
