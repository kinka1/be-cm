<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;
use App\Models\PosCart;
use App\Models\PosCartItem;
use App\Models\Product;
use App\Services\Pos\CreateCashierOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'status' => ['nullable', 'in:active,checked_out,cancelled'],
        ]);

        $query = PosCart::query()
            ->where('user_id', $request->user()->id)
            ->where('store_id', $data['store_id'])
            ->orderByDesc('updated_at');

        if (!empty($data['status'])) {
            $query->where('status', $data['status']);
        } else {
            $query->where('status', 'active');
        }

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $query->get()->map(fn (PosCart $cart): array => $this->cartResponse($cart))->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $cart = PosCart::query()->create([
            'user_id' => $request->user()->id,
            'store_id' => $data['store_id'],
            'name' => $data['name'],
            'status' => 'active',
        ]);

        return response()->json([
            'status' => 'sukses',
            'message' => 'created',
            'data' => $this->cartResponse($cart),
        ], 201);
    }

    public function showCart(Request $request, PosCart $cart): JsonResponse
    {
        $this->authorizeCart($request, $cart);

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $this->cartResponse($cart),
        ]);
    }

    public function updateCart(Request $request, PosCart $cart): JsonResponse
    {
        $this->authorizeCart($request, $cart);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $cart->update(['name' => $data['name']]);

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $this->cartResponse($cart),
        ]);
    }

    public function deleteCart(Request $request, PosCart $cart): JsonResponse
    {
        $this->authorizeCart($request, $cart);
        $cart->delete();

        return response()->json([
            'status' => 'sukses',
            'message' => 'deleted',
            'data' => null,
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
        ]);

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $this->cartResponse($this->cart($request, (int) $data['store_id'])),
        ]);
    }

    public function addCartItem(Request $request, PosCart $cart): JsonResponse
    {
        $this->authorizeActiveCart($request, $cart);

        $data = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('store_id', $cart->store_id)],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $item = $cart->items()->where('product_id', $data['product_id'])->first();

        if ($item) {
            $item->update([
                'quantity' => (float) $item->quantity + (float) $data['quantity'],
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $item->notes,
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $data['product_id'],
                'quantity' => $data['quantity'],
                'notes' => $data['notes'] ?? null,
            ]);
        }

        return response()->json([
            'status' => 'sukses',
            'message' => 'created',
            'data' => $this->cartResponse($cart),
        ], 201);
    }

    public function updateCartItem(Request $request, PosCart $cart, PosCartItem $item): JsonResponse
    {
        $this->authorizeCartItem($request, $cart, $item);
        $this->abortIfInactive($cart);

        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $item->update([
            'quantity' => $data['quantity'],
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $this->cartResponse($cart),
        ]);
    }

    public function removeCartItem(Request $request, PosCart $cart, PosCartItem $item): JsonResponse
    {
        $this->authorizeCartItem($request, $cart, $item);
        $this->abortIfInactive($cart);
        $item->delete();

        return response()->json([
            'status' => 'sukses',
            'message' => 'deleted',
            'data' => $this->cartResponse($cart),
        ]);
    }

    public function clearCart(Request $request, PosCart $cart): JsonResponse
    {
        $this->authorizeActiveCart($request, $cart);
        $cart->items()->delete();

        return response()->json([
            'status' => 'sukses',
            'message' => 'cleared',
            'data' => $this->cartResponse($cart),
        ]);
    }

    public function checkoutCart(Request $request, PosCart $cart, CreateCashierOrderService $service): JsonResponse
    {
        $this->authorizeActiveCart($request, $cart);

        $data = $request->validate([
            'order_type' => ['required', 'in:dine_in_cashier,takeaway'],
            'table_id' => ['nullable', 'integer', 'exists:calon_mantu,id'],
            'table_label' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,qris,transfer'],
            'amount_paid' => ['required_if:payment_method,cash', 'nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $employeeId = $request->user()?->employee_id;
        if (!$employeeId) {
            return response()->json(['status' => 'gagal', 'message' => 'user belum terhubung ke employee', 'data' => null], 422);
        }

        $cart->load('items');
        if ($cart->items->isEmpty()) {
            return response()->json(['status' => 'gagal', 'message' => 'keranjang kosong', 'data' => null], 422);
        }

        $order = DB::transaction(function () use ($service, $cart, $data, $employeeId) {
            $order = $service->create([
                'order_type' => $data['order_type'],
                'store_id' => $cart->store_id,
                'table_id' => $data['table_id'] ?? null,
                'table_label' => $data['table_label'] ?? null,
                'employee_id' => $employeeId,
                'customer_name' => $data['customer_name'] ?? $cart->name,
                'payment_method' => $data['payment_method'],
                'amount_paid' => $data['amount_paid'] ?? null,
                'discount' => $data['discount'] ?? 0,
                'items' => $cart->items->map(fn (PosCartItem $item) => [
                    'product_id' => $item->product_id,
                    'quantity' => (float) $item->quantity,
                    'notes' => $item->notes,
                ])->all(),
            ]);

            $cart->items()->delete();
            $cart->update(['status' => 'checked_out']);

            return $order;
        });

        return response()->json([
            'status' => 'sukses',
            'message' => 'checked out',
            'data' => $order,
        ], 201);
    }

    public function addItem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('store_id', $request->integer('store_id'))],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $cart = $this->cart($request, (int) $data['store_id']);
        $item = $cart->items()->where('product_id', $data['product_id'])->first();

        if ($item) {
            $item->update([
                'quantity' => (float) $item->quantity + (float) $data['quantity'],
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $item->notes,
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $data['product_id'],
                'quantity' => $data['quantity'],
                'notes' => $data['notes'] ?? null,
            ]);
        }

        return response()->json([
            'status' => 'sukses',
            'message' => 'created',
            'data' => $this->cartResponse($cart),
        ], 201);
    }

    public function updateItem(Request $request, PosCartItem $item): JsonResponse
    {
        $this->authorizeItem($request, $item);

        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $item->update([
            'quantity' => $data['quantity'],
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $this->cartResponse($item->cart),
        ]);
    }

    public function removeItem(Request $request, PosCartItem $item): JsonResponse
    {
        $this->authorizeItem($request, $item);
        $cart = $item->cart;
        $item->delete();

        return response()->json([
            'status' => 'sukses',
            'message' => 'deleted',
            'data' => $this->cartResponse($cart),
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
        ]);

        $cart = $this->cart($request, (int) $data['store_id']);
        $cart->items()->delete();

        return response()->json([
            'status' => 'sukses',
            'message' => 'cleared',
            'data' => $this->cartResponse($cart),
        ]);
    }

    public function checkout(Request $request, CreateCashierOrderService $service): JsonResponse
    {
        $data = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'order_type' => ['required', 'in:dine_in_cashier,takeaway'],
            'table_id' => ['nullable', 'integer', 'exists:calon_mantu,id'],
            'table_label' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,qris,transfer'],
            'amount_paid' => ['required_if:payment_method,cash', 'nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $employeeId = $request->user()?->employee_id;
        if (!$employeeId) {
            return response()->json(['status' => 'gagal', 'message' => 'user belum terhubung ke employee', 'data' => null], 422);
        }

        $cart = $this->cart($request, (int) $data['store_id']);
        $cart->load('items');

        if ($cart->items->isEmpty()) {
            return response()->json(['status' => 'gagal', 'message' => 'keranjang kosong', 'data' => null], 422);
        }

        $order = DB::transaction(function () use ($service, $cart, $data, $employeeId) {
            $order = $service->create([
                'order_type' => $data['order_type'],
                'store_id' => $data['store_id'],
                'table_id' => $data['table_id'] ?? null,
                'table_label' => $data['table_label'] ?? null,
                'employee_id' => $employeeId,
                'customer_name' => $data['customer_name'] ?? null,
                'payment_method' => $data['payment_method'],
                'amount_paid' => $data['amount_paid'] ?? null,
                'discount' => $data['discount'] ?? 0,
                'items' => $cart->items->map(fn (PosCartItem $item) => [
                    'product_id' => $item->product_id,
                    'quantity' => (float) $item->quantity,
                    'notes' => $item->notes,
                ])->all(),
            ]);

            $cart->items()->delete();

            return $order;
        });

        return response()->json([
            'status' => 'sukses',
            'message' => 'checked out',
            'data' => $order,
        ], 201);
    }

    private function cart(Request $request, int $storeId): PosCart
    {
        return PosCart::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'store_id' => $storeId,
        ]);
    }

    private function cartResponse(PosCart $cart): array
    {
        $cart->load(['items.product']);

        $items = $cart->items->map(function (PosCartItem $item): array {
            $price = (float) $item->product->selling_price;
            $quantity = (float) $item->quantity;

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $quantity,
                'unit_price' => $price,
                'subtotal' => $price * $quantity,
                'notes' => $item->notes,
                'product' => $item->product,
            ];
        })->values();

        return [
            'id' => $cart->id,
            'user_id' => $cart->user_id,
            'store_id' => $cart->store_id,
            'name' => $cart->name,
            'status' => $cart->status,
            'items' => $items,
            'subtotal' => $items->sum('subtotal'),
            'total_items' => $items->count(),
        ];
    }

    private function authorizeCart(Request $request, PosCart $cart): void
    {
        abort_if($cart->user_id !== $request->user()->id, 404);
    }

    private function authorizeActiveCart(Request $request, PosCart $cart): void
    {
        $this->authorizeCart($request, $cart);
        $this->abortIfInactive($cart);
    }

    private function authorizeCartItem(Request $request, PosCart $cart, PosCartItem $item): void
    {
        $this->authorizeCart($request, $cart);
        abort_if($item->pos_cart_id !== $cart->id, 404);
    }

    private function abortIfInactive(PosCart $cart): void
    {
        abort_if($cart->status !== 'active', 422, 'keranjang tidak aktif');
    }

    private function authorizeItem(Request $request, PosCartItem $item): void
    {
        $item->loadMissing('cart');

        abort_if($item->cart->user_id !== $request->user()->id, 404);
    }
}
