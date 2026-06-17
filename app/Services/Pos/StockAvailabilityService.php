<?php

namespace App\Services\Pos;

use App\Models\Recipe;
use App\Models\Product;
use Illuminate\Support\Collection;

class StockAvailabilityService
{
    public function validate(Collection $details): array
    {
        $required = [];

        foreach ($details as $detail) {
            $product = $detail['product'];

            if (!$product->is_active) {
                return [false, "Produk {$product->product_name} tidak aktif"];
            }

            $recipes = Recipe::query()->where('product_id', $product->id)->get();

            if ($recipes->isEmpty()) {
                $required[$product->id] = ($required[$product->id] ?? 0) + (float) $detail['quantity'];
                continue;
            }

            foreach ($recipes as $recipe) {
                $required[$recipe->ingredient_id] = ($required[$recipe->ingredient_id] ?? 0) + ((float) $recipe->quantity_needed * (float) $detail['quantity']);
            }
        }

        $products = Product::query()->whereIn('id', array_keys($required))->get()->keyBy('id');

        foreach ($required as $productId => $quantity) {
            $product = $products->get($productId);

            if (!$product || (float) $product->current_stock < $quantity) {
                return [false, "Stok {$product?->product_name} tidak cukup"];
            }
        }

        return [true, null];
    }
}
