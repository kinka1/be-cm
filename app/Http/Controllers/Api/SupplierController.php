<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Supplier::query()->orderBy('supplier_name');
        if ($request->filled('store_id')) $query->where('store_id', $request->integer('store_id'));
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('search')) $query->where('supplier_name', 'like', '%'.$request->string('search').'%');
        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $query->paginate($request->integer('per_page', 15))]);
    }

    public function store(Request $request): JsonResponse
    {
        $supplier = Supplier::create($request->validate(['store_id' => ['required','exists:stores,id'], 'supplier_name' => ['required','string','max:255'], 'contact_name' => ['nullable','string'], 'phone' => ['nullable','string'], 'email' => ['nullable','email'], 'address' => ['nullable','string'], 'status' => ['nullable','in:active,inactive']]));
        return response()->json(['status' => 'sukses', 'message' => 'created', 'data' => $supplier], 201);
    }

    public function show(Supplier $supplier): JsonResponse { return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $supplier]); }

    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($request->validate(['store_id' => ['required','exists:stores,id'], 'supplier_name' => ['required','string','max:255'], 'contact_name' => ['nullable','string'], 'phone' => ['nullable','string'], 'email' => ['nullable','email'], 'address' => ['nullable','string'], 'status' => ['required','in:active,inactive']]));
        return response()->json(['status' => 'sukses', 'message' => 'updated', 'data' => $supplier]);
    }

    public function destroy(Supplier $supplier): JsonResponse { $supplier->delete(); return response()->json(['status' => 'sukses', 'message' => 'deleted', 'data' => null]); }
}
