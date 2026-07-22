<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::query()->with('items')->orderByDesc('order_date');
        if ($request->filled('store_id')) $query->where('store_id', $request->integer('store_id'));
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('supplier_id')) $query->where('supplier_id', $request->integer('supplier_id'));
        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $query->paginate($request->integer('per_page', 15))]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['store_id' => ['required','exists:stores,id'], 'supplier_id' => ['nullable','exists:suppliers,id'], 'employee_id' => ['nullable','exists:employees,id'], 'order_date' => ['required','date'], 'notes' => ['nullable','string'], 'items' => ['required','array','min:1'], 'items.*.product_id' => ['required','exists:products,id'], 'items.*.quantity' => ['required','numeric','gt:0'], 'items.*.unit_cost' => ['nullable','numeric','min:0'], 'items.*.notes' => ['nullable','string']]);
        $po = DB::transaction(function () use ($data) {
            $po = PurchaseOrder::create(['store_id' => $data['store_id'], 'po_number' => 'PO-'.now()->format('YmdHis').'-'.random_int(1000,9999), 'supplier_id' => $data['supplier_id'] ?? null, 'employee_id' => $data['employee_id'] ?? null, 'order_date' => $data['order_date'], 'status' => 'draft', 'notes' => $data['notes'] ?? null]);
            $total = 0;
            foreach ($data['items'] as $item) {
                $subtotal = (float) $item['quantity'] * (float) ($item['unit_cost'] ?? 0);
                $total += $subtotal;
                PurchaseOrderItem::create(['purchase_order_id' => $po->id, 'product_id' => $item['product_id'], 'quantity' => $item['quantity'], 'unit_cost' => $item['unit_cost'] ?? 0, 'subtotal' => $subtotal, 'notes' => $item['notes'] ?? null]);
            }
            $po->update(['total_amount' => $total]);
            return $po->load('items');
        });
        return response()->json(['status' => 'sukses', 'message' => 'created', 'data' => $po], 201);
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse { return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $purchaseOrder->load('items')]); }

    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status === 'received') return response()->json(['status' => 'gagal', 'message' => 'purchase order sudah received', 'data' => null], 422);
        $purchaseOrder->update($request->validate(['store_id' => ['required','exists:stores,id'], 'supplier_id' => ['nullable','exists:suppliers,id'], 'employee_id' => ['nullable','exists:employees,id'], 'order_date' => ['required','date'], 'status' => ['required','in:draft,ordered,cancelled'], 'notes' => ['nullable','string']]));
        return response()->json(['status' => 'sukses', 'message' => 'updated', 'data' => $purchaseOrder]);
    }

    public function receive(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status === 'received') return response()->json(['status' => 'gagal', 'message' => 'purchase order sudah received', 'data' => null], 422);
        DB::transaction(function () use ($purchaseOrder) {
            foreach ($purchaseOrder->items as $item) {
                StockTransaction::create(['store_id' => $purchaseOrder->store_id, 'product_id' => $item->product_id, 'transaction_type' => 'in', 'quantity' => $item->quantity, 'reference_type' => 'purchase', 'reference_id' => $purchaseOrder->id, 'employee_id' => $purchaseOrder->employee_id, 'notes' => 'Receive PO '.$purchaseOrder->po_number, 'transaction_date' => now(), 'created_at' => now()]);
            }
            $purchaseOrder->update(['status' => 'received', 'received_date' => now()->toDateString()]);
        });
        return response()->json(['status' => 'sukses', 'message' => 'received', 'data' => $purchaseOrder->refresh()->load('items')]);
    }

    public function cancel(PurchaseOrder $purchaseOrder): JsonResponse { $purchaseOrder->update(['status' => 'cancelled']); return response()->json(['status' => 'sukses', 'message' => 'cancelled', 'data' => $purchaseOrder]); }
}
