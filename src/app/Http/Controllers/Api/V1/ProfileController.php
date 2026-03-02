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

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->value,
            'profile' => [
                'phone' => $user->phone,
                'linkedin_url' => $user->linkedin_url,
                'graduation_year' => $user->graduation_year,
                'program_study' => $user->program_study,
            ],
        ]);
    }

    /**
     * Update authenticated user's profile (whitelisted fields only).
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();

        $allowed = [
            'name',
            'phone',
            'linkedin_url',
            'graduation_year',
            'program_study',
        ];

        $user->fill(
            array_intersect_key(
                $request->validated(),
                array_flip($allowed)
            )
        );

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'linkedin_url' => $user->linkedin_url,
                'graduation_year' => $user->graduation_year,
                'program_study' => $user->program_study,
            ],
        ]);
    }
}
