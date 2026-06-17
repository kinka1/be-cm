<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\CreateQrOrderRequest;
use App\Services\Pos\CreateQrOrderService;
use Illuminate\Http\JsonResponse;

class QrOrderController extends Controller
{
    public function store(CreateQrOrderRequest $request, CreateQrOrderService $service): JsonResponse
    {
        $order = $service->create($request->validated());

        return response()->json([
            'status' => 'sukses',
            'message' => 'created',
            'data' => $order,
        ], 201);
    }
}
