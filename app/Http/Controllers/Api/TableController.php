<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalonMantu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TableController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CalonMantu::query()->orderBy('table_number');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->integer('store_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('table_number', 'like', "%{$search}%")
                    ->orWhere('qr_code', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $query->paginate($this->perPage($request)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'table_number' => ['required', 'string', 'max:255'],
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'qr_code' => ['nullable', 'string', 'max:255', 'unique:calon_mantu,qr_code'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['nullable', 'in:available,occupied,reserved'],
        ]);

        $table = CalonMantu::create([
            'store_id' => $validated['store_id'],
            'table_number' => $validated['table_number'],
            'qr_code' => $validated['qr_code'] ?? $this->generateQrCode($validated['table_number']),
            'capacity' => $validated['capacity'],
            'status' => $validated['status'] ?? 'available',
            'created_at' => now(),
        ]);

        return response()->json([
            'status' => 'sukses',
            'message' => 'created',
            'data' => $table,
        ], 201);
    }

    public function show(CalonMantu $table): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $table,
        ]);
    }

    public function update(Request $request, CalonMantu $table): JsonResponse
    {
        $validated = $request->validate([
            'table_number' => ['required', 'string', 'max:255'],
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'qr_code' => ['required', 'string', 'max:255', Rule::unique('calon_mantu', 'qr_code')->ignore($table->id)],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:available,occupied,reserved'],
        ]);

        $table->update($validated);

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $table,
        ]);
    }

    public function destroy(CalonMantu $table): JsonResponse
    {
        $table->delete();

        return response()->json([
            'status' => 'sukses',
            'message' => 'deleted',
            'data' => null,
        ]);
    }

    public function updateStatus(Request $request, CalonMantu $table): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:available,occupied,reserved'],
        ]);

        $table->update($validated);

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $table,
        ]);
    }

    private function generateQrCode(string $tableNumber): string
    {
        return 'CM-TABLE-'.str($tableNumber)->slug()->upper().'-'.random_int(1000, 9999);
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 15), 1), 100);
    }
}
