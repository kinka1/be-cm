<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductBatchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductBatch::query()->orderBy('expired_date');
        if ($request->filled('product_id')) $query->where('product_id', $request->integer('product_id'));
        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $query->paginate($request->integer('per_page', 15))]);
    }

    public function store(Request $request): JsonResponse
    {
        $batch = ProductBatch::create($request->validate(['product_id' => ['required','exists:products,id'], 'batch_number' => ['required','string','max:255'], 'expired_date' => ['nullable','date'], 'quantity' => ['required','numeric','min:0'], 'received_date' => ['nullable','date'], 'notes' => ['nullable','string']]));
        return response()->json(['status' => 'sukses', 'message' => 'created', 'data' => $batch], 201);
    }

    public function expiringSoon(Request $request): JsonResponse
    {
        $days = $request->integer('days', 30);
        $batches = ProductBatch::query()->whereNotNull('expired_date')->whereDate('expired_date', '<=', now()->addDays($days))->orderBy('expired_date')->paginate($request->integer('per_page', 15));
        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $batches]);
    }
}
