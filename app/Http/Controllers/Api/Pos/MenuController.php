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
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $this->menuQuery($request)->paginate($this->perPage($request)),
        ]);
    }

    public function tableMenu(Request $request, string $qrCode): JsonResponse
    {
        $table = CalonMantu::query()->where('qr_code', $qrCode)->firstOrFail();

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => [
                'table' => $table,
                'menu' => $this->menuQuery($request)->paginate($this->perPage($request)),
            ],
        ]);
    }

    private function menuQuery(Request $request): Builder
    {
        $query = Product::query()
            ->where('is_active', true)
            ->orderBy('product_name');

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

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 15), 1), 100);
    }
}
