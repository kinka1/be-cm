<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class AttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Attendance::query()->with(['employee', 'store'])->orderByDesc('date')->orderByDesc('clock_in');

        if ($request->filled('employee_id')) $query->where('employee_id', $request->integer('employee_id'));
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('from_date')) $query->whereDate('date', '>=', $request->date('from_date'));
        if ($request->filled('to_date')) $query->whereDate('date', '<=', $request->date('to_date'));
        if ($request->filled('store_id')) $query->where('store_id', $request->integer('store_id'));
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->whereHas('employee', fn ($builder) => $builder->where('full_name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $query->paginate($request->integer('per_page', 15))]);
    }

    public function summary(Request $request): JsonResponse
    {
        $query = Attendance::query();

        if ($request->filled('store_id')) $query->where('store_id', $request->integer('store_id'));
        if ($request->filled('employee_id')) $query->where('employee_id', $request->integer('employee_id'));
        if ($request->filled('from_date')) $query->whereDate('date', '>=', $request->date('from_date'));
        if ($request->filled('to_date')) $query->whereDate('date', '<=', $request->date('to_date'));

        $byStatus = (clone $query)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => [
                'total' => (clone $query)->count(),
                'hadir' => (int) ($byStatus['hadir'] ?? 0),
                'izin' => (int) ($byStatus['izin'] ?? 0),
                'sakit' => (int) ($byStatus['sakit'] ?? 0),
                'alpha' => (int) ($byStatus['alpha'] ?? 0),
                'clocked_in' => (clone $query)->whereNotNull('clock_in')->count(),
                'clocked_out' => (clone $query)->whereNotNull('clock_out')->count(),
            ],
        ]);
    }

    public function today(Request $request): JsonResponse
    {
        $employeeId = $this->employeeId($request, $request->integer('employee_id') ?: null);

        if (!$employeeId) {
            return response()->json(['status' => 'gagal', 'message' => 'employee_id wajib diisi atau user harus terhubung ke employee', 'data' => null], 422);
        }

        if (!$this->canAccessEmployee($request, $employeeId)) {
            return response()->json(['status' => 'gagal', 'message' => 'tidak memiliki akses ke employee ini', 'data' => null], 403);
        }

        $attendance = Attendance::query()->with(['employee', 'store'])->where('employee_id', $employeeId)->whereDate('date', today())->first();

        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $attendance]);
    }

    public function clockIn(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'location_coordinates' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $employeeId = $this->employeeId($request, $data['employee_id'] ?? null);

        if (!$employeeId) {
            return response()->json(['status' => 'gagal', 'message' => 'employee_id wajib diisi atau user harus terhubung ke employee', 'data' => null], 422);
        }

        if (!$this->canAccessEmployee($request, $employeeId)) {
            return response()->json(['status' => 'gagal', 'message' => 'tidak memiliki akses ke employee ini', 'data' => null], 403);
        }

        $employee = Employee::findOrFail($employeeId);

        $attendance = Attendance::query()->firstOrNew(['employee_id' => $employeeId, 'date' => today()->toDateString()]);

        if ($attendance->exists && $attendance->clock_in !== null) {
            return response()->json(['status' => 'gagal', 'message' => 'sudah clock in hari ini', 'data' => $attendance], 422);
        }

        $attendance->fill([
            'store_id' => $employee->store_id,
            'clock_in' => now()->format('H:i:s'),
            'status' => 'hadir',
            'location_coordinates' => $data['location_coordinates'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        if ($request->hasFile('photo')) {
            $photoUrl = $this->storeAttendancePhoto($request);

            if (!$photoUrl) {
                return response()->json(['status' => 'gagal', 'message' => 'gagal menyimpan foto attendance', 'data' => null], 500);
            }

            $attendance->photo_url = $photoUrl;
        }

        $attendance->save();

        return response()->json(['status' => 'sukses', 'message' => 'clock in berhasil', 'data' => $attendance->load(['employee', 'store'])], 201);
    }

    public function clockOut(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'location_coordinates' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $employeeId = $this->employeeId($request, $data['employee_id'] ?? null);

        if (!$employeeId) {
            return response()->json(['status' => 'gagal', 'message' => 'employee_id wajib diisi atau user harus terhubung ke employee', 'data' => null], 422);
        }

        if (!$this->canAccessEmployee($request, $employeeId)) {
            return response()->json(['status' => 'gagal', 'message' => 'tidak memiliki akses ke employee ini', 'data' => null], 403);
        }

        $attendance = Attendance::query()->where('employee_id', $employeeId)->whereDate('date', today())->first();

        if (!$attendance || $attendance->clock_in === null) {
            return response()->json(['status' => 'gagal', 'message' => 'belum clock in hari ini', 'data' => null], 422);
        }

        if ($attendance->clock_out !== null) {
            return response()->json(['status' => 'gagal', 'message' => 'sudah clock out hari ini', 'data' => $attendance], 422);
        }

        $attendance->update([
            'clock_out' => now()->format('H:i:s'),
            'location_coordinates' => $data['location_coordinates'] ?? $attendance->location_coordinates,
            'notes' => $data['notes'] ?? $attendance->notes,
        ]);

        return response()->json(['status' => 'sukses', 'message' => 'clock out berhasil', 'data' => $attendance->load(['employee', 'store'])]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedAttendance($request);
        if (!$this->canAccessEmployee($request, (int) $data['employee_id'])) {
            return response()->json(['status' => 'gagal', 'message' => 'tidak memiliki akses ke employee ini', 'data' => null], 403);
        }
        $attendance = Attendance::create($this->payload($request, $data));

        return response()->json(['status' => 'sukses', 'message' => 'created', 'data' => $attendance->load(['employee', 'store'])], 201);
    }

    public function show(Attendance $attendance): JsonResponse
    {
        return response()->json(['status' => 'sukses', 'message' => 'ok', 'data' => $attendance->load(['employee', 'store'])]);
    }

    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        $data = $this->validatedAttendance($request, $attendance->id);
        if (!$this->canAccessEmployee($request, (int) $data['employee_id'])) {
            return response()->json(['status' => 'gagal', 'message' => 'tidak memiliki akses ke employee ini', 'data' => null], 403);
        }
        $attendance->update($this->payload($request, $data, $attendance));

        return response()->json(['status' => 'sukses', 'message' => 'updated', 'data' => $attendance->load(['employee', 'store'])]);
    }

    public function destroy(Attendance $attendance): JsonResponse
    {
        $attendance->delete();

        return response()->json(['status' => 'sukses', 'message' => 'deleted', 'data' => null]);
    }

    private function validatedAttendance(Request $request, ?int $ignoreId = null): array
    {
        $date = $request->input('date');

        return $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id', Rule::unique('attendances', 'employee_id')->where('date', $date)->ignore($ignoreId)],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'date' => ['required', 'date'],
            'clock_in' => ['nullable', 'date_format:H:i:s'],
            'clock_out' => ['nullable', 'date_format:H:i:s'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'photo_url' => ['nullable', 'string'],
            'status' => ['required', 'in:hadir,izin,sakit,alpha'],
            'location_coordinates' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function payload(Request $request, array $data, ?Attendance $attendance = null): array
    {
        $payload = collect($data)->except(['photo'])->toArray();

        if (empty($payload['store_id']) && !empty($payload['employee_id'])) {
            $payload['store_id'] = Employee::query()->whereKey($payload['employee_id'])->value('store_id');
        }

        if ($request->hasFile('photo')) {
            $photoUrl = $this->storeAttendancePhoto($request);

            if (!$photoUrl) {
                abort(500, 'gagal menyimpan foto attendance');
            }

            $payload['photo_url'] = $photoUrl;
        } elseif ($attendance && !array_key_exists('photo_url', $payload)) {
            $payload['photo_url'] = $attendance->photo_url;
        }

        return $payload;
    }

    private function storeAttendancePhoto(Request $request): ?string
    {
        try {
            Storage::disk('public')->makeDirectory('attendances');
            $path = $request->file('photo')->store('attendances', 'public');
        } catch (Throwable) {
            return null;
        }

        if (!$path) {
            return null;
        }

        return Storage::url($path);
    }

    private function employeeId(Request $request, ?int $fallback = null): ?int
    {
        return $request->user()?->employee_id ?: $fallback;
    }

    private function canAccessEmployee(Request $request, int $employeeId): bool
    {
        $user = $request->user();

        if (!$user || !$user->employee_id) {
            return true;
        }

        if ((int) $user->employee_id === $employeeId) {
            return true;
        }

        $roleName = $user->employee?->role?->role_name;

        return in_array($roleName, ['admin', 'supervisor'], true);
    }
}
