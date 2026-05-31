<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MilkRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'abnormal_milk' => $this->boolean('abnormal_milk'),
        ]);
    }

    public function rules(): array
    {
        return [
            'animal_id' => ['required', 'exists:animals,id'],
            'yield_liters' => ['required', 'numeric', 'min:0.01'],
            'milking_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'lactation_stage' => ['nullable', Rule::in(config('modules.lactation_stages'))],
            'lactation_number' => ['nullable', 'integer', 'min:1'],
            'udder_condition' => ['nullable', Rule::in(config('modules.udder_conditions'))],
            'abnormal_milk' => ['boolean'],
            'abnormal_notes' => [
                Rule::requiredIf(fn () => $this->boolean('abnormal_milk')),
                'nullable',
                'string',
            ],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function recordAttributes(): array
    {
        return $this->safe()->only([
            'animal_id',
            'yield_liters',
            'milking_duration_minutes',
            'lactation_stage',
            'lactation_number',
            'udder_condition',
            'abnormal_milk',
            'abnormal_notes',
            'notes',
        ]);
    }
}
