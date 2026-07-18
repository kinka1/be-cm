<?php

namespace Database\Seeders;

use App\Models\CalonMantu;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\Role;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::query()->get()->keyBy('role_name');

        $admin = $this->employeeWithUser([
            'full_name' => 'Admin Calon Mantu',
            'email' => 'admin@calonmantu.test',
            'username' => 'admin',
            'role_id' => $roles->get('admin')->id,
            'join_date' => '2026-01-01',
        ]);

        $supervisor = $this->employeeWithUser([
            'full_name' => 'Sari Supervisor',
            'email' => 'supervisor@calonmantu.test',
            'username' => 'supervisor',
            'role_id' => $roles->get('supervisor')->id,
            'join_date' => '2026-02-01',
        ]);

        $operator = $this->employeeWithUser([
            'full_name' => 'Budi Operator',
            'email' => 'operator@calonmantu.test',
            'username' => 'operator',
            'role_id' => $roles->get('operator')->id,
            'join_date' => '2026-03-01',
        ]);

        $this->tables();

        $categories = $this->categories();
        $products = $this->products($categories);
        $this->recipes($products);
        $this->initialStock($products, $supervisor->id);
        $this->sampleOrder($products, $operator->id);
        $this->syncCurrentStock();
    }

    private function employeeWithUser(array $data): Employee
    {
        $employee = Employee::query()->updateOrCreate(
            ['email' => $data['email']],
            [
                'full_name' => $data['full_name'],
                'join_date' => $data['join_date'],
                'role_id' => $data['role_id'],
                'ktp_url' => '/storage/dummy/ktp-'.$data['username'].'.jpg',
                'kk_url' => '/storage/dummy/kk-'.$data['username'].'.jpg',
                'status' => 'active',
            ]
        );

        User::query()->updateOrCreate(
            ['username' => $data['username']],
            [
                'employee_id' => $employee->id,
                'name' => $data['full_name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
            ]
        );

        return $employee;
    }

    private function tables(): void
    {
        foreach ([1, 2, 3, 4, 5] as $number) {
            CalonMantu::query()->updateOrCreate(
                ['qr_code' => 'CM-TABLE-'.$number],
                [
                    'table_number' => (string) $number,
                    'capacity' => $number <= 2 ? 2 : 4,
                    'status' => 'available',
                    'created_at' => now(),
                ]
            );
        }
    }

    private function categories(): array
    {
        $data = [
            'coffee' => ['category_name' => 'Coffee', 'description' => 'Menu minuman berbasis kopi'],
            'non_coffee' => ['category_name' => 'Non Coffee', 'description' => 'Menu minuman tanpa kopi'],
            'food' => ['category_name' => 'Food', 'description' => 'Menu makanan ringan'],
            'ingredient' => ['category_name' => 'Ingredient', 'description' => 'Bahan baku produksi menu'],
        ];

        $categories = [];

        foreach ($data as $key => $item) {
            $categories[$key] = Category::query()->updateOrCreate(
                ['category_name' => $item['category_name']],
                ['description' => $item['description']]
            );
        }

        return $categories;
    }

    private function products(array $categories): array
    {
        $data = [
            'beans' => ['Arabica Beans', 'ING-BEANS', 'ingredient', 'gram', 500, 120, 0, false],
            'milk' => ['Fresh Milk', 'ING-MILK', 'ingredient', 'ml', 1000, 18, 0, false],
            'sugar' => ['Sugar', 'ING-SUGAR', 'ingredient', 'gram', 500, 8, 0, false],
            'tea' => ['Tea Leaves', 'ING-TEA', 'ingredient', 'gram', 200, 40, 0, false],
            'cup' => ['Plastic Cup 16oz', 'ING-CUP-16', 'ingredient', 'pcs', 50, 700, 0, false],
            'espresso' => ['Espresso', 'MENU-ESPRESSO', 'coffee', 'cup', 0, 7000, 18000, true],
            'latte' => ['Cafe Latte', 'MENU-LATTE', 'coffee', 'cup', 0, 10000, 25000, true],
            'iced_tea' => ['Iced Tea', 'MENU-ICED-TEA', 'non_coffee', 'cup', 0, 4000, 12000, true],
            'fries' => ['French Fries', 'FOOD-FRIES', 'food', 'portion', 5, 9000, 18000, true],
        ];

        $products = [];

        foreach ($data as $key => [$name, $sku, $category, $unit, $minimumStock, $costPrice, $sellingPrice, $isActive]) {
            $products[$key] = Product::query()->updateOrCreate(
                ['sku' => $sku],
                [
                    'product_name' => $name,
                    'category_id' => $categories[$category]->id,
                    'description' => 'Dummy data '.$name,
                    'unit_of_measure' => $unit,
                    'minimum_stock' => $minimumStock,
                    'cost_price' => $costPrice,
                    'selling_price' => $sellingPrice,
                    'is_active' => $isActive,
                ]
            );
        }

        return $products;
    }

    private function recipes(array $products): void
    {
        $recipes = [
            'espresso' => [['beans', 18, 'gram'], ['cup', 1, 'pcs']],
            'latte' => [['beans', 18, 'gram'], ['milk', 150, 'ml'], ['cup', 1, 'pcs']],
            'iced_tea' => [['tea', 5, 'gram'], ['sugar', 10, 'gram'], ['cup', 1, 'pcs']],
        ];

        foreach ($recipes as $menu => $ingredients) {
            foreach ($ingredients as [$ingredient, $quantity, $unit]) {
                Recipe::query()->updateOrCreate(
                    [
                        'product_id' => $products[$menu]->id,
                        'ingredient_id' => $products[$ingredient]->id,
                    ],
                    [
                        'quantity_needed' => $quantity,
                        'unit' => $unit,
                    ]
                );
            }
        }
    }

    private function initialStock(array $products, int $employeeId): void
    {
        $stocks = [
            'beans' => 5000,
            'milk' => 12000,
            'sugar' => 3000,
            'tea' => 1500,
            'cup' => 200,
            'fries' => 40,
        ];

        foreach ($stocks as $key => $quantity) {
            StockTransaction::query()->firstOrCreate(
                [
                    'product_id' => $products[$key]->id,
                    'reference_type' => 'purchase',
                    'reference_id' => 1000 + $products[$key]->id,
                ],
                [
                    'transaction_type' => 'in',
                    'quantity' => $quantity,
                    'employee_id' => $employeeId,
                    'notes' => 'Dummy initial stock',
                    'transaction_date' => now()->subDays(2),
                    'created_at' => now()->subDays(2),
                ]
            );
        }
    }

    private function sampleOrder(array $products, int $employeeId): void
    {
        $orderData = [
            'order_type' => 'takeaway',
            'customer_name' => 'Dummy Customer',
            'employee_id' => $employeeId,
            'order_date' => now()->subDay(),
            'subtotal' => 43000,
            'tax' => 0,
            'discount' => 0,
            'total_amount' => 43000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ];

        if (Schema::hasColumn('orders', 'payment_fee')) {
            $orderData['payment_fee'] = 0;
        }

        $order = Order::query()->updateOrCreate(
            ['order_number' => 'ORD-DUMMY-0001'],
            $orderData
        );

        $details = [
            ['product' => 'latte', 'quantity' => 1, 'unit_price' => 25000],
            ['product' => 'fries', 'quantity' => 1, 'unit_price' => 18000],
        ];

        foreach ($details as $detail) {
            OrderDetail::query()->updateOrCreate(
                [
                    'order_id' => $order->id,
                    'product_id' => $products[$detail['product']]->id,
                ],
                [
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'subtotal' => $detail['quantity'] * $detail['unit_price'],
                    'notes' => 'Dummy order item',
                    'created_at' => now()->subDay(),
                ]
            );
        }

        Payment::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'payment_method' => 'cash',
                'amount_paid' => 50000,
                'change_amount' => 7000,
                'payment_date' => now()->subDay(),
                'payment_status' => 'success',
                'created_at' => now()->subDay(),
            ]
        );

        $this->saleStock($products['beans']->id, $order->id, $employeeId, 18);
        $this->saleStock($products['milk']->id, $order->id, $employeeId, 150);
        $this->saleStock($products['cup']->id, $order->id, $employeeId, 1);
        $this->saleStock($products['fries']->id, $order->id, $employeeId, 1);
    }

    private function saleStock(int $productId, int $orderId, int $employeeId, float $quantity): void
    {
        StockTransaction::query()->firstOrCreate(
            [
                'product_id' => $productId,
                'reference_type' => 'sale',
                'reference_id' => $orderId,
            ],
            [
                'transaction_type' => 'out',
                'quantity' => $quantity,
                'employee_id' => $employeeId,
                'notes' => 'Dummy order stock out',
                'transaction_date' => now()->subDay(),
                'created_at' => now()->subDay(),
            ]
        );
    }

    private function syncCurrentStock(): void
    {
        Product::query()->each(function (Product $product) {
            $stockIn = StockTransaction::query()
                ->where('product_id', $product->id)
                ->where('transaction_type', 'in')
                ->sum('quantity');

            $stockOut = StockTransaction::query()
                ->where('product_id', $product->id)
                ->where('transaction_type', 'out')
                ->sum('quantity');

            $adjustment = StockTransaction::query()
                ->where('product_id', $product->id)
                ->where('transaction_type', 'adjustment')
                ->sum('quantity');

            $product->forceFill([
                'current_stock' => (float) $stockIn - (float) $stockOut + (float) $adjustment,
            ])->save();
        });
    }
}
