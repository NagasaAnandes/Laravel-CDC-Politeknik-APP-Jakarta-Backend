<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login and issue a Sanctum token.
     */
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        $passwordValid = $user
            ? Hash::check($data['password'], $user->password)
            : Hash::check($data['password'], bcrypt('dummy-password'));

        if (! $user || ! $passwordValid) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (! $user->isActive()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($user->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $token = $user
            ->createToken('api-token')
            ->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->value ?? null,
            ],
        ]);
    }
    /**
     * Revoke current access token.
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    /**
     * Return authenticated user info.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->value ?? null,
        ], 200);
    }
}
