<?php

namespace App\Services\Pos;

use App\Models\CalonMantu;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateQrOrderService
{
    public function __construct(
        private readonly OrderTotalService $orderTotalService,
        private readonly StockAvailabilityService $stockAvailabilityService,
        private readonly MidtransPaymentService $midtransPaymentService,
    ) {
    }

    public function create(array $data): Order
    {
        $table = CalonMantu::query()->where('qr_code', $data['qr_code'])->firstOrFail();
        $totals = $this->orderTotalService->calculate($data['items'], (float) ($data['discount'] ?? 0), 0, $table->store_id);

        [$stockOk, $stockMessage] = $this->stockAvailabilityService->validate($totals['details']);

        if (!$stockOk) {
            throw ValidationException::withMessages(['stock' => [$stockMessage]]);
        }

        return DB::transaction(function () use ($data, $table, $totals) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'store_id' => $table->store_id,
                'table_id' => $table->id,
                'order_type' => 'dine_in_qr',
                'customer_name' => $data['customer_name'] ?? null,
                'order_date' => now(),
                'subtotal' => $totals['subtotal'],
                'tax' => 0,
                'discount' => $totals['discount'],
                'payment_fee' => $totals['payment_fee'],
                'total_amount' => $totals['total_amount'],
                'payment_method' => 'qris',
                'payment_status' => 'pending',
                'order_status' => 'pending',
            ]);

            foreach ($totals['details'] as $detail) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'subtotal' => $detail['subtotal'],
                    'notes' => $detail['notes'],
                    'created_at' => now(),
                ]);
            }

            $gateway = $this->midtransPaymentService->createQrisTransaction($order);

            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'qris',
                'payment_gateway' => 'midtrans',
                'amount_paid' => $order->total_amount,
                'change_amount' => 0,
                'qris_transaction_id' => $gateway['gateway_transaction_id'] ?? null,
                'gateway_order_id' => $gateway['gateway_order_id'] ?? $order->order_number,
                'gateway_transaction_id' => $gateway['gateway_transaction_id'] ?? null,
                'gateway_response' => $gateway['gateway_response'] ?? null,
                'payment_fee' => $order->payment_fee,
                'payment_status' => 'pending',
                'created_at' => now(),
            ]);

            return $order->load(['details.product', 'payment']);
        });
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-'.now()->format('YmdHis').'-'.random_int(1000, 9999);
    }
}
