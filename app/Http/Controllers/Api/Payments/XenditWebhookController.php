<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Pos\ReceiptEmailService;
use App\Services\Pos\StockDeductionService;
use App\Services\Pos\XenditPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class XenditWebhookController extends Controller
{
    public function __invoke(Request $request, XenditPaymentService $xendit, StockDeductionService $stockDeduction, ReceiptEmailService $receiptEmail): JsonResponse
    {
        $payload = $request->all();

        if (!$xendit->isValidWebhookToken($request->header('x-callback-token'))) {
            return response()->json(['status' => 'gagal', 'message' => 'invalid callback token', 'data' => null], 401);
        }

        $orderId = data_get($payload, 'reference_id')
            ?: data_get($payload, 'external_id')
            ?: data_get($payload, 'qr_code.reference_id')
            ?: data_get($payload, 'data.reference_id')
            ?: data_get($payload, 'data.external_id');
        $transactionId = data_get($payload, 'id')
            ?: data_get($payload, 'qr_code.id')
            ?: data_get($payload, 'data.id')
            ?: data_get($payload, 'payment_id');
        $status = strtolower((string) (data_get($payload, 'status') ?: data_get($payload, 'data.status') ?: data_get($payload, 'event')));

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

        DB::transaction(function () use ($order, $payload, $transactionId, $status, $stockDeduction, $receiptEmail): void {
            $payment = $order->payment()->lockForUpdate()->firstOrFail();
            $payment->fill([
                'payment_gateway' => 'xendit',
                'gateway_transaction_id' => $transactionId ?: $payment->gateway_transaction_id,
                'qris_transaction_id' => $transactionId ?: $payment->qris_transaction_id,
                'gateway_response' => $payload,
            ]);

            if (in_array($status, ['succeeded', 'success', 'completed', 'paid', 'qr_payment.succeeded'], true)) {
                if ($payment->payment_status !== 'success') {
                    $payment->payment_status = 'success';
                    $payment->payment_date = now();
                    $payment->save();

                    $order->update(['payment_status' => 'paid', 'order_status' => 'preparing']);
                    $stockDeduction->deduct($order);
                    $receiptEmail->send($order->fresh(['store', 'details.product', 'payment']));
                    return;
                }
            }

            if (in_array($status, ['failed', 'expired', 'expire', 'cancelled', 'canceled'], true)) {
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
