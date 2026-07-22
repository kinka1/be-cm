<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetSummaryController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $products = Product::query();
        $transactions = StockTransaction::query();

        if ($request->filled('store_id')) {
            $products->where('store_id', $request->integer('store_id'));
            $transactions->where('store_id', $request->integer('store_id'));
        }

        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => ['active_products' => (clone $products)->where('is_active', true)->count(), 'low_stock_items' => (clone $products)->whereColumn('current_stock', '<', 'minimum_stock')->count(), 'stock_value' => (clone $products)->selectRaw('COALESCE(SUM(current_stock * cost_price),0) AS value')->value('value'), 'today_transactions' => $transactions->whereDate('transaction_date', today())->count()]]);
    }

    public function lowStockSummary(Request $request): JsonResponse
    {
        $query = Product::whereColumn('current_stock', '<', 'minimum_stock')->orderBy('product_name');

        if ($request->filled('store_id')) $query->where('store_id', $request->integer('store_id'));

        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $query->get()]);
    }

    public function stockMovementSummary(Request $request): JsonResponse
    {
        $query = StockTransaction::query()->select('product_id', 'transaction_type', DB::raw('SUM(quantity) as total_quantity'))->with('product')->groupBy('product_id', 'transaction_type');

        if ($request->filled('store_id')) $query->where('store_id', $request->integer('store_id'));

        $data = $query->get();
        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $data]);
    }
}
