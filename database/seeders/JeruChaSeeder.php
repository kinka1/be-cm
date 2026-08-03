<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class JeruChaSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::query()->updateOrCreate(
            ['code' => 'JeruCha'],
            [
                'store_name' => 'JeruCha',
                'address' => 'Jalan Tuk Buntung, Sidomulyo, Cepu, Kabupaten Blora, Jawa Tengah (depan bekas SMK PGRI Cepu).',
                'phone' => '0812345678',
                'is_active' => true,
            ]
        );

        $roles = collect(['operator', 'supervisor', 'admin'])
            ->mapWithKeys(function (string $roleName): array {
                $role = Role::query()->updateOrCreate(
                    ['role_name' => $roleName],
                    ['permissions' => []]
                );

                return [$roleName => $role];
            });

        $this->employeeWithUser([
            'full_name' => 'Admin JeruCha',
            'store_id' => $store->id,
            'email' => 'admin.jerucha@calonmantu.test',
            'username' => 'admin_jerucha',
            'password' => '12345678',
            'role_id' => $roles->get('admin')->id,
            'join_date' => '2026-03-01',
        ]);

        $this->employeeWithUser([
            'full_name' => 'Operator JeruCha',
            'store_id' => $store->id,
            'email' => 'operator.jerucha@calonmantu.test',
            'username' => 'jerucha',
            'password' => '12345678',
            'role_id' => $roles->get('operator')->id,
            'join_date' => '2026-03-01',
        ]);

        $categories = collect($this->menus())
            ->keys()
            ->mapWithKeys(function (string $categoryName) use ($store): array {
                $category = Category::query()->updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'category_name' => $categoryName,
                    ],
                    ['description' => null]
                );

                return [$categoryName => $category];
            });

        foreach ($this->menus() as $categoryName => $products) {
            foreach ($products as $product) {
                Product::query()->updateOrCreate(
                    ['sku' => $product['sku']],
                    [
                        'store_id' => $store->id,
                        'category_id' => $categories[$categoryName]->id,
                        'product_name' => $product['name'],
                        'description' => null,
                        'unit_of_measure' => 'pcs',
                        'minimum_stock' => 0,
                        'current_stock' => 0,
                        'cost_price' => 0,
                        'selling_price' => $product['price'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function employeeWithUser(array $data): Employee
    {
        $employee = Employee::query()->updateOrCreate(
            ['email' => $data['email']],
            [
                'full_name' => $data['full_name'],
                'store_id' => $data['store_id'],
                'join_date' => $data['join_date'],
                'role_id' => $data['role_id'],
                'ktp_url' => '/storage/dummy/ktp-' . $data['username'] . '.jpg',
                'kk_url' => '/storage/dummy/kk-' . $data['username'] . '.jpg',
                'status' => 'active',
            ]
        );

        User::query()->updateOrCreate(
            ['username' => $data['username']],
            [
                'employee_id' => $employee->id,
                'current_store_id' => $data['store_id'],
                'name' => $data['full_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]
        );

        $employee->stores()->syncWithoutDetaching([$data['store_id']]);

        return $employee;
    }

    private function menus(): array
    {
        return [
            'Matcha Series' => [
                ['name' => 'Matcha OG', 'sku' => 'JERUCHA-MATCHA-OG', 'price' => 15000],
                ['name' => 'Matcha Berry', 'sku' => 'JERUCHA-MATCHA-BERRY', 'price' => 16000],
                ['name' => 'Honey Matcha', 'sku' => 'JERUCHA-HONEY-MATCHA', 'price' => 17000],
                ['name' => 'Choco Matcha', 'sku' => 'JERUCHA-CHOCO-MATCHA', 'price' => 18000],
                ['name' => 'Matcha Cookies', 'sku' => 'JERUCHA-MATCHA-COOKIES', 'price' => 18000],
            ],
            'Jeruk Series' => [
                ['name' => 'Jeruk Ori', 'sku' => 'JERUCHA-JERUK-ORI', 'price' => 8000],
                ['name' => 'Jeruk Madu', 'sku' => 'JERUCHA-JERUK-MADU', 'price' => 10000],
                ['name' => 'Jeruk Yakult', 'sku' => 'JERUCHA-JERUK-YAKULT', 'price' => 11000],
            ],
            'Coffee Series' => [
                ['name' => 'Americano', 'sku' => 'JERUCHA-AMERICANO', 'price' => 15000],
                ['name' => 'Cafe Latte', 'sku' => 'JERUCHA-CAFE-LATTE', 'price' => 17000],
                ['name' => 'Aren Latte', 'sku' => 'JERUCHA-AREN-LATTE', 'price' => 17000],
                ['name' => 'Vanilla Latte', 'sku' => 'JERUCHA-VANILLA-LATTE', 'price' => 20000],
                ['name' => 'Butterscotch Latte', 'sku' => 'JERUCHA-BUTTERSCOTCH-LATTE', 'price' => 20000],
                ['name' => 'Mochaccino', 'sku' => 'JERUCHA-MOCHACCINO', 'price' => 21000],
            ],
            'Non Coffee Series' => [
                ['name' => 'Chocolate', 'sku' => 'JERUCHA-CHOCOLATE', 'price' => 15000],
                ['name' => 'Red Velvet', 'sku' => 'JERUCHA-RED-VELVET', 'price' => 15000],
                ['name' => 'Milky Berry', 'sku' => 'JERUCHA-MILKY-BERRY', 'price' => 15000],
                ['name' => 'Cookies N Cream', 'sku' => 'JERUCHA-COOKIES-N-CREAM', 'price' => 15000],
            ],
            'Toast JeruCha' => [
                ['name' => 'Ori Manis', 'sku' => 'JERUCHA-ORI-MANIS', 'price' => 7000],
                ['name' => 'Toast Coklat', 'sku' => 'JERUCHA-TOAST-COKLAT', 'price' => 9000],
                ['name' => 'Toast Keju', 'sku' => 'JERUCHA-TOAST-KEJU', 'price' => 9000],
                ['name' => 'Toast Coklat Keju', 'sku' => 'JERUCHA-TOAST-COKLAT-KEJU', 'price' => 11000],
            ],
            'Other JeruCha' => [
                ['name' => 'Mix Platter', 'sku' => 'JERUCHA-MIX-PLATTER', 'price' => 15000],
            ],
            'Tambahan' => [
                ['name' => 'Extra Shot', 'sku' => 'JERUCHA-EXTRA-SHOT', 'price' => 5000],
            ],
        ];
    }
}
