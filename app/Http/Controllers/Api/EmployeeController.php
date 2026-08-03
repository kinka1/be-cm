<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Employee::query()->orderBy('id');

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->integer('role_id'));
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->integer('store_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $query->paginate(15),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $existingEmployee = $this->existingEmployeeForUpsert($request);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($existingEmployee?->id),
                Rule::unique('users', 'email')->ignore($existingEmployee?->id, 'employee_id'),
            ],
            'join_date' => ['required', 'date'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($existingEmployee?->id, 'employee_id')],
            'password' => [$existingEmployee ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'ktp' => [$existingEmployee ? 'sometimes' : 'required', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'kk' => [$existingEmployee ? 'sometimes' : 'required', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $employee = DB::transaction(function () use ($request, $validated, $existingEmployee) {
            $employee = $existingEmployee ?: new Employee();

            if ($request->hasFile('ktp')) {
                $ktpPath = $request->file('ktp')->store('employees/ktp', 'public');
                $employee->ktp_url = Storage::url($ktpPath);
            }

            if ($request->hasFile('kk')) {
                $kkPath = $request->file('kk')->store('employees/kk', 'public');
                $employee->kk_url = Storage::url($kkPath);
            }

            $employee->fill([
                'full_name' => $validated['full_name'],
                'store_id' => $validated['store_id'],
                'email' => $validated['email'],
                'join_date' => $validated['join_date'],
                'role_id' => $validated['role_id'],
                'status' => $validated['status'] ?? ($employee->status ?: 'active'),
            ]);
            $employee->save();

            $employee->stores()->syncWithoutDetaching([$validated['store_id']]);

            $userPayload = [
                'current_store_id' => $validated['store_id'],
                'name' => $employee->full_name,
                'username' => $validated['username'],
                'email' => $employee->email,
            ];

            if (!empty($validated['password'])) {
                $userPayload['password'] = Hash::make($validated['password']);
            }

            User::query()->updateOrCreate(
                ['employee_id' => $employee->id],
                $userPayload
            );

            return $employee;
        });

        return response()->json([
            'status' => 'sukses',
            'message' => $existingEmployee ? 'updated' : 'created',
            'data' => $employee,
        ], $existingEmployee ? 200 : 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $employee,
        ]);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['sometimes', 'required', 'string', 'max:255'],
            'store_id' => ['sometimes', 'required', 'integer', 'exists:stores,id'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($employee->id), Rule::unique('users', 'email')->ignore($employee->id, 'employee_id')],
            'join_date' => ['sometimes', 'required', 'date'],
            'role_id' => ['sometimes', 'required', 'integer', 'exists:roles,id'],
            'status' => ['sometimes', 'required', 'in:active,inactive'],
            'ktp' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'kk' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ]);

        $employee = DB::transaction(function () use ($request, $employee, $validated) {
            if ($request->hasFile('ktp')) {
                $ktpPath = $request->file('ktp')->store('employees/ktp', 'public');
                $employee->ktp_url = Storage::url($ktpPath);
            }

            if ($request->hasFile('kk')) {
                $kkPath = $request->file('kk')->store('employees/kk', 'public');
                $employee->kk_url = Storage::url($kkPath);
            }

            $employee->fill(collect($validated)->except(['ktp', 'kk'])->toArray());
            $employee->save();

            if (array_key_exists('store_id', $validated)) {
                $employee->stores()->syncWithoutDetaching([$validated['store_id']]);
                User::where('employee_id', $employee->id)->update(['current_store_id' => $validated['store_id']]);
            }

            User::where('employee_id', $employee->id)->update([
                'name' => $employee->full_name,
                'email' => $employee->email,
            ]);

            return $employee;
        });

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $employee,
        ]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json([
            'status' => 'sukses',
            'message' => 'deleted',
            'data' => null,
        ]);
    }

    private function existingEmployeeForUpsert(Request $request): ?Employee
    {
        $user = $request->user();

        if ($user?->employee?->role?->role_name !== 'admin') {
            return null;
        }

        if ($request->filled('email')) {
            $employee = Employee::query()->where('email', $request->string('email'))->first();

            if ($employee) {
                return $employee;
            }
        }

        if ($request->filled('username')) {
            $user = User::query()->where('username', $request->string('username'))->first();

            if ($user?->employee_id) {
                return Employee::query()->find($user->employee_id);
            }
        }

        return null;
    }
}
