<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AssetSummaryController extends Controller
{
    public function summary(): JsonResponse
    {
        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => ['active_products' => Product::where('is_active', true)->count(), 'low_stock_items' => Product::whereColumn('current_stock', '<', 'minimum_stock')->count(), 'stock_value' => Product::selectRaw('COALESCE(SUM(current_stock * cost_price),0) AS value')->value('value'), 'today_transactions' => StockTransaction::whereDate('transaction_date', today())->count()]]);
    }

    public function lowStockSummary(): JsonResponse { return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => Product::whereColumn('current_stock', '<', 'minimum_stock')->orderBy('product_name')->get()]); }

    public function stockMovementSummary(): JsonResponse
    {
        $data = StockTransaction::query()->select('product_id', 'transaction_type', DB::raw('SUM(quantity) as total_quantity'))->with('product')->groupBy('product_id', 'transaction_type')->get();
        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $data]);
    }
}
