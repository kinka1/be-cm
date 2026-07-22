<?php

namespace App\Services\Pos;

use App\Models\Order;
use App\Models\Recipe;
use App\Models\StockTransaction;

class StockDeductionService
{
    public function deduct(Order $order): void
    {
        $order->loadMissing('details.product');

        foreach ($order->details as $detail) {
            $recipes = Recipe::query()->where('product_id', $detail->product_id)->get();

            if ($recipes->isEmpty()) {
                $this->createTransaction($order, $detail->product_id, (float) $detail->quantity);
                continue;
            }

            foreach ($recipes as $recipe) {
                $this->createTransaction($order, $recipe->ingredient_id, (float) $recipe->quantity_needed * (float) $detail->quantity);
            }
        }
    }

    private function createTransaction(Order $order, int $productId, float $quantity): void
    {
        StockTransaction::create([
            'store_id' => $order->store_id,
            'product_id' => $productId,
            'transaction_type' => 'out',
            'quantity' => $quantity,
            'reference_type' => 'sale',
            'reference_id' => $order->id,
            'employee_id' => $order->employee_id,
            'notes' => "Auto deduct from paid order {$order->order_number}",
            'transaction_date' => now(),
            'created_at' => now(),
        ]);
    }
}
