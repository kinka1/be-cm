<?php

namespace App\Services\Pos;

use App\Mail\OrderReceiptMail;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ReceiptEmailService
{
    public function send(Order $order): void
    {
        if (!$order->customer_email || $order->payment_status !== 'paid') {
            return;
        }

        DB::afterCommit(function () use ($order): void {
            try {
                Mail::to($order->customer_email)->send(new OrderReceiptMail($order));
            } catch (Throwable $exception) {
                Log::warning('Failed to send order receipt email', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_email' => $order->customer_email,
                    'error' => $exception->getMessage(),
                ]);
            }
        });
    }
}
