<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;
use App\Models\CheckoutRequest;
use App\Models\PosCart;
use App\Models\PosCartItem;
use App\Models\PosModifier;
use App\Models\Product;
use App\Services\Pos\CreateCashierOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

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

        $item = $cart->items()->where('product_id', $data['product_id'])->whereDoesntHave('modifiers')->first();

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

    public function addBulkCartItems(Request $request, PosCart $cart): JsonResponse
    {
        $this->authorizeActiveCart($request, $cart);

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('store_id', $cart->store_id)],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($cart, $data): void {
            foreach ($data['items'] as $cartItem) {
                $item = $cart->items()
                    ->where('product_id', $cartItem['product_id'])
                    ->whereDoesntHave('modifiers')
                    ->lockForUpdate()
                    ->first();

                if ($item) {
                    $item->update([
                        'quantity' => (float) $item->quantity + (float) $cartItem['quantity'],
                        'notes' => array_key_exists('notes', $cartItem) ? $cartItem['notes'] : $item->notes,
                    ]);

                    continue;
                }

                $cart->items()->create([
                    'product_id' => $cartItem['product_id'],
                    'quantity' => $cartItem['quantity'],
                    'notes' => $cartItem['notes'] ?? null,
                ]);
            }
        });

        return response()->json([
            'status' => 'sukses',
            'message' => 'created',
            'data' => $this->cartResponse($cart),
        ], 201);
    }

    public function replaceCartItems(Request $request, PosCart $cart): JsonResponse
    {
        $this->authorizeActiveCart($request, $cart);

        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('store_id', $cart->store_id)],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.notes' => ['nullable', 'string'],
            'items.*.modifiers' => ['nullable', 'array', 'max:1'],
            'items.*.modifiers.*.modifier_id' => ['required', 'integer', 'exists:pos_modifiers,id'],
            'items.*.modifiers.*.quantity' => ['nullable', 'integer', 'min:1', 'max:1'],
        ]);

        DB::transaction(function () use ($cart, $data): void {
            $normalizedItems = collect($data['items'])
                ->map(fn (array $item): array => $this->cartItemPayload($cart, $item))
                ->groupBy(fn (array $item): string => $this->cartItemKey($item))
                ->map(function ($items): array {
                    $first = $items->first();
                    $first['quantity'] = $items->sum('quantity');
                    return $first;
                })
                ->values();

            if ($normalizedItems->isEmpty()) {
                $cart->items()->delete();
                return;
            }

            $cart->items()->delete();

            foreach ($normalizedItems as $cartItem) {
                $item = $cart->items()->create([
                    'product_id' => $cartItem['product_id'],
                    'quantity' => $cartItem['quantity'],
                    'notes' => $cartItem['notes'],
                ]);

                foreach ($cartItem['modifiers'] as $modifier) {
                    $item->modifiers()->create([
                        'modifier_id' => $modifier['modifier_id'],
                        'name' => $modifier['name'],
                        'price_delta' => $modifier['price_delta'],
                        'quantity' => $modifier['quantity'],
                        'subtotal' => (float) $modifier['price_delta'] * (float) $modifier['quantity'] * (float) $cartItem['quantity'],
                    ]);
                }
            }
        });

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $this->cartResponse($cart),
        ]);
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
            'idempotency_key' => ['required', 'string', 'max:255'],
        ]);

        return $this->checkoutWithIdempotency($request, $cart, $service, $data, true);
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
        $item = $cart->items()->where('product_id', $data['product_id'])->whereDoesntHave('modifiers')->first();

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
            'idempotency_key' => ['required', 'string', 'max:255'],
        ]);

        $cart = $this->cart($request, (int) $data['store_id']);

        return $this->checkoutWithIdempotency($request, $cart, $service, $data, false);
    }

    private function checkoutWithIdempotency(Request $request, PosCart $cart, CreateCashierOrderService $service, array $data, bool $markCartCheckedOut): JsonResponse
    {
        $employeeId = $request->user()?->employee_id;

        if (!$employeeId) {
            return response()->json(['status' => 'gagal', 'message' => 'user belum terhubung ke employee', 'data' => null], 422);
        }

        $initial = DB::transaction(function () use ($request, $cart, $data, $employeeId): array {
            $lockedCart = PosCart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $this->authorizeCart($request, $lockedCart);

            $checkoutRequest = CheckoutRequest::query()
                ->where('store_id', $lockedCart->store_id)
                ->where('user_id', $request->user()->id)
                ->where('idempotency_key', $data['idempotency_key'])
                ->lockForUpdate()
                ->first();

            $requestHash = $this->checkoutRequestHash($lockedCart, $data, $employeeId);

            if ($checkoutRequest) {
                if ($checkoutRequest->request_hash !== $requestHash) {
                    throw ValidationException::withMessages(['idempotency_key' => ['Idempotency key sudah dipakai untuk payload checkout yang berbeda.']]);
                }

                if ($checkoutRequest->status === 'success' && $checkoutRequest->order_id) {
                    return [
                        'status' => 200,
                        'order' => $checkoutRequest->order->load(['details.product', 'details.modifiers', 'payment']),
                        'checkout_request_id' => $checkoutRequest->id,
                    ];
                }

                if ($checkoutRequest->status === 'processing') {
                    return ['status' => 409, 'order' => null, 'checkout_request_id' => $checkoutRequest->id];
                }

                $checkoutRequest->update(['status' => 'processing', 'error_message' => null]);
            } else {
                $checkoutRequest = CheckoutRequest::query()->create([
                    'store_id' => $lockedCart->store_id,
                    'cart_id' => $lockedCart->id,
                    'user_id' => $request->user()->id,
                    'idempotency_key' => $data['idempotency_key'],
                    'status' => 'processing',
                    'request_hash' => $requestHash,
                ]);
            }

            return ['status' => 201, 'order' => null, 'checkout_request_id' => $checkoutRequest->id];
        });

        if ($initial['status'] === 409) {
            return response()->json(['status' => 'gagal', 'message' => 'checkout sedang diproses', 'data' => null], 409);
        }

        if ($initial['status'] === 200) {
            return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $initial['order']]);
        }

        try {
            $result = DB::transaction(function () use ($request, $cart, $service, $data, $employeeId, $markCartCheckedOut, $initial): array {
                $checkoutRequest = CheckoutRequest::query()->whereKey($initial['checkout_request_id'])->lockForUpdate()->firstOrFail();

                if ($checkoutRequest->status === 'success' && $checkoutRequest->order_id) {
                    return [
                        'status' => 200,
                        'order' => $checkoutRequest->order->load(['details.product', 'details.modifiers', 'payment']),
                    ];
                }

                $lockedCart = PosCart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
                $this->authorizeCart($request, $lockedCart);
                $lockedCart->load(['items.product', 'items.modifiers']);

                if ($lockedCart->status !== 'active') {
                    throw ValidationException::withMessages(['cart' => ['keranjang sudah checkout atau tidak aktif']]);
                }

                if ($lockedCart->items->isEmpty()) {
                    throw ValidationException::withMessages(['cart' => ['keranjang kosong']]);
                }

                $order = $service->create([
                    'order_type' => $data['order_type'],
                    'store_id' => $lockedCart->store_id,
                    'table_id' => $data['table_id'] ?? null,
                    'table_label' => $data['table_label'] ?? null,
                    'employee_id' => $employeeId,
                    'customer_name' => $data['customer_name'] ?? ($markCartCheckedOut ? $lockedCart->name : null),
                    'payment_method' => $data['payment_method'],
                    'amount_paid' => $data['amount_paid'] ?? null,
                    'discount' => $data['discount'] ?? 0,
                    'items' => $lockedCart->items->map(fn (PosCartItem $item) => [
                        'product_id' => $item->product_id,
                        'quantity' => (float) $item->quantity,
                        'notes' => $item->notes,
                        'modifiers' => $this->orderModifierPayload($item),
                    ])->all(),
                ]);

                $lockedCart->items()->delete();

                if ($markCartCheckedOut) {
                    $lockedCart->update(['status' => 'checked_out']);
                }

                $checkoutRequest->update([
                    'order_id' => $order->id,
                    'status' => 'success',
                    'error_message' => null,
                ]);

                return ['status' => 201, 'order' => $order];
            });
        } catch (Throwable $exception) {
            CheckoutRequest::query()
                ->whereKey($initial['checkout_request_id'])
                ->update([
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                ]);

            throw $exception;
        }

        return response()->json([
            'status' => 'sukses',
            'message' => $result['status'] === 200 ? 'ok' : 'checked out',
            'data' => $result['order'],
        ], $result['status']);
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
        $cart->load(['items.product', 'items.modifiers']);

        $items = $cart->items->map(function (PosCartItem $item): array {
            $price = (float) $item->product->selling_price;
            $quantity = (float) $item->quantity;
            $modifiers = $item->modifiers->map(fn ($modifier): array => [
                'modifier_id' => $modifier->modifier_id,
                'name' => $modifier->name,
                'price_delta' => (float) $modifier->price_delta,
                'quantity' => (int) $modifier->quantity,
                'subtotal' => (float) $modifier->subtotal,
            ])->values();

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->product_name,
                'quantity' => $quantity,
                'unit_price' => $price,
                'notes' => $item->notes,
                'modifiers' => $modifiers,
                'subtotal' => ($price * $quantity) + $modifiers->sum('subtotal'),
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

    private function checkoutRequestHash(PosCart $cart, array $data, int $employeeId): string
    {
        $payload = [
            'cart_id' => $cart->id,
            'store_id' => $cart->store_id,
            'employee_id' => $employeeId,
            'order_type' => $data['order_type'],
            'table_id' => $data['table_id'] ?? null,
            'table_label' => $data['table_label'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'payment_method' => $data['payment_method'],
            'amount_paid' => $data['amount_paid'] ?? null,
            'discount' => $data['discount'] ?? 0,
        ];

        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
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

    private function cartItemPayload(PosCart $cart, array $item): array
    {
        $product = Product::query()->with('category')->where('store_id', $cart->store_id)->findOrFail($item['product_id']);
        $modifiers = collect($item['modifiers'] ?? []);
        $modifierIds = $modifiers->pluck('modifier_id');

        if ($modifierIds->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages(['items' => ['Duplicate modifier tidak diperbolehkan.']]);
        }

        $snapshots = $modifiers->map(function (array $modifierItem) use ($cart, $product): array {
            $modifier = PosModifier::query()
                ->where('store_id', $cart->store_id)
                ->where('is_active', true)
                ->whereHas('categories', fn ($query) => $query
                    ->where('categories.id', $product->category_id)
                    ->where('pos_category_modifiers.is_active', true))
                ->find($modifierItem['modifier_id']);

            if (!$modifier) {
                throw ValidationException::withMessages(['items' => ['Modifier tidak tersedia untuk produk ini.']]);
            }

            return [
                'modifier_id' => $modifier->id,
                'name' => $modifier->name,
                'price_delta' => (float) $modifier->price_delta,
                'quantity' => (int) ($modifierItem['quantity'] ?? 1),
            ];
        })->values()->all();

        return [
            'product_id' => $product->id,
            'quantity' => (float) $item['quantity'],
            'notes' => $item['notes'] ?? null,
            'modifiers' => $snapshots,
        ];
    }

    private function cartItemKey(array $item): string
    {
        return $item['product_id'].'|'.($item['notes'] ?? '').'|'.collect($item['modifiers'])->pluck('modifier_id')->sort()->implode(',');
    }

    private function orderModifierPayload(PosCartItem $item): array
    {
        return $item->modifiers->map(fn ($modifier): array => [
            'modifier_id' => $modifier->modifier_id,
            'name' => $modifier->name,
            'price_delta' => (float) $modifier->price_delta,
            'quantity' => (int) $modifier->quantity,
        ])->values()->all();
    }

    private function authorizeItem(Request $request, PosCartItem $item): void
    {
        $item->loadMissing('cart');

        abort_if($item->cart->user_id !== $request->user()->id, 404);
    }
}
