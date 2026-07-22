<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?: Auth::guard('sanctum')->user();

        if (!$user || !$user->employee_id) {
            return $next($request);
        }

        $allowedStoreIds = $user->accessibleStores()->pluck('stores.id')->map(fn ($id) => (int) $id)->all();

        if ($allowedStoreIds === []) {
            return response()->json(['status' => 'gagal', 'message' => 'user belum memiliki akses toko', 'data' => null], 403);
        }

        $storeId = $this->requestedStoreId($request);

        if ($storeId === null && $user->current_store_id !== null) {
            $storeId = (int) $user->current_store_id;
            $request->merge(['store_id' => $storeId]);
        }

        if ($storeId !== null && !in_array($storeId, $allowedStoreIds, true)) {
            return response()->json(['status' => 'gagal', 'message' => 'tidak memiliki akses ke toko ini', 'data' => null], 403);
        }

        return $next($request);
    }

    private function requestedStoreId(Request $request): ?int
    {
        if ($request->filled('store_id')) {
            return $request->integer('store_id');
        }

        $routeStore = $request->route('store');

        if (is_object($routeStore) && isset($routeStore->id)) {
            return (int) $routeStore->id;
        }

        if (is_numeric($routeStore)) {
            return (int) $routeStore;
        }

        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if (is_object($parameter) && isset($parameter->store_id)) {
                return (int) $parameter->store_id;
            }
        }

        return null;
    }
}
