<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login and issue a Sanctum token (Mobile ONLY).
     */
    public function login(LoginRequest $request)
    {
        // 🔒 Enforce JSON (hindari HTML / redirect dari Laravel)
        if (! $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request type',
            ], 406);
        }

        $data = $request->validated();

        // 🔍 Ambil user
        $user = User::where('email', $data['email'])->first();

        // 🔒 Dummy hash (anti timing attack, TANPA bcrypt runtime)
        $dummyHash = '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';

        $passwordValid = $user
            ? Hash::check($data['password'], $user->password)
            : Hash::check($data['password'], $dummyHash);

        if (! $user || ! $passwordValid) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // 🔒 User inactive
        if (! $user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'User inactive',
            ], 403);
        }

        // 🔒 Block admin (mobile tidak boleh)
        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        /**
         * 🧠 DEVICE AWARE TOKEN STRATEGY
         * - Hindari token duplicate
         * - Memudahkan revoke per device
         */
        $deviceName = $request->input('device_name')
            ?? $request->userAgent()
            ?? 'mobile';

        // 🔥 OPTIONAL STRATEGY:
        // 👉 Single device (uncomment jika mau strict)
        // $user->tokens()->delete();

        // 👉 Per-device replace (RECOMMENDED)
        $user->tokens()
            ->where('name', $deviceName)
            ->delete();

        // 🔑 Create token
        $token = $user
            ->createToken($deviceName)
            ->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role?->value ?? null,
                ],
            ],
        ], 200);
    }

    /**
     * Logout (revoke current token only).
     */
    public function logout(Request $request)
    {
        // 🔒 Pastikan authenticated
        if (! $request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Get authenticated user (token-based).
     */
    public function me(Request $request)
    {
        // 🔒 JSON enforcement (debug penting)
        if (! $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request type',
            ], 406);
        }

        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role?->value ?? null,
            ],
        ], 200);
    }
}
