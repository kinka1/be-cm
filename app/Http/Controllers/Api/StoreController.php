<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Store::query()->orderBy('store_name');

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->string('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(fn ($builder) => $builder->where('store_name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
        }

        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $query->paginate($request->integer('per_page', 15))]);
    }

    public function store(Request $request): JsonResponse
    {
        $store = Store::create($request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:stores,code'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]));

        return response()->json(['status' => 'sukses', 'message' => 'created', 'data' => $store], 201);
    }

    public function show(Store $store): JsonResponse
    {
        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $store]);
    }

    public function update(Request $request, Store $store): JsonResponse
    {
        $store->update($request->validate([
            'store_name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('stores', 'code')->ignore($store->id)],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]));

        return response()->json(['status' => 'sukses', 'message' => 'updated', 'data' => $store]);
    }

    public function destroy(Store $store): JsonResponse
    {
        if ($store->products()->exists() || $store->orders()->exists()) {
            return response()->json(['status' => 'gagal', 'message' => 'store masih memiliki data produk atau order', 'data' => null], 422);
        }

        $store->delete();

        return response()->json(['status' => 'sukses', 'message' => 'deleted', 'data' => null]);
    }
}
