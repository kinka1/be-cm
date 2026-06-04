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
            $employeeId = DB::table('employees')->insertGetId([
                'full_name' => $request->string('full_name'),
                'email' => $request->string('email'),
                'join_date' => $request->date('join_date')->toDateString(),
                'role_id' => $request->integer('role_id'),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return User::create([
                'employee_id' => $employeeId,
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
                'user' => $user,
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
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'sukses',
            'message' => 'ok',
            'data' => $request->user(),
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
