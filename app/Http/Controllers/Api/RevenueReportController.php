<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevenueReportController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'payment_method' => ['nullable', 'in:cash,qris'],
        ]);

        $query = $this->baseQuery($request)
            ->whereDate('order_date', '>=', $data['from_date'])
            ->whereDate('order_date', '<=', $data['to_date']);

        $daily = (clone $query)
            ->selectRaw('DATE(order_date) as date')
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('COALESCE(SUM(subtotal), 0) as subtotal')
            ->selectRaw('COALESCE(SUM(discount), 0) as discount')
            ->selectRaw('COALESCE(SUM(tax), 0) as tax')
            ->selectRaw('COALESCE(SUM(payment_fee), 0) as payment_fee')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as revenue')
            ->selectRaw("COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN total_amount ELSE 0 END), 0) as cash_revenue")
            ->selectRaw("COALESCE(SUM(CASE WHEN payment_method = 'qris' THEN total_amount ELSE 0 END), 0) as qris_revenue")
            ->groupBy(DB::raw('DATE(order_date)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'total_orders' => (int) $row->total_orders,
                'subtotal' => (float) $row->subtotal,
                'discount' => (float) $row->discount,
                'tax' => (float) $row->tax,
                'payment_fee' => (float) $row->payment_fee,
                'revenue' => (float) $row->revenue,
                'cash_revenue' => (float) $row->cash_revenue,
                'qris_revenue' => (float) $row->qris_revenue,
            ]);

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => [
                'from_date' => $data['from_date'],
                'to_date' => $data['to_date'],
                'store_id' => $request->filled('store_id') ? $request->integer('store_id') : null,
                'payment_method' => $data['payment_method'] ?? null,
                'total_orders' => $daily->sum('total_orders'),
                'subtotal' => $daily->sum('subtotal'),
                'discount' => $daily->sum('discount'),
                'tax' => $daily->sum('tax'),
                'payment_fee' => $daily->sum('payment_fee'),
                'total_revenue' => $daily->sum('revenue'),
                'cash_revenue' => $daily->sum('cash_revenue'),
                'qris_revenue' => $daily->sum('qris_revenue'),
                'daily_details' => $daily,
            ],
        ]);
    }

    public function daily(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'in:cash,qris'],
            'include_orders' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $date = $data['date'] ?? today()->toDateString();
        $query = $this->baseQuery($request)->whereDate('order_date', $date);

        $summary = [
            'date' => $date,
            'store_id' => $request->filled('store_id') ? $request->integer('store_id') : null,
            'payment_method' => $data['payment_method'] ?? null,
            'total_orders' => (clone $query)->count(),
            'subtotal' => (float) (clone $query)->sum('subtotal'),
            'discount' => (float) (clone $query)->sum('discount'),
            'tax' => (float) (clone $query)->sum('tax'),
            'payment_fee' => (float) (clone $query)->sum('payment_fee'),
            'total_revenue' => (float) (clone $query)->sum('total_amount'),
            'cash_revenue' => (float) (clone $query)->where('payment_method', 'cash')->sum('total_amount'),
            'qris_revenue' => (float) (clone $query)->where('payment_method', 'qris')->sum('total_amount'),
        ];

        $orders = null;
        if ($request->boolean('include_orders')) {
            $orders = (clone $query)
                ->with(['store', 'details.product', 'payment'])
                ->orderByDesc('order_date')
                ->paginate($request->integer('per_page', 15));
        }

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => [
                'summary' => $summary,
                'orders' => $orders,
            ],
        ]);
    }

    private function baseQuery(Request $request)
    {
        $query = Order::query()->where('payment_status', 'paid');

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->integer('store_id'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->string('payment_method'));
        }

        return $query;
    }
}
