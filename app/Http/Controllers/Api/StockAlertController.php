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

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->integer('store_id'));
        }

        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $query->paginate($request->integer('per_page', 15))]);
    }

    public function summary(Request $request): JsonResponse
    {
        $lowStockQuery = Product::query()->whereColumn('current_stock', '<', 'minimum_stock');
        $outOfStockQuery = Product::query()->where('current_stock', '<=', 0);

        if ($request->filled('store_id')) {
            $lowStockQuery->where('store_id', $request->integer('store_id'));
            $outOfStockQuery->where('store_id', $request->integer('store_id'));
        }

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => [
                'low_stock_count' => $lowStockQuery->count(),
                'out_of_stock_count' => $outOfStockQuery->count(),
            ],
        ]);
    }
}
