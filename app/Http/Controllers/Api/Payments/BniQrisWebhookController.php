<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Pos\BniQrisPaymentService;
use App\Services\Pos\StockDeductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BniQrisWebhookController extends Controller
{
    public function __invoke(Request $request, BniQrisPaymentService $bniQris, StockDeductionService $stockDeduction): JsonResponse
    {
        $payload = $request->all();
        $signature = $request->header('X-SIGNATURE') ?: $request->header('X-BNI-SIGNATURE');

        if (!$bniQris->isValidWebhookSignature($payload, $signature)) {
            return response()->json(['status' => 'gagal', 'message' => 'invalid signature', 'data' => null], 401);
        }

        $orderId = data_get($payload, 'order_id') ?: data_get($payload, 'data.order_id') ?: data_get($payload, 'merchant_order_id');
        $transactionId = data_get($payload, 'transaction_id') ?: data_get($payload, 'data.transaction_id') ?: data_get($payload, 'data.reference_no');
        $status = strtolower((string) (data_get($payload, 'status') ?: data_get($payload, 'transaction_status') ?: data_get($payload, 'data.status')));

        if (!$orderId && !$transactionId) {
            return response()->json(['status' => 'gagal', 'message' => 'missing order identifier', 'data' => null], 422);
        }

        $order = Order::query()
            ->where(function ($query) use ($orderId, $transactionId): void {
                if ($orderId) {
                    $query->where('order_number', $orderId)
                        ->orWhereHas('payment', fn ($paymentQuery) => $paymentQuery->where('gateway_order_id', $orderId));
                }

                if ($transactionId) {
                    $query->orWhereHas('payment', fn ($paymentQuery) => $paymentQuery->where('gateway_transaction_id', $transactionId));
                }
            })
            ->firstOrFail();

        DB::transaction(function () use ($order, $payload, $transactionId, $status, $stockDeduction): void {
            $payment = $order->payment()->lockForUpdate()->firstOrFail();
            $payment->fill([
                'gateway_transaction_id' => $transactionId ?: $payment->gateway_transaction_id,
                'qris_transaction_id' => $transactionId ?: $payment->qris_transaction_id,
                'gateway_response' => $payload,
            ]);

            if (in_array($status, ['paid', 'success', 'settlement', 'settled', 'completed'], true)) {
                if ($payment->payment_status !== 'success') {
                    $payment->payment_status = 'success';
                    $payment->payment_date = now();
                    $payment->save();

                    $order->update(['payment_status' => 'paid', 'order_status' => 'preparing']);
                    $stockDeduction->deduct($order);
                    return;
                }
            }

            if (in_array($status, ['failed', 'expire', 'expired', 'cancel', 'cancelled', 'deny', 'denied'], true)) {
                $payment->payment_status = 'failed';
                $payment->save();
                $order->update(['payment_status' => 'cancelled', 'order_status' => 'cancelled']);
                return;
            }

            $payment->save();
        });

        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => null]);
    }
}
