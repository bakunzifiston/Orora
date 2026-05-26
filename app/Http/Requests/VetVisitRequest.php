<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VetVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('treatment_method') === '') {
            $this->merge(['treatment_method' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'animal_id' => ['required', 'exists:animals,id'],
            'disease_name' => ['required', 'string', 'max:255'],
            'medicine_name' => ['required', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:100'],
            'treatment_method' => ['nullable', Rule::in(config('modules.treatment_methods'))],
            'treatment_method_other' => [
                Rule::requiredIf(fn () => $this->input('treatment_method') === 'Other'),
                'nullable',
                'string',
                'max:255',
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(config('modules.treatment_statuses'))],
            'veterinarian_name' => ['nullable', 'string', 'max:255'],
            'symptoms' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ];
    }

    public function attributes(): array
    {
        return [
            'treatment_method_other' => 'other treatment method',
        ];
    }

    public function vetVisitAttributes(): array
    {
        $attributes = $this->safe()->except(['attachment', 'treatment_method_other']);

        if (($attributes['treatment_method'] ?? null) === 'Other') {
            $attributes['treatment_method'] = $this->input('treatment_method_other');
        }

        return $attributes;
    }
}
