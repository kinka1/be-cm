<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockAlertController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->whereColumn('current_stock', '<', 'minimum_stock')->orderBy('product_name');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $query->paginate($request->integer('per_page', 15))]);
    }

    public function summary(): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => [
                'low_stock_count' => Product::query()->whereColumn('current_stock', '<', 'minimum_stock')->count(),
                'out_of_stock_count' => Product::query()->where('current_stock', '<=', 0)->count(),
            ],
        ]);
    }
}
