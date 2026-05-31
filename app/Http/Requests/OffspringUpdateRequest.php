<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OffspringUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gender' => ['required', Rule::in(array_keys(config('modules.animal_genders')))],
            'birth_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'color_markings' => ['nullable', 'string', 'max:255'],
            'health_status_at_birth' => ['required', Rule::in(config('modules.offspring_health_at_birth'))],
            'notes' => ['nullable', 'string'],
        ];
    }
}
