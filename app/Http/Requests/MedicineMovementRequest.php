<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicineMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('notes') === '') {
            $this->merge(['notes' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'movement_type' => ['required', Rule::in(config('modules.medicine_movement_types'))],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'moved_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
