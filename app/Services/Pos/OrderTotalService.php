<?php

namespace App\Services\Pos;

use App\Models\Product;
use Illuminate\Validation\ValidationException;

class OrderTotalService
{
    public function calculate(array $items, float $discount = 0, float $paymentFee = 0, ?int $storeId = null): array
    {
        $productIds = collect($items)->pluck('product_id')->unique()->values();
        $productQuery = Product::query()->whereIn('id', $productIds);

        if ($storeId !== null) {
            $productQuery->where('store_id', $storeId);
        }

        $products = $productQuery->get()->keyBy('id');

        if ($products->count() !== $productIds->count()) {
            throw ValidationException::withMessages(['items' => ['Produk tidak ditemukan pada toko yang dipilih']]);
        }

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
