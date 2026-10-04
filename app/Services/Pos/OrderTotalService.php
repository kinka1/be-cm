<?php

namespace App\Services\Pos;

use App\Models\Product;
use Illuminate\Validation\ValidationException;

class OrderTotalService
{
    public function calculate(array $items, float $discount = 0, float $paymentFee = 0, ?int $storeId = null): array
    {
        $productIds = collect($items)
            ->filter(fn (array $item): bool => ($item['type'] ?? 'menu') === 'menu')
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values();
        $productQuery = Product::query()->whereIn('id', $productIds);

        if ($storeId !== null) {
            $productQuery->where('store_id', $storeId);
        }

        $products = $productQuery->get()->keyBy('id');

        if ($productIds->isNotEmpty() && $products->count() !== $productIds->count()) {
            throw ValidationException::withMessages(['items' => ['Produk tidak ditemukan pada toko yang dipilih']]);
        }

        $details = collect($items)->map(function (array $item) use ($products) {
            $type = $item['type'] ?? 'menu';

            if ($type === 'custom') {
                if (blank($item['custom_name'] ?? null) || !array_key_exists('unit_price', $item)) {
                    throw ValidationException::withMessages(['items' => ['Nama dan harga custom item wajib diisi.']]);
                }

                if (!empty($item['modifiers'])) {
                    throw ValidationException::withMessages(['items' => ['Custom item tidak mendukung modifier.']]);
                }

                $quantity = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];

                return [
                    'type' => 'custom',
                    'product' => null,
                    'product_id' => null,
                    'item_name' => trim((string) $item['custom_name']),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'modifiers' => collect(),
                    'subtotal' => $quantity * $unitPrice,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            if (empty($item['product_id']) || !$products->has($item['product_id'])) {
                throw ValidationException::withMessages(['items' => ['Produk tidak ditemukan pada toko yang dipilih']]);
            }

            $product = $products->get($item['product_id']);
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $product->selling_price;
            $modifiers = collect($item['modifiers'] ?? [])->map(function (array $modifier) use ($quantity): array {
                $modifierQuantity = (float) ($modifier['quantity'] ?? 1);
                $priceDelta = (float) ($modifier['price_delta'] ?? 0);

                return [
                    'modifier_id' => $modifier['modifier_id'] ?? null,
                    'name' => $modifier['name'] ?? null,
                    'price_delta' => $priceDelta,
                    'quantity' => $modifierQuantity,
                    'subtotal' => $priceDelta * $modifierQuantity * $quantity,
                ];
            })->values();
            $baseSubtotal = $quantity * $unitPrice;

            return [
                'type' => 'menu',
                'product' => $product,
                'product_id' => $product->id,
                'item_name' => $product->product_name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'modifiers' => $modifiers,
                'subtotal' => $baseSubtotal + $modifiers->sum('subtotal'),
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
