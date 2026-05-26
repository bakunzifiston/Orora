<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VaccinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->input('vaccine_type') === '') {
            $merge['vaccine_type'] = null;
        }

        if ($this->input('administration_method') === '') {
            $merge['administration_method'] = null;
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'animal_id' => ['required', 'exists:animals,id'],
            'vaccine_name' => ['required', 'string', 'max:255'],
            'vaccine_type' => ['nullable', Rule::in(config('modules.vaccine_types'))],
            'vaccine_type_other' => [
                Rule::requiredIf(fn () => $this->input('vaccine_type') === 'Other'),
                'nullable',
                'string',
                'max:255',
            ],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'batch_number' => ['nullable', 'string', 'max:100'],
            'dosage' => ['nullable', 'string', 'max:100'],
            'administration_method' => ['nullable', Rule::in(config('modules.administration_methods'))],
            'administration_method_other' => [
                Rule::requiredIf(fn () => $this->input('administration_method') === 'Other'),
                'nullable',
                'string',
                'max:255',
            ],
            'vaccination_date' => ['required', 'date'],
            'next_due_date' => ['nullable', 'date', 'after_or_equal:vaccination_date'],
            'status' => ['required', Rule::in(config('modules.vaccination_statuses'))],
            'veterinarian_name' => ['nullable', 'string', 'max:255'],
            'veterinary_clinic' => ['nullable', 'string', 'max:255'],
            'administered_by' => ['nullable', 'string', 'max:255'],
            'side_effects' => ['nullable', 'string'],
            'reaction_notes' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ];
    }

    public function attributes(): array
    {
        return [
            'vaccine_type_other' => 'other vaccine type',
            'administration_method_other' => 'other administration method',
        ];
    }

    public function vaccinationAttributes(): array
    {
        $attributes = $this->safe()->except(['attachment', 'vaccine_type_other', 'administration_method_other']);

        if (($attributes['vaccine_type'] ?? null) === 'Other') {
            $attributes['vaccine_type'] = $this->input('vaccine_type_other');
        }

        if (($attributes['administration_method'] ?? null) === 'Other') {
            $attributes['administration_method'] = $this->input('administration_method_other');
        }

        return $attributes;
    }
}
