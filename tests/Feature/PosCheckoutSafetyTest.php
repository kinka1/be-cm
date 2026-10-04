<?php

namespace Tests\Feature;

use App\Models\CashierSession;
use App\Models\Category;
use App\Models\Employee;
use App\Models\PosCart;
use App\Models\PosModifier;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PosCheckoutSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is not available in this PHP environment.');
        }

        parent::setUp();
    }

    public function test_double_checkout_same_key_creates_one_order_and_retry_returns_same_order(): void
    {
        $context = $this->posContext();
        Sanctum::actingAs($context['user']);

        $cart = PosCart::query()->create([
            'user_id' => $context['user']->id,
            'store_id' => $context['store']->id,
            'name' => 'Meja 01',
            'status' => 'active',
        ]);
        $cart->items()->create(['product_id' => $context['product']->id, 'quantity' => 1]);

        $payload = [
            'order_type' => 'dine_in_cashier',
            'payment_method' => 'cash',
            'amount_paid' => 20000,
            'discount' => 0,
            'idempotency_key' => 'same-key-1',
        ];

        $first = $this->postJson("/api/pos/carts/{$cart->id}/checkout", $payload)->assertCreated();
        $second = $this->postJson("/api/pos/carts/{$cart->id}/checkout", $payload)->assertOk();

        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_same_idempotency_key_with_different_payload_is_rejected(): void
    {
        $context = $this->posContext();
        Sanctum::actingAs($context['user']);

        $cart = PosCart::query()->create([
            'user_id' => $context['user']->id,
            'store_id' => $context['store']->id,
            'name' => 'Meja 01',
            'status' => 'active',
        ]);
        $cart->items()->create(['product_id' => $context['product']->id, 'quantity' => 1]);

        $payload = [
            'order_type' => 'dine_in_cashier',
            'payment_method' => 'cash',
            'amount_paid' => 20000,
            'discount' => 0,
            'idempotency_key' => 'same-key-2',
        ];

        $this->postJson("/api/pos/carts/{$cart->id}/checkout", $payload)->assertCreated();

        $payload['discount'] = 1000;
        $this->postJson("/api/pos/carts/{$cart->id}/checkout", $payload)->assertUnprocessable();
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_checkout_without_idempotency_key_is_rejected(): void
    {
        $context = $this->posContext();
        Sanctum::actingAs($context['user']);

        $cart = PosCart::query()->create([
            'user_id' => $context['user']->id,
            'store_id' => $context['store']->id,
            'name' => 'Meja 01',
            'status' => 'active',
        ]);
        $cart->items()->create(['product_id' => $context['product']->id, 'quantity' => 1]);

        $this->postJson("/api/pos/carts/{$cart->id}/checkout", [
            'order_type' => 'dine_in_cashier',
            'payment_method' => 'cash',
            'amount_paid' => 20000,
        ])->assertUnprocessable();
    }

    public function test_cart_sync_empty_clears_cart(): void
    {
        $context = $this->posContext();
        Sanctum::actingAs($context['user']);

        $cart = PosCart::query()->create(['user_id' => $context['user']->id, 'store_id' => $context['store']->id, 'name' => 'Cart', 'status' => 'active']);
        $cart->items()->create(['product_id' => $context['product']->id, 'quantity' => 1]);

        $this->putJson("/api/pos/carts/{$cart->id}/items", ['items' => []])->assertOk();

        $this->assertDatabaseCount('pos_cart_items', 0);
    }

    public function test_cart_sync_merges_same_product_notes_and_modifier_set(): void
    {
        $context = $this->posContext();
        Sanctum::actingAs($context['user']);
        $modifier = $this->modifierFor($context['store'], $context['category'], 'Upsize');
        $cart = PosCart::query()->create(['user_id' => $context['user']->id, 'store_id' => $context['store']->id, 'name' => 'Cart', 'status' => 'active']);

        $this->putJson("/api/pos/carts/{$cart->id}/items", [
            'items' => [
                ['product_id' => $context['product']->id, 'quantity' => 1, 'notes' => null, 'modifiers' => [['modifier_id' => $modifier->id, 'quantity' => 1]]],
                ['product_id' => $context['product']->id, 'quantity' => 2, 'notes' => null, 'modifiers' => [['modifier_id' => $modifier->id, 'quantity' => 1]]],
            ],
        ])->assertOk();

        $this->assertDatabaseCount('pos_cart_items', 1);
        $this->assertSame('3.00', DB::table('pos_cart_items')->value('quantity'));
    }

    public function test_cart_sync_allows_same_product_with_different_modifier_as_separate_lines(): void
    {
        $context = $this->posContext();
        Sanctum::actingAs($context['user']);
        $modifierA = $this->modifierFor($context['store'], $context['category'], 'Upsize');
        $modifierB = $this->modifierFor($context['store'], $context['category'], 'Extra Shot');
        $cart = PosCart::query()->create(['user_id' => $context['user']->id, 'store_id' => $context['store']->id, 'name' => 'Cart', 'status' => 'active']);

        $this->putJson("/api/pos/carts/{$cart->id}/items", [
            'items' => [
                ['product_id' => $context['product']->id, 'quantity' => 1, 'notes' => null, 'modifiers' => [['modifier_id' => $modifierA->id, 'quantity' => 1]]],
                ['product_id' => $context['product']->id, 'quantity' => 1, 'notes' => null, 'modifiers' => [['modifier_id' => $modifierB->id, 'quantity' => 1]]],
            ],
        ])->assertOk();

        $this->assertDatabaseCount('pos_cart_items', 2);
    }

    public function test_cart_sync_invalid_modifier_rolls_back_existing_cart(): void
    {
        $context = $this->posContext();
        Sanctum::actingAs($context['user']);
        $otherCategory = Category::query()->create(['store_id' => $context['store']->id, 'category_name' => 'Other']);
        $invalidModifier = $this->modifierFor($context['store'], $otherCategory, 'Invalid For Product');
        $cart = PosCart::query()->create(['user_id' => $context['user']->id, 'store_id' => $context['store']->id, 'name' => 'Cart', 'status' => 'active']);
        $cart->items()->create(['product_id' => $context['product']->id, 'quantity' => 1]);

        $this->putJson("/api/pos/carts/{$cart->id}/items", [
            'items' => [
                ['product_id' => $context['product']->id, 'quantity' => 2, 'notes' => null, 'modifiers' => [['modifier_id' => $invalidModifier->id, 'quantity' => 1]]],
            ],
        ])->assertUnprocessable();

        $this->assertDatabaseCount('pos_cart_items', 1);
        $this->assertSame('1.00', DB::table('pos_cart_items')->value('quantity'));
    }

    public function test_checkout_copies_cart_modifiers_to_order_detail_modifiers(): void
    {
        $context = $this->posContext();
        Sanctum::actingAs($context['user']);
        $modifier = $this->modifierFor($context['store'], $context['category'], 'Upsize');
        $cart = PosCart::query()->create(['user_id' => $context['user']->id, 'store_id' => $context['store']->id, 'name' => 'Cart', 'status' => 'active']);

        $this->putJson("/api/pos/carts/{$cart->id}/items", [
            'items' => [
                ['product_id' => $context['product']->id, 'quantity' => 1, 'notes' => null, 'modifiers' => [['modifier_id' => $modifier->id, 'quantity' => 1]]],
            ],
        ])->assertOk();

        $this->postJson("/api/pos/carts/{$cart->id}/checkout", [
            'order_type' => 'dine_in_cashier',
            'payment_method' => 'cash',
            'amount_paid' => 20000,
            'idempotency_key' => 'modifier-checkout',
        ])->assertCreated();

        $this->assertDatabaseHas('order_detail_modifiers', ['modifier_id' => $modifier->id, 'name' => 'Upsize']);
    }

    public function test_print_summary_after_paid_order_is_not_zero_and_matches_selected_session(): void
    {
        $context = $this->posContext();
        Sanctum::actingAs($context['user']);
        $cart = PosCart::query()->create(['user_id' => $context['user']->id, 'store_id' => $context['store']->id, 'name' => 'Cart', 'status' => 'active']);
        $cart->items()->create(['product_id' => $context['product']->id, 'quantity' => 1]);

        $this->postJson("/api/pos/carts/{$cart->id}/checkout", [
            'order_type' => 'dine_in_cashier',
            'payment_method' => 'cash',
            'amount_paid' => 20000,
            'idempotency_key' => 'summary-checkout',
        ])->assertCreated();

        $response = $this->getJson("/api/pos/cashier-sessions/{$context['session']->id}/print-summary")->assertOk();

        $this->assertSame($context['session']->id, $response->json('data.session.id'));
        $this->assertSame(10000, $response->json('data.summary.cash_sales'));
        $this->assertSame(1, $response->json('data.summary.total_orders'));
    }

    public function test_user_cannot_access_print_summary_from_unauthorized_store(): void
    {
        $context = $this->posContext();
        $otherStore = Store::query()->create(['store_name' => 'Other Store', 'code' => 'OTHER', 'is_active' => true]);
        $otherEmployee = Employee::query()->create([
            'store_id' => $otherStore->id,
            'full_name' => 'Other Operator',
            'email' => 'other@example.com',
            'join_date' => now()->toDateString(),
            'role_id' => $context['role']->id,
            'status' => 'active',
        ]);
        $otherSession = CashierSession::query()->create([
            'store_id' => $otherStore->id,
            'employee_id' => $otherEmployee->id,
            'opened_by' => $otherEmployee->id,
            'opening_cash' => 0,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        Sanctum::actingAs($context['user']);

        $this->getJson("/api/pos/cashier-sessions/{$otherSession->id}/print-summary")->assertForbidden();
    }

    public function test_cart_sync_custom_item_creates_cart_line(): void
    {
        $context = $this->posContext();
        Sanctum::actingAs($context['user']);
        $cart = PosCart::query()->create(['user_id' => $context['user']->id, 'store_id' => $context['store']->id, 'name' => 'Cart', 'status' => 'active']);

        $this->putJson("/api/pos/carts/{$cart->id}/items", [
            'items' => [
                ['type' => 'custom', 'custom_name' => 'Menu custom', 'unit_price' => 25000, 'quantity' => 1, 'notes' => 'Manual'],
            ],
        ])->assertOk()->assertJsonPath('data.items.0.type', 'custom');

        $this->assertDatabaseHas('pos_cart_items', [
            'pos_cart_id' => $cart->id,
            'item_type' => 'custom',
            'custom_name' => 'Menu custom',
            'custom_unit_price' => 25000,
        ]);
    }

    public function test_cart_sync_merges_same_custom_items(): void
    {
        $context = $this->posContext();
        Sanctum::actingAs($context['user']);
        $cart = PosCart::query()->create(['user_id' => $context['user']->id, 'store_id' => $context['store']->id, 'name' => 'Cart', 'status' => 'active']);

        $this->putJson("/api/pos/carts/{$cart->id}/items", [
            'items' => [
                ['type' => 'custom', 'custom_name' => 'Tambahan sambal', 'unit_price' => 3000, 'quantity' => 1],
                ['type' => 'custom', 'custom_name' => 'Tambahan sambal', 'unit_price' => 3000, 'quantity' => 2],
            ],
        ])->assertOk();

        $this->assertDatabaseCount('pos_cart_items', 1);
        $this->assertSame('3.00', DB::table('pos_cart_items')->value('quantity'));
    }

    public function test_cart_sync_same_custom_name_with_different_price_creates_separate_lines(): void
    {
        $context = $this->posContext();
        Sanctum::actingAs($context['user']);
        $cart = PosCart::query()->create(['user_id' => $context['user']->id, 'store_id' => $context['store']->id, 'name' => 'Cart', 'status' => 'active']);

        $this->putJson("/api/pos/carts/{$cart->id}/items", [
            'items' => [
                ['type' => 'custom', 'custom_name' => 'Manual charge', 'unit_price' => 3000, 'quantity' => 1],
                ['type' => 'custom', 'custom_name' => 'Manual charge', 'unit_price' => 5000, 'quantity' => 1],
            ],
        ])->assertOk();

        $this->assertDatabaseCount('pos_cart_items', 2);
    }

    public function test_cart_sync_custom_item_with_modifier_is_rejected(): void
    {
        $context = $this->posContext();
        Sanctum::actingAs($context['user']);
        $modifier = $this->modifierFor($context['store'], $context['category'], 'Upsize');
        $cart = PosCart::query()->create(['user_id' => $context['user']->id, 'store_id' => $context['store']->id, 'name' => 'Cart', 'status' => 'active']);

        $this->putJson("/api/pos/carts/{$cart->id}/items", [
            'items' => [
                ['type' => 'custom', 'custom_name' => 'Manual charge', 'unit_price' => 3000, 'quantity' => 1, 'modifiers' => [['modifier_id' => $modifier->id, 'quantity' => 1]]],
            ],
        ])->assertUnprocessable();
    }

    public function test_checkout_custom_item_creates_order_detail_without_product_and_no_stock_transaction(): void
    {
        $context = $this->posContext();
        Sanctum::actingAs($context['user']);
        $cart = PosCart::query()->create(['user_id' => $context['user']->id, 'store_id' => $context['store']->id, 'name' => 'Cart', 'status' => 'active']);

        $this->putJson("/api/pos/carts/{$cart->id}/items", [
            'items' => [
                ['type' => 'custom', 'custom_name' => 'Menu custom', 'unit_price' => 25000, 'quantity' => 1],
            ],
        ])->assertOk();

        $this->postJson("/api/pos/carts/{$cart->id}/checkout", [
            'order_type' => 'dine_in_cashier',
            'payment_method' => 'cash',
            'amount_paid' => 30000,
            'idempotency_key' => 'custom-checkout',
        ])->assertCreated();

        $this->assertDatabaseHas('order_details', ['item_type' => 'custom', 'item_name' => 'Menu custom', 'product_id' => null]);
        $this->assertDatabaseCount('stock_transactions', 0);
    }

    public function test_checkout_mixed_menu_and_custom_total_is_correct(): void
    {
        $context = $this->posContext();
        Sanctum::actingAs($context['user']);
        $cart = PosCart::query()->create(['user_id' => $context['user']->id, 'store_id' => $context['store']->id, 'name' => 'Cart', 'status' => 'active']);

        $this->putJson("/api/pos/carts/{$cart->id}/items", [
            'items' => [
                ['type' => 'menu', 'product_id' => $context['product']->id, 'quantity' => 1],
                ['type' => 'custom', 'custom_name' => 'Menu custom', 'unit_price' => 25000, 'quantity' => 2],
            ],
        ])->assertOk();

        $response = $this->postJson("/api/pos/carts/{$cart->id}/checkout", [
            'order_type' => 'dine_in_cashier',
            'payment_method' => 'cash',
            'amount_paid' => 70000,
            'idempotency_key' => 'mixed-checkout',
        ])->assertCreated();

        $this->assertSame('60000.00', $response->json('data.total_amount'));
    }

    private function posContext(): array
    {
        $store = Store::query()->create(['store_name' => 'Calon Mantu', 'code' => 'CM'.uniqid(), 'is_active' => true]);
        $role = Role::query()->create(['role_name' => 'operator'.uniqid(), 'permissions' => json_encode([])]);
        $employee = Employee::query()->create([
            'store_id' => $store->id,
            'full_name' => 'Operator A',
            'email' => 'operator'.uniqid().'@example.com',
            'join_date' => now()->toDateString(),
            'role_id' => $role->id,
            'status' => 'active',
        ]);
        $user = User::factory()->create([
            'employee_id' => $employee->id,
            'current_store_id' => $store->id,
            'username' => 'operator'.uniqid(),
        ]);
        DB::table('employee_store')->insert([
            'employee_id' => $employee->id,
            'store_id' => $store->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $category = Category::query()->create(['store_id' => $store->id, 'category_name' => 'Premium Series'.uniqid()]);
        $product = Product::query()->create([
            'store_id' => $store->id,
            'product_name' => 'Americano',
            'sku' => 'SKU'.uniqid(),
            'category_id' => $category->id,
            'product_type' => 'menu',
            'unit_of_measure' => 'pcs',
            'current_stock' => 100,
            'selling_price' => 10000,
            'is_active' => true,
        ]);
        $session = CashierSession::query()->create([
            'store_id' => $store->id,
            'employee_id' => $employee->id,
            'opened_by' => $employee->id,
            'opening_cash' => 500000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        return compact('store', 'role', 'employee', 'user', 'category', 'product', 'session');
    }

    private function modifierFor(Store $store, Category $category, string $name): PosModifier
    {
        $modifier = PosModifier::query()->create([
            'store_id' => $store->id,
            'name' => $name,
            'price_delta' => 3000,
            'is_active' => true,
        ]);
        DB::table('pos_category_modifiers')->insert([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'modifier_id' => $modifier->id,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $modifier;
    }
}
