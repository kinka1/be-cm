<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AssetSummaryController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\Payments\BniQrisDevelopmentController;
use App\Http\Controllers\Api\Payments\BniQrisWebhookController;
use App\Http\Controllers\Api\Payments\MidtransWebhookController;
use App\Http\Controllers\Api\Pos\CashierOrderController;
use App\Http\Controllers\Api\Pos\CashierSessionController;
use App\Http\Controllers\Api\Pos\MenuController;
use App\Http\Controllers\Api\Pos\OrderController;
use App\Http\Controllers\Api\Pos\OrderStatusController;
use App\Http\Controllers\Api\Pos\PaymentMethodController;
use App\Http\Controllers\Api\Pos\QrOrderController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\RevenueReportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductBatchController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\StockTransactionController;
use App\Http\Controllers\Api\StockReportController;
use App\Http\Controllers\Api\StockAdjustmentController;
use App\Http\Controllers\Api\StockAlertController;
use App\Http\Controllers\Api\StockCardController;
use App\Http\Controllers\Api\StockOpnameController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TableController;
use App\Models\CalonMantu;
use Illuminate\Support\Facades\Route;

Route::model('table', CalonMantu::class);

Route::get('health', fn () => response()->json([
    'status' => 'sukses',
    'message' => 'ok',
    'data' => [
        'service' => config('app.name'),
        'timestamp' => now()->toISOString(),
    ],
]));

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::get('me/stores', [AuthController::class, 'stores']);
    Route::post('me/current-store', [AuthController::class, 'updateCurrentStore']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('dev/bni-qris/create-test', [BniQrisDevelopmentController::class, 'createTest']);
    Route::get('attendances/today', [AttendanceController::class, 'today']);
    Route::get('attendances/summary', [AttendanceController::class, 'summary']);
    Route::post('attendances/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('attendances/clock-out', [AttendanceController::class, 'clockOut']);
    Route::apiResource('attendances', AttendanceController::class);

    Route::apiResource('roles', RoleController::class);
    Route::apiResource('stores', StoreController::class);
    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::get('products/deleted', [ProductController::class, 'deleted']);
    Route::post('products/{id}/restore', [ProductController::class, 'restore']);
    Route::delete('products/{id}/force', [ProductController::class, 'forceDelete']);
    Route::get('products/{product}/stock-card', [StockCardController::class, 'show']);
    Route::get('products/{product}/recipes', [RecipeController::class, 'productRecipes']);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('recipes', RecipeController::class);
    Route::get('stock-alerts', [StockAlertController::class, 'index']);
    Route::get('stock-alerts/summary', [StockAlertController::class, 'summary']);
    Route::get('stock-report/export', [StockReportController::class, 'export']);
    Route::patch('tables/{table}/status', [TableController::class, 'updateStatus']);
    Route::apiResource('tables', TableController::class);
    Route::get('stock-report', [StockReportController::class, 'index']);
    Route::get('stock-transactions', [StockTransactionController::class, 'index']);
    Route::post('stock-transactions', [StockTransactionController::class, 'store']);
    Route::apiResource('suppliers', SupplierController::class);
    Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive']);
    Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel']);
    Route::apiResource('purchase-orders', PurchaseOrderController::class)->except(['destroy']);
    Route::post('stock-opnames/{stockOpname}/items', [StockOpnameController::class, 'addItem']);
    Route::post('stock-opnames/{stockOpname}/submit', [StockOpnameController::class, 'submit']);
    Route::post('stock-opnames/{stockOpname}/approve', [StockOpnameController::class, 'approve']);
    Route::apiResource('stock-opnames', StockOpnameController::class)->only(['index', 'store', 'show']);
    Route::post('stock-adjustments/{stockAdjustment}/approve', [StockAdjustmentController::class, 'approve']);
    Route::post('stock-adjustments/{stockAdjustment}/reject', [StockAdjustmentController::class, 'reject']);
    Route::get('stock-adjustments', [StockAdjustmentController::class, 'index']);
    Route::post('stock-adjustments', [StockAdjustmentController::class, 'store']);
    Route::get('product-batches/expiring-soon', [ProductBatchController::class, 'expiringSoon']);
    Route::get('product-batches', [ProductBatchController::class, 'index']);
    Route::post('product-batches', [ProductBatchController::class, 'store']);
    Route::get('assets/summary', [AssetSummaryController::class, 'summary']);
    Route::get('assets/low-stock-summary', [AssetSummaryController::class, 'lowStockSummary']);
    Route::get('assets/stock-movement-summary', [AssetSummaryController::class, 'stockMovementSummary']);
    Route::get('revenue/summary', [RevenueReportController::class, 'summary']);
    Route::get('revenue/daily', [RevenueReportController::class, 'daily']);
});

Route::prefix('pos')->group(function () {
    Route::get('tables/{qr_code}/menu', [MenuController::class, 'tableMenu']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('payment-methods', [PaymentMethodController::class, 'index']);
        Route::get('menu', [MenuController::class, 'index']);
        Route::post('cashier-sessions/open', [CashierSessionController::class, 'open']);
        Route::get('cashier-sessions/current', [CashierSessionController::class, 'current']);
        Route::get('cashier-sessions', [CashierSessionController::class, 'index']);
        Route::get('cashier-sessions/{cashierSession}', [CashierSessionController::class, 'show']);
        Route::post('cashier-sessions/{cashierSession}/cash-movements', [CashierSessionController::class, 'addCashMovement']);
        Route::post('cashier-sessions/{cashierSession}/close', [CashierSessionController::class, 'close']);
        Route::get('cashier-sessions/{cashierSession}/summary', [CashierSessionController::class, 'summary']);
        Route::get('cashier-sessions/{cashierSession}/orders', [CashierSessionController::class, 'orders']);
        Route::get('cashier-sessions/{cashierSession}/cash-movements', [CashierSessionController::class, 'cashMovements']);
        Route::post('cashier-orders', [CashierOrderController::class, 'store']);
        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::patch('orders/{order}/status', [OrderStatusController::class, 'update']);
    });
    Route::post('qr-orders', [QrOrderController::class, 'store']);
});

Route::post('payments/midtrans/webhook', MidtransWebhookController::class);
Route::post('payments/bni-qris/webhook', BniQrisWebhookController::class);
