<?php

namespace App\Http\Requests\Api\V1\Profile;

use Illuminate\Foundation\Http\FormRequest;

class StoreEducationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'institution' => 'required|string|max:150',
            'degree' => 'nullable|string|max:100',
            'field_of_study' => 'nullable|string|max:150',
            'start_year' => 'required|integer',
            'end_year' => 'nullable|integer|gte:start_year',
        ];
    }
}
