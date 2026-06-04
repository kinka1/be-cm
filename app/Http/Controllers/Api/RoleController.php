<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => Role::query()->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role_name' => ['required', 'string', 'max:255', 'unique:roles,role_name'],
        ]);

        $role = Role::create($validated);

        return response()->json([
            'status' => 'sukses',
            'message' => 'created',
            'data' => $role,
        ], 201);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $role,
        ]);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'role_name' => ['required', 'string', 'max:255', Rule::unique('roles', 'role_name')->ignore($role->id)],
        ]);

        $role->update($validated);

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $role,
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $role->delete();

        return response()->json([
            'status' => 'sukses',
            'message' => 'deleted',
            'data' => null,
        ]);
    }
}
