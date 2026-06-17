<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\Payments\MidtransWebhookController;
use App\Http\Controllers\Api\Pos\CashierOrderController;
use App\Http\Controllers\Api\Pos\MenuController;
use App\Http\Controllers\Api\Pos\OrderController;
use App\Http\Controllers\Api\Pos\OrderStatusController;
use App\Http\Controllers\Api\Pos\QrOrderController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockTransactionController;
use App\Http\Controllers\Api\StockReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
});

Route::apiResource('roles', RoleController::class);
Route::apiResource('employees', EmployeeController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);
Route::get('stock-report', [StockReportController::class, 'index']);
Route::get('stock-transactions', [StockTransactionController::class, 'index']);
Route::post('stock-transactions', [StockTransactionController::class, 'store']);

Route::prefix('pos')->group(function () {
    Route::get('menu', [MenuController::class, 'index']);
    Route::get('tables/{qr_code}/menu', [MenuController::class, 'tableMenu']);
    Route::post('qr-orders', [QrOrderController::class, 'store']);
    Route::post('cashier-orders', [CashierOrderController::class, 'store']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::patch('orders/{order}/status', [OrderStatusController::class, 'update']);
});

Route::post('payments/midtrans/webhook', MidtransWebhookController::class);
