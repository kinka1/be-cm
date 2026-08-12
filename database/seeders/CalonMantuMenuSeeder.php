<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CalonMantuMenuSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::query()->findOrFail(3);

        foreach ($this->menus() as $categoryName => $menus) {
            $category = Category::query()->updateOrCreate(
                [
                    'store_id' => $store->id,
                    'category_name' => $categoryName,
                ],
                ['description' => null]
            );

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
                        'current_stock' => 0,
                        'cost_price' => 0,
                        'selling_price' => $menu['price'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function sku(string $name): string
    {
        return 'CM-' . Str::upper(Str::slug($name));
    }

    private function menus(): array
    {
        return [
            'Signature Series' => [
                ['name' => 'Kothok Mantoe', 'price' => 8000],
                ['name' => 'Kothok Klasik', 'price' => 11000],
            ],
            'Premium Series' => [
                ['name' => 'Espresso', 'price' => 12000],
                ['name' => 'Americano', 'description' => 'Espresso, Water', 'price' => 16000],
                ['name' => 'Cafe Latte', 'description' => 'Espresso with fresh milk', 'price' => 18000],
                ['name' => 'Aren Latte', 'description' => 'Espresso, Milk with Palm Sugar', 'price' => 19000],
                ['name' => 'Mochaccino', 'description' => 'Espresso, Milk with Chocolate', 'price' => 22000],
                ['name' => 'Mantoe Idaman', 'description' => 'Espresso, Milk with caramel syrup', 'price' => 21000],
                ['name' => 'Mantoe Romansa', 'description' => 'Espresso, Milk with Hazelnuts syrup', 'price' => 20000],
                ['name' => 'Mantoe Asa', 'description' => 'Espresso, Milk with Banana syrup', 'price' => 20000],
                ['name' => 'Vanilla Latte', 'description' => 'Espresso, Milk with Vanilla syrup', 'price' => 21000],
                ['name' => 'Salted Caramel Latte', 'description' => 'Espresso, Milk with Caramel syrup, himalayan salt', 'price' => 22000],
                ['name' => 'Butterscotch', 'description' => 'Espresso, Milk with Butterscotch syrup, himalayan salt', 'price' => 22000],
            ],
            'Special Mantoe' => [
                ['name' => 'Milky Berry', 'price' => 18000],
                ['name' => 'Cocoa Berry', 'price' => 19000],
                ['name' => 'Cookies N Cream', 'price' => 18000],
            ],
            'Milk Based' => [
                ['name' => 'Red Velvet', 'price' => 18000],
                ['name' => 'Chocolate', 'price' => 18000],
                ['name' => 'Choco Hazelnut', 'price' => 19000],
            ],
            'Tea' => [
                ['name' => 'Jasmine Tea', 'price' => 10000],
                ['name' => 'Lychee Tea', 'price' => 12000],
                ['name' => 'Lemon Tea', 'price' => 12000],
                ['name' => 'Peach Tea', 'price' => 12000],
            ],
            'Matcha Mantoe' => [
                ['name' => 'Matcha OG', 'price' => 18000],
                ['name' => 'Matcha Berry', 'price' => 19000],
                ['name' => 'Honey Matcha', 'price' => 20000],
                ['name' => 'Choco Matcha', 'price' => 20000],
                ['name' => 'Matcha Cookies', 'price' => 20000],
            ],
            'Refreshed' => [
                ['name' => 'Jeruk OG', 'price' => 10000],
                ['name' => 'Jeruk Madu', 'price' => 12000],
                ['name' => 'Jeruk Yakult', 'price' => 12000],
            ],
            'Other' => [
                ['name' => 'Mineral Water', 'price' => 10000],
            ],
            'NUSANTARA' => [
                ['name' => 'Nasi Ayam Laos', 'price' => 26000],
                ['name' => 'Soto Betawi', 'price' => 30000],
                ['name' => 'Sop Iga', 'price' => 44000],
                ['name' => 'Asem Iga', 'price' => 44000],
                ['name' => 'Nasi Goreng Oriental', 'price' => 20000],
                ['name' => 'Nasi Goreng Special', 'price' => 25000],
                ['name' => 'Mie Goreng Jawa', 'price' => 20000],
            ],
            'WESTERN' => [
                ['name' => 'Aglio Olio', 'price' => 25000],
                ['name' => 'Carbonara', 'price' => 26000],
                ['name' => 'Bolognese', 'price' => 26000],
                ['name' => 'Chicken Cordon Blue', 'price' => 26000],
            ],
            'KATSU SERIES' => [
                ['name' => 'Chicken Katsu', 'price' => 25000],
                ['name' => 'Chicken Sambal Matah', 'price' => 26000],
                ['name' => 'Chicken Asam Manis', 'price' => 26000],
            ],
            'Ricebowl' => [
                ['name' => 'Chicken Ricebowl Teriyaki', 'price' => 27000],
                ['name' => 'Chicken Ricebowl Barbeque', 'price' => 27000],
                ['name' => 'Beef Ricebowl Blackpaper', 'price' => 30000],
                ['name' => 'Beef Ricebowl Saus Tiram', 'price' => 30000],
            ],
            'SAVORY AND SWEET' => [
                ['name' => 'Mantoe Platter', 'price' => 18000],
                ['name' => 'Mantoe Soft Crepes', 'price' => 18000],
                ['name' => 'Fried Dimsum', 'price' => 15000],
                ['name' => 'Dimsum (3)', 'price' => 15000],
                ['name' => 'Dimsum Goreng Keju (3)', 'price' => 15000],
                ['name' => 'Risol Mayo', 'price' => 16000],
                ['name' => 'Risol Coklat', 'price' => 15000],
                ['name' => 'Risol Matcha', 'price' => 15000],
                ['name' => 'Risol Beef', 'price' => 16000],
                ['name' => 'Tahu Judas', 'price' => 16000],
                ['name' => 'Lumpia', 'price' => 16000],
            ],
            'Toastect' => [
                ['name' => 'Toast Ori', 'price' => 10000],
                ['name' => 'Toast Coklat', 'price' => 11000],
                ['name' => 'Toast Keju', 'price' => 11000],
                ['name' => 'Toast Coklat Keju', 'price' => 13000],
                ['name' => 'Sandwich Mantoe', 'price' => 18000],
                ['name' => 'Ice Cream Toast', 'price' => 16000],
                ['name' => 'Pancake', 'price' => 20000],
                ['name' => 'Pancake Coklat', 'price' => 24000],
            ],
        ];
    }
}
