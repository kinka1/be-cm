<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PosCategoryModifier;
use App\Models\PosModifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModifierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $query = PosModifier::query()
            ->where('store_id', $data['store_id'])
            ->orderBy('name');

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'name' => ['required', 'string', 'max:255', Rule::unique('pos_modifiers', 'name')->where('store_id', $request->integer('store_id'))],
            'price_delta' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $modifier = PosModifier::query()->create([
            'store_id' => $data['store_id'],
            'name' => $data['name'],
            'price_delta' => $data['price_delta'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json([
            'status' => 'sukses',
            'message' => 'created',
            'data' => $modifier,
        ], 201);
    }

    public function update(Request $request, PosModifier $modifier): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required_without_all:price_delta,is_active', 'string', 'max:255', Rule::unique('pos_modifiers', 'name')->where('store_id', $modifier->store_id)->ignore($modifier->id)],
            'price_delta' => ['required_without_all:name,is_active', 'numeric', 'min:0'],
            'is_active' => ['required_without_all:name,price_delta', 'boolean'],
        ]);

        $modifier->update($data);

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $modifier->fresh(),
        ]);
    }

    public function destroy(PosModifier $modifier): JsonResponse
    {
        $modifier->delete();

        return response()->json([
            'status' => 'sukses',
            'message' => 'deleted',
            'data' => null,
        ]);
    }

    public function assignToCategory(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'modifier_id' => ['required', 'integer', Rule::exists('pos_modifiers', 'id')->where('store_id', $category->store_id)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $categoryModifier = PosCategoryModifier::query()->updateOrCreate(
            [
                'category_id' => $category->id,
                'modifier_id' => $data['modifier_id'],
            ],
            [
                'store_id' => $category->store_id,
                'is_active' => $data['is_active'] ?? true,
            ]
        );

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $categoryModifier->load(['category', 'modifier']),
        ]);
    }
}
