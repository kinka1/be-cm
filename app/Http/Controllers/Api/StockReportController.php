<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StockReportController extends Controller
{
    public function index(): JsonResponse
    {
        $report = DB::table('stock_report')->orderBy('product_id')->get();

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $report,
        ]);
    }
}
