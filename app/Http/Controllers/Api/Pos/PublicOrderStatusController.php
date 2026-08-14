<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class PublicOrderStatusController extends Controller
{
    public function show(string $orderNumber): JsonResponse
    {
        $order = Order::query()
            ->with('payment')
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'order tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $payment = $order->payment;

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'order_type' => $order->order_type,
                'table_id' => $order->table_id,
                'table_label' => $order->table_label,
                'total_amount' => $order->total_amount,
                'payment_method' => $order->payment_method,
                'payment_gateway' => $payment?->payment_gateway,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
                'payment_status_label' => $this->paymentStatusLabel((string) $order->payment_status),
                'order_status_label' => $this->orderStatusLabel((string) $order->order_status),
                'qr_string' => $order->payment_status === 'pending' ? $this->qrString($payment?->gateway_response ?? []) : null,
                'paid_at' => $payment?->payment_date,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ],
        ]);
    }

    private function qrString(array $gatewayResponse): ?string
    {
        return data_get($gatewayResponse, 'qr_string')
            ?: data_get($gatewayResponse, 'qr_code.qr_string')
            ?: data_get($gatewayResponse, 'data.qr_string');
    }

    private function paymentStatusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'Pembayaran Berhasil',
            'cancelled' => 'Pembayaran Gagal / Kadaluarsa',
            default => 'Menunggu Pembayaran',
        };
    }

    private function orderStatusLabel(string $status): string
    {
        return match ($status) {
            'preparing' => 'Pesanan Diproses',
            'ready' => 'Pesanan Siap',
            'completed' => 'Pesanan Selesai',
            'cancelled' => 'Pesanan Dibatalkan',
            default => 'Menunggu Pembayaran',
        };
    }
}
