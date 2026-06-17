<?php

namespace App\Services\Pos;

use App\Models\Product;

class OrderTotalService
{
    public function calculate(array $items, float $discount = 0, float $paymentFee = 0): array
    {
        $productIds = collect($items)->pluck('product_id')->unique()->values();
        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');

        $details = collect($items)->map(function (array $item) use ($products) {
            $product = $products->get($item['product_id']);
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $product->selling_price;

            return [
                'product' => $product,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $quantity * $unitPrice,
                'notes' => $item['notes'] ?? null,
            ];
        });

        $subtotal = (float) $details->sum('subtotal');
        $discount = min($discount, $subtotal);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'payment_fee' => $paymentFee,
            'total_amount' => $subtotal - $discount + $paymentFee,
            'details' => $details,
        ];
    }
}
