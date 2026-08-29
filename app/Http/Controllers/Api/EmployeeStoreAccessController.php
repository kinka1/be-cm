<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeStoreAccessController extends Controller
{
    public function index(Request $request, Employee $employee): JsonResponse
    {
        $this->authorizeAdminOrSupervisor($request);

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $this->employeeStoreResponse($employee),
        ]);
    }

    public function sync(Request $request, Employee $employee): JsonResponse
    {
        $this->authorizeAdminOrSupervisor($request);

        $data = $request->validate([
            'store_ids' => ['required', 'array'],
            'store_ids.*' => ['integer', 'distinct', 'exists:stores,id'],
            'current_store_id' => ['nullable', 'integer', 'exists:stores,id'],
        ]);

        $storeIds = collect($data['store_ids'])->map(fn ($id): int => (int) $id)->values()->all();
        $currentStoreId = array_key_exists('current_store_id', $data) ? $data['current_store_id'] : null;

        if ($currentStoreId !== null && !in_array((int) $currentStoreId, $storeIds, true)) {
            throw ValidationException::withMessages([
                'current_store_id' => ['Store aktif harus termasuk dalam store_ids.'],
            ]);
        }

        DB::transaction(function () use ($employee, $storeIds, $currentStoreId): void {
            $employee->stores()->sync($storeIds);

            $primaryStoreId = $currentStoreId ?? ($storeIds[0] ?? null);

            $employee->update(['store_id' => $primaryStoreId]);
            $employee->user()->update(['current_store_id' => $primaryStoreId]);
        });

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $this->employeeStoreResponse($employee),
        ]);
    }

    public function attach(Request $request, Employee $employee, Store $store): JsonResponse
    {
        $this->authorizeAdminOrSupervisor($request);

        DB::transaction(function () use ($employee, $store): void {
            $employee->stores()->syncWithoutDetaching([$store->id]);

            if ($employee->store_id === null) {
                $employee->update(['store_id' => $store->id]);
            }

            if ($employee->user?->current_store_id === null) {
                $employee->user()->update(['current_store_id' => $store->id]);
            }
        });

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $this->employeeStoreResponse($employee),
        ]);
    }

    public function detach(Request $request, Employee $employee, Store $store): JsonResponse
    {
        $this->authorizeAdminOrSupervisor($request);

        DB::transaction(function () use ($employee, $store): void {
            $employee->stores()->detach($store->id);

            $remainingStoreId = $employee->stores()->orderBy('stores.id')->value('stores.id');

            if ((int) $employee->store_id === (int) $store->id) {
                $employee->update(['store_id' => $remainingStoreId]);
            }

            if ((int) $employee->user?->current_store_id === (int) $store->id) {
                $employee->user()->update(['current_store_id' => $remainingStoreId]);
            }
        });

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $this->employeeStoreResponse($employee),
        ]);
    }

    private function authorizeAdminOrSupervisor(Request $request): void
    {
        $roleName = strtolower((string) $request->user()?->employee?->role?->role_name);

        abort_unless(in_array($roleName, ['admin', 'supervisor', 'spv'], true), 403, 'hanya admin atau spv yang dapat mengatur akses store karyawan');
    }

    private function employeeStoreResponse(Employee $employee): array
    {
        $employee->load(['stores' => fn ($query) => $query->orderBy('store_name'), 'user.currentStore']);

        return [
            'id' => $employee->id,
            'full_name' => $employee->full_name,
            'store_id' => $employee->store_id,
            'current_store_id' => $employee->user?->current_store_id,
            'stores' => $employee->stores,
            'user' => $employee->user,
        ];
    }
}
