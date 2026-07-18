<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->orderBy('id');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->string('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('product_name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $query->paginate(15),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'unit_of_measure' => ['required', 'string', 'max:50'],
            'minimum_stock' => ['nullable', 'numeric', 'min:0'],
            'current_stock' => ['nullable', 'numeric'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $product = Product::create($validated);

        return response()->json([
            'status' => 'sukses',
            'message' => 'created',
            'data' => $product,
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $product,
        ]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'product_name' => ['sometimes', 'required', 'string', 'max:255'],
            'sku' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product->id)],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'unit_of_measure' => ['sometimes', 'required', 'string', 'max:50'],
            'minimum_stock' => ['nullable', 'numeric', 'min:0'],
            'current_stock' => ['nullable', 'numeric'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $product->update($validated);

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $product,
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'status' => 'sukses',
            'message' => 'deleted',
            'data' => null,
        ]);
    }

    public function deleted(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => Product::onlyTrashed()->paginate($request->integer('per_page', 15)),
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();

        return response()->json(['status' => 'sukses', 'message' => 'restored', 'data' => $product]);
    }

    public function forceDelete(int $id): JsonResponse
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->forceDelete();

        return response()->json(['status' => 'sukses', 'message' => 'force deleted', 'data' => null]);
    }
}
