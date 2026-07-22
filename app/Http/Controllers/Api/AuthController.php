<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request) {
            $storeId = $request->integer('store_id') ?: DB::table('stores')->where('code', 'MAIN')->value('id');

            $employeeId = DB::table('employees')->insertGetId([
                'store_id' => $storeId,
                'full_name' => $request->string('full_name'),
                'email' => $request->string('email'),
                'join_date' => $request->date('join_date')->toDateString(),
                'role_id' => $request->integer('role_id'),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($storeId) {
                DB::table('employee_store')->updateOrInsert(
                    ['employee_id' => $employeeId, 'store_id' => $storeId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }

            return User::create([
                'employee_id' => $employeeId,
                'current_store_id' => $storeId,
                'name' => $request->string('name'),
                'username' => $request->string('username'),
                'email' => $request->string('email'),
                'password' => Hash::make($request->string('password')),
            ]);
        });

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'sukses',
            'message' => 'registered',
            'data' => [
                'user' => $user->load(['employee', 'currentStore']),
                'token' => $token,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('username', $request->string('username'))->first();

        if (!$user || !Hash::check($request->string('password'), $user->password)) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'invalid credentials',
                'data' => null,
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'sukses',
            'message' => 'logged in',
            'data' => [
                'user' => $user->load(['employee', 'currentStore']),
                'token' => $token,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $request->user()->load(['employee', 'currentStore']),
        ]);
    }

    public function stores(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $request->user()->accessibleStores()->where('is_active', true)->orderBy('store_name')->get(),
        ]);
    }

    public function updateCurrentStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
        ]);

        $hasAccess = $request->user()->accessibleStores()->where('stores.id', $validated['store_id'])->exists();

        if (!$hasAccess) {
            return response()->json(['status' => 'gagal', 'message' => 'tidak memiliki akses ke toko ini', 'data' => null], 403);
        }

        $request->user()->update(['current_store_id' => $validated['store_id']]);

        return response()->json([
            'status' => 'sukses',
            'message' => 'updated',
            'data' => $request->user()->fresh()->load(['employee', 'currentStore']),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'sukses',
            'message' => 'logged out',
            'data' => null,
        ]);
    }
}
