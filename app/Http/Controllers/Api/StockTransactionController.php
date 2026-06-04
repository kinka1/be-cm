<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockTransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockTransaction::query()->orderByDesc('transaction_date');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->integer('employee_id'));
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->string('transaction_type'));
        }

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $query->paginate(15),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'transaction_type' => ['required', 'in:in,out,adjustment'],
            'quantity' => ['required', 'numeric'],
            'reference_type' => ['required', 'in:purchase,sale,adjustment'],
            'reference_id' => ['nullable', 'integer'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'notes' => ['nullable', 'string'],
            'transaction_date' => ['nullable', 'date'],
        ]);

        $transaction = StockTransaction::create([
            'product_id' => $validated['product_id'],
            'transaction_type' => $validated['transaction_type'],
            'quantity' => $validated['quantity'],
            'reference_type' => $validated['reference_type'],
            'reference_id' => $validated['reference_id'] ?? null,
            'employee_id' => $validated['employee_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'transaction_date' => $validated['transaction_date'] ?? now(),
            'created_at' => now(),
        ]);

        return response()->json([
            'status' => 'sukses',
            'message' => 'created',
            'data' => $transaction,
        ], 201);
    }
}
