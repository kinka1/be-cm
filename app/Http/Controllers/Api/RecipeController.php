<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecipeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Recipe::query()
            ->with(['product', 'ingredient'])
            ->orderBy('product_id')
            ->orderBy('ingredient_id');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        if ($request->filled('ingredient_id')) {
            $query->where('ingredient_id', $request->integer('ingredient_id'));
        }

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $query->paginate($this->perPage($request)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());

        $recipe = Recipe::create($validated)->load(['product', 'ingredient']);

        return response()->json([
            'status' => 'sukses',
            'message' => 'created',
            'data' => $recipe,
        ], 201);
    }

    public function show(Recipe $recipe): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $recipe->load(['product', 'ingredient']),
        ]);
    }

    public function update(Request $request, Recipe $recipe): JsonResponse
    {
        $validated = $request->validate($this->rules($recipe));

        $recipe->update($validated);

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $recipe->load(['product', 'ingredient']),
        ]);
    }

    public function destroy(Recipe $recipe): JsonResponse
    {
        $recipe->delete();

        return response()->json([
            'status' => 'sukses',
            'message' => 'deleted',
            'data' => null,
        ]);
    }

    public function productRecipes(Request $request, Product $product): JsonResponse
    {
        $recipes = $product->recipes()
            ->with('ingredient')
            ->orderBy('ingredient_id')
            ->paginate($this->perPage($request));

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => [
                'product' => $product,
                'recipes' => $recipes,
            ],
        ]);
    }

    private function rules(?Recipe $recipe = null): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'ingredient_id' => [
                'required',
                'integer',
                'exists:products,id',
                'different:product_id',
                Rule::unique('recipes', 'ingredient_id')
                    ->where(fn ($query) => $query->where('product_id', request()->integer('product_id')))
                    ->ignore($recipe?->id),
            ],
            'quantity_needed' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', 'string', 'max:50'],
        ];
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 15), 1), 100);
    }
}
