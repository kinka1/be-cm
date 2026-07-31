<?php

namespace App\Services\Pos;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\CalonMantu;
use App\Models\CashierSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateCashierOrderService
{
    public function __construct(
        private readonly OrderTotalService $orderTotalService,
        private readonly StockAvailabilityService $stockAvailabilityService,
        private readonly StockDeductionService $stockDeductionService,
        private readonly MidtransPaymentService $midtransPaymentService,
        private readonly BniQrisPaymentService $bniQrisPaymentService,
    ) {
    }

    public function create(array $data): Order
    {
        if (!empty($data['table_id'])) {
            $tableStoreId = CalonMantu::query()->whereKey($data['table_id'])->value('store_id');

            if ((int) $tableStoreId !== (int) $data['store_id']) {
                throw ValidationException::withMessages(['table_id' => ['Meja tidak tersedia pada toko yang dipilih']]);
            }
        }

        $totals = $this->orderTotalService->calculate($data['items'], (float) ($data['discount'] ?? 0), 0, (int) $data['store_id']);

        [$stockOk, $stockMessage] = $this->stockAvailabilityService->validate($totals['details']);

        if (!$stockOk) {
            throw ValidationException::withMessages(['stock' => [$stockMessage]]);
        }

        if (($data['payment_method'] ?? null) === 'cash' && (float) ($data['amount_paid'] ?? 0) < $totals['total_amount']) {
            throw ValidationException::withMessages(['amount_paid' => ['Jumlah pembayaran kurang dari total order']]);
        }

        $cashierSession = CashierSession::query()
            ->where('store_id', $data['store_id'])
            ->where('employee_id', $data['employee_id'])
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        if (!$cashierSession) {
            throw ValidationException::withMessages(['cashier_session' => ['Operator belum membuka kasir untuk toko ini']]);
        }

        return DB::transaction(function () use ($data, $totals, $cashierSession) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'store_id' => $data['store_id'],
                'table_id' => $data['table_id'] ?? null,
                'order_type' => $data['order_type'],
                'customer_name' => $data['customer_name'] ?? null,
                'employee_id' => $data['employee_id'],
                'cashier_session_id' => $cashierSession->id,
                'order_date' => now(),
                'subtotal' => $totals['subtotal'],
                'tax' => 0,
                'discount' => $totals['discount'],
                'payment_fee' => $totals['payment_fee'],
                'total_amount' => $totals['total_amount'],
                'payment_method' => $data['payment_method'],
                'payment_status' => $data['payment_method'] === 'cash' ? 'paid' : 'pending',
                'order_status' => $data['payment_method'] === 'cash' ? 'preparing' : 'pending',
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

            if ($data['payment_method'] === 'cash') {
                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => 'cash',
                    'amount_paid' => $data['amount_paid'],
                    'change_amount' => (float) $data['amount_paid'] - (float) $order->total_amount,
                    'payment_date' => now(),
                    'payment_status' => 'success',
                    'created_at' => now(),
                ]);

                $this->stockDeductionService->deduct($order);
            } else {
                $gateway = $this->createQrisTransaction($order);

                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => 'qris',
                    'payment_gateway' => $gateway['payment_gateway'] ?? config('services.qris_gateway', 'midtrans'),
                    'amount_paid' => $order->total_amount,
                    'change_amount' => 0,
                    'qris_transaction_id' => $gateway['qris_transaction_id'] ?? $gateway['gateway_transaction_id'] ?? null,
                    'gateway_order_id' => $gateway['gateway_order_id'] ?? $order->order_number,
                    'gateway_transaction_id' => $gateway['gateway_transaction_id'] ?? null,
                    'gateway_response' => $gateway['gateway_response'] ?? null,
                    'payment_fee' => $order->payment_fee,
                    'payment_status' => 'pending',
                    'created_at' => now(),
                ]);
            }

            return $order->load(['details.product', 'payment']);
        });
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-'.now()->format('YmdHis').'-'.random_int(1000, 9999);
    }

    private function createQrisTransaction(Order $order): array
    {
        if (config('services.qris_gateway') === 'bni') {
            return $this->bniQrisPaymentService->createQrisTransaction($order);
        }

        return array_merge(
            ['payment_gateway' => 'midtrans'],
            $this->midtransPaymentService->createQrisTransaction($order)
        );
    }
}
