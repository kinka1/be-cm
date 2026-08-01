<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
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
            'payment_method' => ['nullable', 'in:cash,qris,transfer'],
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
            ->selectRaw("COALESCE(SUM(CASE WHEN payment_method = 'transfer' THEN total_amount ELSE 0 END), 0) as transfer_revenue")
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
                'transfer_revenue' => (float) $row->transfer_revenue,
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
                'transfer_revenue' => $daily->sum('transfer_revenue'),
                'daily_details' => $daily,
            ],
        ]);
    }

    public function daily(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'in:cash,qris,transfer'],
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
            'transfer_revenue' => (float) (clone $query)->where('payment_method', 'transfer')->sum('total_amount'),
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

    public function sales(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'payment_method' => ['nullable', 'in:cash,qris,transfer'],
            'group_by' => ['nullable', 'in:day,store,category,product,payment_method'],
            'include_orders' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $ordersQuery = $this->salesOrdersQuery($data);
        $detailsQuery = $this->salesDetailsQuery($data);

        $amounts = $this->salesAmounts($detailsQuery);

        $summary = [
            'total_orders' => (clone $ordersQuery)->count(),
            'total_items' => $amounts['total_items'],
            'gross_sales' => $amounts['gross_sales'],
            'discount' => $amounts['discount'],
            'net_sales' => $amounts['net_sales'],
            'cash_revenue' => $this->salesAmounts((clone $detailsQuery)->where('orders.payment_method', 'cash'))['net_sales'],
            'qris_revenue' => $this->salesAmounts((clone $detailsQuery)->where('orders.payment_method', 'qris'))['net_sales'],
            'transfer_revenue' => $this->salesAmounts((clone $detailsQuery)->where('orders.payment_method', 'transfer'))['net_sales'],
        ];

        $orders = null;
        if ($request->boolean('include_orders')) {
            $orders = (clone $ordersQuery)
                ->with(['store', 'details.product', 'payment'])
                ->orderByDesc('order_date')
                ->paginate($request->integer('per_page', 15));
        }

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => [
                'filters' => [
                    'from_date' => $data['from_date'],
                    'to_date' => $data['to_date'],
                    'store_id' => $data['store_id'] ?? null,
                    'category_id' => $data['category_id'] ?? null,
                    'product_id' => $data['product_id'] ?? null,
                    'payment_method' => $data['payment_method'] ?? null,
                    'group_by' => $data['group_by'] ?? null,
                ],
                'summary' => $summary,
                'breakdown' => $this->salesBreakdown($data),
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

    private function salesOrdersQuery(array $data)
    {
        $query = Order::query()
            ->where('payment_status', 'paid')
            ->whereDate('order_date', '>=', $data['from_date'])
            ->whereDate('order_date', '<=', $data['to_date']);

        if (!empty($data['store_id'])) {
            $query->where('store_id', $data['store_id']);
        }

        if (!empty($data['payment_method'])) {
            $query->where('payment_method', $data['payment_method']);
        }

        if (!empty($data['product_id']) || !empty($data['category_id'])) {
            $query->whereHas('details.product', function ($productQuery) use ($data): void {
                if (!empty($data['product_id'])) {
                    $productQuery->where('products.id', $data['product_id']);
                }

                if (!empty($data['category_id'])) {
                    $productQuery->where('products.category_id', $data['category_id']);
                }
            });
        }

        return $query;
    }

    private function salesDetailsQuery(array $data)
    {
        $query = OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('stores', 'stores.id', '=', 'orders.store_id')
            ->where('orders.payment_status', 'paid')
            ->whereDate('orders.order_date', '>=', $data['from_date'])
            ->whereDate('orders.order_date', '<=', $data['to_date']);

        if (!empty($data['store_id'])) {
            $query->where('orders.store_id', $data['store_id']);
        }

        if (!empty($data['category_id'])) {
            $query->where('products.category_id', $data['category_id']);
        }

        if (!empty($data['product_id'])) {
            $query->where('order_details.product_id', $data['product_id']);
        }

        if (!empty($data['payment_method'])) {
            $query->where('orders.payment_method', $data['payment_method']);
        }

        return $query;
    }

    private function salesBreakdown(array $data)
    {
        $groupBy = $data['group_by'] ?? null;

        if (!$groupBy) {
            return [];
        }

        $query = $this->salesDetailsQuery($data)
            ->selectRaw('COUNT(DISTINCT orders.id) as total_orders')
            ->selectRaw('COALESCE(SUM(order_details.quantity), 0) as total_items')
            ->selectRaw('COALESCE(SUM(order_details.subtotal), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(CASE WHEN orders.subtotal > 0 THEN order_details.subtotal / orders.subtotal * orders.discount ELSE 0 END), 0) as discount')
            ->selectRaw('COALESCE(SUM(order_details.subtotal - CASE WHEN orders.subtotal > 0 THEN order_details.subtotal / orders.subtotal * orders.discount ELSE 0 END), 0) as net_sales');

        match ($groupBy) {
            'day' => $query->selectRaw('DATE(orders.order_date) as date')->groupBy(DB::raw('DATE(orders.order_date)'))->orderBy('date'),
            'store' => $query->selectRaw('orders.store_id, stores.store_name')->groupBy('orders.store_id', 'stores.store_name')->orderBy('stores.store_name'),
            'category' => $query->selectRaw('products.category_id, categories.category_name')->groupBy('products.category_id', 'categories.category_name')->orderBy('categories.category_name'),
            'product' => $query->selectRaw('order_details.product_id, products.product_name, products.sku, products.category_id, categories.category_name')->groupBy('order_details.product_id', 'products.product_name', 'products.sku', 'products.category_id', 'categories.category_name')->orderBy('products.product_name'),
            'payment_method' => $query->selectRaw('orders.payment_method')->groupBy('orders.payment_method')->orderBy('orders.payment_method'),
        };

        return $query->get()->map(function ($row) use ($groupBy): array {
            $data = [
                'total_orders' => (int) $row->total_orders,
                'total_items' => (float) $row->total_items,
                'gross_sales' => (float) $row->gross_sales,
                'discount' => (float) $row->discount,
                'net_sales' => (float) $row->net_sales,
            ];

            return match ($groupBy) {
                'day' => ['date' => $row->date] + $data,
                'store' => ['store_id' => $row->store_id, 'store_name' => $row->store_name] + $data,
                'category' => ['category_id' => $row->category_id, 'category_name' => $row->category_name] + $data,
                'product' => ['product_id' => $row->product_id, 'product_name' => $row->product_name, 'sku' => $row->sku, 'category_id' => $row->category_id, 'category_name' => $row->category_name] + $data,
                'payment_method' => ['payment_method' => $row->payment_method] + $data,
            };
        });
    }

    private function salesAmounts($detailsQuery): array
    {
        $row = (clone $detailsQuery)
            ->selectRaw('COALESCE(SUM(order_details.quantity), 0) as total_items')
            ->selectRaw('COALESCE(SUM(order_details.subtotal), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(CASE WHEN orders.subtotal > 0 THEN order_details.subtotal / orders.subtotal * orders.discount ELSE 0 END), 0) as discount')
            ->selectRaw('COALESCE(SUM(order_details.subtotal - CASE WHEN orders.subtotal > 0 THEN order_details.subtotal / orders.subtotal * orders.discount ELSE 0 END), 0) as net_sales')
            ->first();

        return [
            'total_items' => (float) $row->total_items,
            'gross_sales' => (float) $row->gross_sales,
            'discount' => (float) $row->discount,
            'net_sales' => (float) $row->net_sales,
        ];
    }
}
