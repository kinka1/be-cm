<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IngredientStockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DB::table('products')
            ->leftJoin('stock_transactions', 'stock_transactions.product_id', '=', 'products.id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('stores', 'stores.id', '=', 'products.store_id')
            ->where('products.product_type', 'ingredient')
            ->select([
                'products.id as product_id',
                'products.product_name',
                'products.sku',
                'products.store_id',
                'stores.store_name',
                'products.category_id',
                'categories.category_name',
                'products.unit_of_measure',
                'products.minimum_stock',
                'products.current_stock',
                'products.cost_price',
                'products.is_active',
            ])
            ->selectRaw("'ingredient' as product_type")
            ->selectRaw("COALESCE(SUM(CASE WHEN stock_transactions.transaction_type = 'in' THEN stock_transactions.quantity ELSE 0 END), 0) as stock_in_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN stock_transactions.transaction_type = 'out' THEN stock_transactions.quantity ELSE 0 END), 0) as stock_out_total")
            ->selectRaw('MAX(stock_transactions.transaction_date) as last_transaction_date')
            ->selectRaw('products.current_stock < products.minimum_stock as is_low_stock')
            ->groupBy([
                'products.id',
                'products.product_name',
                'products.sku',
                'products.store_id',
                'stores.store_name',
                'products.category_id',
                'categories.category_name',
                'products.unit_of_measure',
                'products.minimum_stock',
                'products.current_stock',
                'products.cost_price',
                'products.is_active',
            ])
            ->orderBy('products.product_name');

        if ($request->filled('store_id')) {
            $query->where('products.store_id', $request->integer('store_id'));
        }

        if ($request->filled('category_id')) {
            $query->where('products.category_id', $request->integer('category_id'));
        }

        if ($request->filled('product_id')) {
            $query->where('products.id', $request->integer('product_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('products.is_active', filter_var($request->string('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->boolean('low_stock_only')) {
            $query->whereColumn('products.current_stock', '<', 'products.minimum_stock');
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search): void {
                $builder->where('products.product_name', 'like', "%{$search}%")
                    ->orWhere('products.sku', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $query->paginate($request->integer('per_page', 15)),
        ]);
    }
}
