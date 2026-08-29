<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PosCategoryModifier;
use App\Models\PosModifier;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeder menu papan CM STASIUN (foto "menu cm.jpeg").
 *
 * Terpisah dari CalonMantuMenuSeeder (store CM CEPU) karena harga dan
 * susunan kategorinya berbeda. SKU memakai prefix CMST- agar tidak bentrok
 * dengan prefix CM- milik CM CEPU (kolom products.sku unik global).
 */
class CmStasiunMenuSeeder extends Seeder
{
    private const SKU_PREFIX = 'CMST-';

    /**
     * Kategori minuman yang boleh di-upsize / ganti susu Oatside
     * ("Tambahan: Upsize +4K  Oatside +4K" di papan menu).
     */
    private const DRINK_CATEGORIES = [
        'Signature Series',
        'Premium Series',
        'Non Coffee Series',
        'Special Mantoe',
    ];

    public function run(): void
    {
        $store = Store::query()->updateOrCreate(
            ['code' => 'CM-STASIUN'],
            [
                'store_name' => 'CM STASIUN',
                'is_active' => true,
            ]
        );

        $categories = [];

        foreach ($this->menus() as $categoryName => $menus) {
            $category = Category::query()->updateOrCreate(
                [
                    'store_id' => $store->id,
                    'category_name' => $categoryName,
                ],
                ['description' => null]
            );

            $categories[$categoryName] = $category;

            foreach ($menus as $menu) {
                Product::query()->updateOrCreate(
                    ['sku' => $this->sku($menu['name'])],
                    [
                        'store_id' => $store->id,
                        'category_id' => $category->id,
                        'product_type' => 'menu',
                        'product_name' => $menu['name'],
                        'description' => $menu['description'] ?? null,
                        'unit_of_measure' => 'pcs',
                        'minimum_stock' => 0,
                        'current_stock' => 1000000,
                        'cost_price' => 0,
                        'selling_price' => $menu['price'],
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->seedModifiers($store, $categories);
    }

    /**
     * @param  array<string, Category>  $categories
     */
    private function seedModifiers(Store $store, array $categories): void
    {
        // "Tambahan: Extra Shot +5K" tertulis di bawah Premium Series.
        $this->attachModifier($store, 'Extra Shot', 5000, array_filter([
            $categories['Premium Series'] ?? null,
        ]));

        $drinkCategories = array_filter(array_map(
            fn (string $name): ?Category => $categories[$name] ?? null,
            self::DRINK_CATEGORIES
        ));

        $this->attachModifier($store, 'Upsize', 4000, $drinkCategories);
        $this->attachModifier($store, 'Oatside', 4000, $drinkCategories);
    }

    /**
     * @param  array<int, Category>  $categories
     */
    private function attachModifier(Store $store, string $name, int $priceDelta, array $categories): void
    {
        $modifier = PosModifier::query()->updateOrCreate(
            [
                'store_id' => $store->id,
                'name' => $name,
            ],
            [
                'price_delta' => $priceDelta,
                'is_active' => true,
            ]
        );

        foreach ($categories as $category) {
            PosCategoryModifier::query()->updateOrCreate(
                [
                    'category_id' => $category->id,
                    'modifier_id' => $modifier->id,
                ],
                [
                    'store_id' => $store->id,
                    'is_active' => true,
                ]
            );
        }
    }

    private function sku(string $name): string
    {
        return self::SKU_PREFIX . Str::upper(Str::slug($name));
    }

    /**
     * @return array<string, array<int, array{name: string, price: int, description?: string}>>
     */
    private function menus(): array
    {
        return [
            'Signature Series' => [
                ['name' => 'Kothok Mantoe', 'price' => 8000],
                ['name' => 'Kothok Klasik', 'price' => 11000],
            ],
            'Premium Series' => [
                ['name' => 'Americano', 'price' => 16000],
                ['name' => 'Cafe Latte', 'price' => 18000],
                ['name' => 'Aren Latte', 'price' => 19000],
                ['name' => 'Mantoe Idaman', 'price' => 20000],
                ['name' => 'Mantoe Romansa', 'price' => 20000],
                ['name' => 'Vanilla Latte', 'price' => 20000],
                ['name' => 'Mochaccino', 'price' => 22000],
                ['name' => 'Butterscotch Sea Salt Latte', 'price' => 22000],
            ],
            'Non Coffee Series' => [
                ['name' => 'Jasmine Tea', 'price' => 6000],
                ['name' => 'Lecy Tea', 'price' => 10000],
                ['name' => 'Lemon Tea', 'price' => 10000],
                // Item ke-4 (10K) tertutup di foto papan menu, belum diseed.
                ['name' => 'Chocolate', 'price' => 18000],
                ['name' => 'Choco Hazelnut', 'price' => 19000],
                ['name' => 'Red Velvet', 'price' => 18000],
                ['name' => 'Matcha', 'price' => 18000],
            ],
            'Special Mantoe' => [
                ['name' => 'Milky Berry', 'price' => 18000],
                ['name' => 'Cocoa Berry', 'price' => 19000],
                ['name' => 'Matcha Berry', 'price' => 19000],
                ['name' => 'Cookies N Cream', 'price' => 18000],
            ],
            'Toast Mantoe' => [
                ['name' => 'Ori Manis', 'price' => 8000],
                ['name' => 'Toast Coklat', 'price' => 10000],
                ['name' => 'Toast Keju', 'price' => 10000],
                ['name' => 'Toast Coklat Keju', 'price' => 12000],
                ['name' => 'Sandwich Mantoe', 'price' => 15000],
                ['name' => 'Ice Cream Toast', 'price' => 15000],
            ],
            'Other Mantoe' => [
                ['name' => 'Mix Platter', 'price' => 15000],
            ],
        ];
    }
}
