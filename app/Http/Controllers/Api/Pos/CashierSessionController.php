<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;
use App\Models\CashierCashMovement;
use App\Models\CashierSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashierSessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CashierSession::query()->with(['store', 'employee'])->orderByDesc('opened_at');

        if ($request->filled('store_id')) $query->where('store_id', $request->integer('store_id'));
        if ($request->filled('employee_id')) $query->where('employee_id', $request->integer('employee_id'));
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('from_date')) $query->whereDate('opened_at', '>=', $request->date('from_date'));
        if ($request->filled('to_date')) $query->whereDate('opened_at', '<=', $request->date('to_date'));

        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $query->paginate($request->integer('per_page', 15))]);
    }

    public function open(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'opening_cash' => ['required', 'numeric', 'min:0'],
            'opening_notes' => ['nullable', 'string'],
        ]);

        $employeeId = $this->employeeId($request);

        if (!$employeeId) {
            return response()->json(['status' => 'gagal', 'message' => 'user belum terhubung ke employee', 'data' => null], 422);
        }

        $exists = CashierSession::query()
            ->where('store_id', $data['store_id'])
            ->where('employee_id', $employeeId)
            ->where('status', 'open')
            ->exists();

        if ($exists) {
            return response()->json(['status' => 'gagal', 'message' => 'sesi kasir masih terbuka untuk operator dan toko ini', 'data' => null], 422);
        }

        $session = CashierSession::create([
            'store_id' => $data['store_id'],
            'employee_id' => $employeeId,
            'opened_by' => $employeeId,
            'opening_cash' => $data['opening_cash'],
            'status' => 'open',
            'opened_at' => now(),
            'opening_notes' => $data['opening_notes'] ?? null,
        ]);

        return response()->json(['status' => 'sukses', 'message' => 'opened', 'data' => $session->load(['store', 'employee'])], 201);
    }

    public function current(Request $request): JsonResponse
    {
        $employeeId = $this->employeeId($request);
        $query = CashierSession::query()->with(['store', 'employee'])->where('employee_id', $employeeId)->where('status', 'open')->latest('opened_at');

        if ($request->filled('store_id')) $query->where('store_id', $request->integer('store_id'));

        $session = $query->first();

        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $session]);
    }

    public function show(CashierSession $cashierSession): JsonResponse
    {
        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $cashierSession->load(['store', 'employee', 'orders.payment', 'cashMovements'])]);
    }

    public function addCashMovement(Request $request, CashierSession $cashierSession): JsonResponse
    {
        if ($cashierSession->status !== 'open') {
            return response()->json(['status' => 'gagal', 'message' => 'sesi kasir sudah ditutup', 'data' => null], 422);
        }

        $data = $request->validate([
            'type' => ['required', 'in:cash_in,cash_out'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $movement = CashierCashMovement::create([
            'cashier_session_id' => $cashierSession->id,
            'store_id' => $cashierSession->store_id,
            'employee_id' => $this->employeeId($request),
            'type' => $data['type'],
            'amount' => $data['amount'],
            'category' => $data['category'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return response()->json(['status' => 'sukses', 'message' => 'created', 'data' => $movement], 201);
    }

    public function close(Request $request, CashierSession $cashierSession): JsonResponse
    {
        if ($cashierSession->status !== 'open') {
            return response()->json(['status' => 'gagal', 'message' => 'sesi kasir sudah ditutup', 'data' => null], 422);
        }

        $data = $request->validate([
            'closing_cash' => ['required', 'numeric', 'min:0'],
            'closing_notes' => ['nullable', 'string'],
        ]);

        $summary = $this->summaryData($cashierSession);
        $closingCash = (float) $data['closing_cash'];

        $cashierSession->update([
            'closing_cash' => $closingCash,
            'expected_cash' => $summary['expected_cash'],
            'cash_difference' => $closingCash - $summary['expected_cash'],
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $this->employeeId($request),
            'closing_notes' => $data['closing_notes'] ?? null,
        ]);

        return response()->json(['status' => 'sukses', 'message' => 'closed', 'data' => $cashierSession->refresh()->load(['store', 'employee'])]);
    }

    public function summary(CashierSession $cashierSession): JsonResponse
    {
        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $this->summaryData($cashierSession)]);
    }

    public function orders(Request $request, CashierSession $cashierSession): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $cashierSession->orders()->with(['details.product', 'payment'])->orderByDesc('order_date')->paginate($request->integer('per_page', 15)),
        ]);
    }

    public function cashMovements(Request $request, CashierSession $cashierSession): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $cashierSession->cashMovements()->orderByDesc('created_at')->paginate($request->integer('per_page', 15)),
        ]);
    }

    private function summaryData(CashierSession $session): array
    {
        $orders = $session->orders()->where('payment_status', 'paid');
        $cashSales = (float) (clone $orders)->where('payment_method', 'cash')->sum('total_amount');
        $qrisSales = (float) (clone $orders)->where('payment_method', 'qris')->sum('total_amount');
        $transferSales = (float) (clone $orders)->where('payment_method', 'transfer')->sum('total_amount');
        $cashIn = (float) $session->cashMovements()->where('type', 'cash_in')->sum('amount');
        $cashOut = (float) $session->cashMovements()->where('type', 'cash_out')->sum('amount');
        $openingCash = (float) $session->opening_cash;
        $expectedCash = $openingCash + $cashSales + $cashIn - $cashOut;

        return [
            'cashier_session_id' => $session->id,
            'store_id' => $session->store_id,
            'employee_id' => $session->employee_id,
            'status' => $session->status,
            'opened_at' => $session->opened_at,
            'closed_at' => $session->closed_at,
            'opening_cash' => $openingCash,
            'cash_sales' => $cashSales,
            'qris_sales' => $qrisSales,
            'transfer_sales' => $transferSales,
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'expected_cash' => $expectedCash,
            'closing_cash' => $session->closing_cash === null ? null : (float) $session->closing_cash,
            'cash_difference' => $session->cash_difference === null ? null : (float) $session->cash_difference,
            'total_orders' => (clone $orders)->count(),
        ];
    }

    private function employeeId(Request $request): ?int
    {
        return $request->user()?->employee_id;
    }
}
