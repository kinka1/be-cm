<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockAdjustment::query()->orderByDesc('created_at');
        if ($request->filled('store_id')) $query->where('store_id', $request->integer('store_id'));
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $query->paginate($request->integer('per_page', 15))]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['product_id' => ['required','exists:products,id'], 'quantity' => ['required','numeric','gt:0'], 'adjustment_type' => ['required','in:increase,decrease'], 'requested_by' => ['nullable','exists:employees,id'], 'reason' => ['nullable','string']]);
        $product = Product::findOrFail($data['product_id']);
        $adjustment = StockAdjustment::create(array_merge($data, ['store_id' => $product->store_id]));
        return response()->json(['status' => 'sukses', 'message' => 'created', 'data' => $adjustment], 201);
    }

    public function approve(Request $request, StockAdjustment $stockAdjustment): JsonResponse
    {
        if ($stockAdjustment->status !== 'pending') return response()->json(['status' => 'gagal', 'message' => 'adjustment tidak pending', 'data' => null], 422);
        $data = $request->validate(['approved_by' => ['nullable','exists:employees,id'], 'approval_notes' => ['nullable','string']]);
        $transaction = StockTransaction::create(['store_id' => $stockAdjustment->store_id, 'product_id' => $stockAdjustment->product_id, 'transaction_type' => $stockAdjustment->adjustment_type === 'increase' ? 'in' : 'out', 'quantity' => $stockAdjustment->quantity, 'reference_type' => 'adjustment', 'reference_id' => $stockAdjustment->id, 'employee_id' => $data['approved_by'] ?? null, 'notes' => $stockAdjustment->reason, 'transaction_date' => now(), 'created_at' => now()]);
        $stockAdjustment->update(['status' => 'approved', 'approved_by' => $data['approved_by'] ?? null, 'approval_notes' => $data['approval_notes'] ?? null, 'approved_at' => now(), 'stock_transaction_id' => $transaction->id]);
        return response()->json(['status' => 'sukses', 'message' => 'approved', 'data' => $stockAdjustment]);
    }

    public function reject(Request $request, StockAdjustment $stockAdjustment): JsonResponse
    {
        $data = $request->validate(['approved_by' => ['nullable','exists:employees,id'], 'approval_notes' => ['nullable','string']]);
        $stockAdjustment->update(['status' => 'rejected', 'approved_by' => $data['approved_by'] ?? null, 'approval_notes' => $data['approval_notes'] ?? null, 'approved_at' => now()]);
        return response()->json(['status' => 'sukses', 'message' => 'rejected', 'data' => $stockAdjustment]);
    }
}
