<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Return authenticated user's profile.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->value ?? $user->role,
            'profile' => [
                'phone' => $user->phone ?? null,
                'linkedin_url' => $user->linkedin_url ?? null,
                'graduation_year' => $user->graduation_year ?? null,
                'program_study' => $user->program_study ?? null,
            ],
        ], 200);
    }

    /**
     * Update authenticated user's profile (whitelisted fields only).
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validated();

        // Whitelist allowed updatable fields to be extra-safe.
        $allowed = [
            'name',
            'phone',
            'linkedin_url',
            'graduation_year',
            'program_study',
        ];

        $data = array_intersect_key($validated, array_flip($allowed));

        if (! empty($data)) {
            $user->fill($data);
            $user->save();
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => [
                'name' => $user->name,
                'phone' => $user->phone ?? null,
                'linkedin_url' => $user->linkedin_url ?? null,
                'graduation_year' => $user->graduation_year ?? null,
                'program_study' => $user->program_study ?? null,
            ],
        ], 200);
    }
}
