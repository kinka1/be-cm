<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()->orderBy('id');

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->integer('store_id'));
        }

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'category_name' => ['required', 'string', 'max:255', Rule::unique('categories', 'category_name')->where('store_id', $request->integer('store_id'))],
            'description' => ['nullable', 'string'],
        ]);

        $category = Category::create($validated);

        return response()->json([
            'status' => 'sukses',
            'message' => 'created',
            'data' => $category,
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $category,
        ]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'category_name' => ['required', 'string', 'max:255', Rule::unique('categories', 'category_name')->where('store_id', $request->integer('store_id'))->ignore($category->id)],
            'description' => ['nullable', 'string'],
        ]);

        $category->update($validated);

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $category,
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'status' => 'sukses',
            'message' => 'deleted',
            'data' => null,
        ]);
    }
}
