<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\StockTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockOpname::query()->with('items')->orderByDesc('opname_date');
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $query->paginate($request->integer('per_page', 15))]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['employee_id' => ['nullable','exists:employees,id'], 'opname_date' => ['required','date'], 'notes' => ['nullable','string']]);
        $opname = StockOpname::create(['opname_number' => 'OPN-'.now()->format('YmdHis').'-'.random_int(1000,9999), 'employee_id' => $data['employee_id'] ?? null, 'opname_date' => $data['opname_date'], 'notes' => $data['notes'] ?? null]);
        return response()->json(['status' => 'sukses', 'message' => 'created', 'data' => $opname], 201);
    }

    public function show(StockOpname $stockOpname): JsonResponse { return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $stockOpname->load('items')]); }

    public function addItem(Request $request, StockOpname $stockOpname): JsonResponse
    {
        if ($stockOpname->status !== 'draft') return response()->json(['status' => 'gagal', 'message' => 'opname bukan draft', 'data' => null], 422);
        $data = $request->validate(['product_id' => ['required','exists:products,id'], 'physical_stock' => ['required','numeric'], 'notes' => ['nullable','string']]);
        $product = Product::findOrFail($data['product_id']);
        $item = StockOpnameItem::updateOrCreate(['stock_opname_id' => $stockOpname->id, 'product_id' => $product->id], ['system_stock' => $product->current_stock, 'physical_stock' => $data['physical_stock'], 'difference' => (float) $data['physical_stock'] - (float) $product->current_stock, 'notes' => $data['notes'] ?? null]);
        return response()->json(['status' => 'sukses', 'message' => 'saved', 'data' => $item]);
    }

    public function submit(StockOpname $stockOpname): JsonResponse
    {
        $stockOpname->update(['status' => 'submitted', 'submitted_at' => now()]);
        return response()->json(['status' => 'sukses', 'message' => 'submitted', 'data' => $stockOpname]);
    }

    public function approve(Request $request, StockOpname $stockOpname): JsonResponse
    {
        $data = $request->validate(['approved_by' => ['nullable','exists:employees,id']]);
        DB::transaction(function () use ($stockOpname, $data) {
            foreach ($stockOpname->items as $item) {
                if ((float) $item->difference === 0.0) continue;
                StockTransaction::create(['product_id' => $item->product_id, 'transaction_type' => $item->difference > 0 ? 'in' : 'out', 'quantity' => abs((float) $item->difference), 'reference_type' => 'adjustment', 'reference_id' => $stockOpname->id, 'employee_id' => $data['approved_by'] ?? null, 'notes' => 'Stock opname '.$stockOpname->opname_number, 'transaction_date' => now(), 'created_at' => now()]);
            }
            $stockOpname->update(['status' => 'approved', 'approved_by' => $data['approved_by'] ?? null, 'approved_at' => now()]);
        });
        return response()->json(['status' => 'sukses', 'message' => 'approved', 'data' => $stockOpname->refresh()->load('items')]);
    }
}
