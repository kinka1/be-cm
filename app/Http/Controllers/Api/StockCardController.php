<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockCardController extends Controller
{
    public function show(Request $request, Product $product): JsonResponse
    {
        $query = StockTransaction::query()->with('employee')->where('product_id', $product->id)->orderBy('transaction_date');

        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->date('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->date('to_date'));
        }
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->string('transaction_type'));
        }
        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->string('reference_type'));
        }

        $balance = 0;
        $transactions = $query->get()->map(function (StockTransaction $transaction) use (&$balance) {
            $quantity = (float) $transaction->quantity;
            $balance += $transaction->transaction_type === 'out' ? -$quantity : $quantity;
            $transaction->running_balance = $balance;
            return $transaction;
        });

        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => ['product' => $product, 'transactions' => $transactions]]);
    }
}
