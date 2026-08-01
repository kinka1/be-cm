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
            'items' => $items,
            'subtotal' => $items->sum('subtotal'),
            'total_items' => $items->count(),
        ];
    }

    private function authorizeItem(Request $request, PosCartItem $item): void
    {
        $item->loadMissing('cart');

        abort_if($item->cart->user_id !== $request->user()->id, 404);
    }
}
