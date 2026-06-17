<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\CreateCashierOrderRequest;
use App\Services\Pos\CreateCashierOrderService;
use Illuminate\Http\JsonResponse;

class CashierOrderController extends Controller
{
    public function store(CreateCashierOrderRequest $request, CreateCashierOrderService $service): JsonResponse
    {
        $order = $service->create($request->validated());

        return response()->json([
            'status' => 'sukses',
            'message' => 'created',
            'data' => $order,
        ], 201);
    }
}
