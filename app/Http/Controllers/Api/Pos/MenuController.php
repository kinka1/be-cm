<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;
use App\Models\CalonMantu;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $menu = $this->menuQuery($request)->paginate($this->perPage($request));

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $menu->through(fn (Product $product): array => $this->menuItem($product)),
        ]);
    }

    public function tableMenu(Request $request, string $qrCode): JsonResponse
    {
        $table = CalonMantu::query()->where('qr_code', $qrCode)->firstOrFail();

        $menu = $this->menuQuery($request, $table->store_id)->paginate($this->perPage($request));

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => [
                'table' => $table,
                'menu' => $menu->through(fn (Product $product): array => $this->menuItem($product)),
            ],
        ]);
    }

    private function menuQuery(Request $request, ?int $storeId = null): Builder
    {
        $query = Product::query()
            ->with(['category.categoryModifiers.modifier'])
            ->where('is_active', true)
            ->where('product_type', 'menu')
            ->orderBy('product_name');

        if ($storeId !== null) {
            $query->where('store_id', $storeId);
        } elseif ($request->filled('store_id')) {
            $query->where('store_id', $request->integer('store_id'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function (Builder $builder) use ($search) {
                $builder->where('product_name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    private function menuItem(Product $product): array
    {
        $data = $product->toArray();
        $categoryModifiers = $product->category?->categoryModifiers ?? collect();

        $data['modifiers'] = $categoryModifiers
            ->filter(fn ($categoryModifier): bool => (bool) $categoryModifier->is_active && (bool) $categoryModifier->modifier?->is_active)
            ->map(fn ($categoryModifier): array => [
                'id' => $categoryModifier->modifier->id,
                'name' => $categoryModifier->modifier->name,
                'price_delta' => $categoryModifier->modifier->price_delta,
                'is_active' => (bool) $categoryModifier->modifier->is_active,
            ])
            ->values()
            ->all();

        unset($data['category']);

        return $data;
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 15), 1), 100);
    }
}
