<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Pos\MidtransPaymentService;
use App\Services\Pos\StockDeductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, MidtransPaymentService $midtrans, StockDeductionService $stockDeduction): JsonResponse
    {
        $payload = $request->all();

        if (!$midtrans->isValidSignature($payload)) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'invalid signature',
                'data' => null,
            ], 401);
        }

        $order = Order::query()->where('order_number', $payload['order_id'] ?? null)->firstOrFail();
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        DB::transaction(function () use ($order, $payload, $transactionStatus, $fraudStatus, $stockDeduction) {
            $payment = $order->payment()->lockForUpdate()->firstOrFail();

            $payment->fill([
                'gateway_transaction_id' => $payload['transaction_id'] ?? $payment->gateway_transaction_id,
                'qris_transaction_id' => $payload['transaction_id'] ?? $payment->qris_transaction_id,
                'gateway_response' => $payload,
            ]);

            $isSuccess = in_array($transactionStatus, ['settlement', 'capture'], true)
                && ($transactionStatus !== 'capture' || $fraudStatus === 'accept');

            if ($isSuccess && $payment->payment_status !== 'success') {
                $payment->payment_status = 'success';
                $payment->payment_date = now();
                $payment->save();

                $order->update([
                    'payment_status' => 'paid',
                    'order_status' => 'preparing',
                ]);

                $stockDeduction->deduct($order);

                return;
            }

            if (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
                $payment->payment_status = 'failed';
                $payment->save();

                $order->update([
                    'payment_status' => 'cancelled',
                    'order_status' => 'cancelled',
                ]);

                return;
            }

            $payment->save();
        });

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => null,
        ]);
    }
}
