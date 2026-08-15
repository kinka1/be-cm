<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Pos\StockDeductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderPaymentStatusController extends Controller
{
    public function update(Request $request, Order $order, StockDeductionService $stockDeduction): JsonResponse
    {
        $data = $request->validate([
            'payment_status' => ['required', 'in:pending,paid,cancelled'],
        ]);

        if ($order->order_status === 'completed') {
            return response()->json([
                'status' => 'gagal',
                'message' => 'order sudah completed',
                'data' => null,
            ], 422);
        }

        if ($order->payment_status === 'paid' && $data['payment_status'] !== 'paid') {
            return response()->json([
                'status' => 'gagal',
                'message' => 'payment sudah paid, tidak bisa diubah tanpa proses refund/void',
                'data' => null,
            ], 422);
        }

        $order = DB::transaction(function () use ($order, $data, $stockDeduction): Order {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            $wasPaid = $order->payment_status === 'paid';
            $payment = $order->payment()->lockForUpdate()->first();

            $orderUpdates = ['payment_status' => $data['payment_status']];
            $paymentUpdates = [];

            if ($data['payment_status'] === 'paid') {
                $orderUpdates['order_status'] = $order->order_status === 'pending' ? 'preparing' : $order->order_status;
                $paymentUpdates = [
                    'payment_status' => 'success',
                    'payment_date' => $payment?->payment_date ?: now(),
                    'amount_paid' => $payment?->amount_paid ?: $order->total_amount,
                    'change_amount' => $payment?->change_amount ?: 0,
                ];
            }

            if ($data['payment_status'] === 'cancelled') {
                $orderUpdates['order_status'] = 'cancelled';
                $paymentUpdates = ['payment_status' => 'failed'];
            }

            if ($data['payment_status'] === 'pending') {
                $paymentUpdates = ['payment_status' => 'pending'];
            }

            $order->update($orderUpdates);

            if ($payment) {
                $payment->fill($paymentUpdates)->save();
            }

            if ($data['payment_status'] === 'paid' && !$wasPaid) {
                $stockDeduction->deduct($order);
            }

            return $order->fresh(['store', 'details.product', 'payment']);
        });

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $order,
        ]);
    }
}
