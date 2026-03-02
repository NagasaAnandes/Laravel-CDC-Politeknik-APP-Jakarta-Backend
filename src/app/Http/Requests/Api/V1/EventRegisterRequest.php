<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\UserRole;

class EventRegisterRequest extends FormRequest
{
    /**
     * Authorization Layer
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        // User must be active
        if (! $user->isActive()) {
            return false;
        }

        // Only student & alumni allowed
        return in_array(
            $user->role,
            [UserRole::STUDENT, UserRole::ALUMNI],
            true
        );
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        return [
            // No body payload required.
            // Route param handles event id.
        ];
    }

    /**
     * Custom response if unauthorized
     */
    protected function failedAuthorization()
    {
        abort(403, 'Unauthorized to register for this event.');
    }
}
