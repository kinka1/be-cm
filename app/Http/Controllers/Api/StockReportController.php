<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $this->query($request);

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $query->paginate($request->integer('per_page', 15)),
        ]);
    }

    public function export(Request $request)
    {
        $rows = $this->query($request)->get();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['product_id', 'product_name', 'stock_in_total', 'stock_out_total', 'current_stock', 'last_transaction_date']);
            foreach ($rows as $row) {
                fputcsv($handle, [(string) $row->product_id, $row->product_name, (string) $row->stock_in_total, (string) $row->stock_out_total, (string) $row->current_stock, (string) $row->last_transaction_date]);
            }
            fclose($handle);
        }, 'stock-report.csv');
    }

    private function query(Request $request)
    {
        $query = DB::table('stock_report')
            ->join('products', 'products.id', '=', 'stock_report.product_id')
            ->select('stock_report.*')
            ->orderBy('stock_report.product_id');

        if ($request->filled('category_id')) $query->where('products.category_id', $request->integer('category_id'));
        if ($request->filled('product_id')) $query->where('stock_report.product_id', $request->integer('product_id'));
        if ($request->boolean('low_stock_only')) $query->whereColumn('stock_report.current_stock', '<', 'products.minimum_stock');
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(fn ($builder) => $builder->where('stock_report.product_name', 'like', "%{$search}%")->orWhere('products.sku', 'like', "%{$search}%"));
        }

        return $query;
    }
}
