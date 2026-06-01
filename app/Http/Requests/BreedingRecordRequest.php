<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\LinkedExpenseRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BreedingRecordRequest extends FormRequest
{
    use LinkedExpenseRules;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareLinkedExpenseValidation();

        foreach (['male_animal_id', 'heat_detection_method', 'technician_name', 'semen_batch_number', 'semen_straw_code', 'semen_source', 'external_sire_name', 'external_sire_breed', 'external_sire_code'] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }
    }

    public function rules(): array
    {
        return array_merge([
            'farm_id' => ['required', 'exists:farms,id'],
            'female_animal_id' => ['required', 'exists:animals,id'],
            'male_animal_id' => ['nullable', 'exists:animals,id'],
            'external_sire_name' => ['nullable', 'string', 'max:255'],
            'external_sire_breed' => ['nullable', 'string', 'max:255'],
            'external_sire_code' => ['nullable', 'string', 'max:100'],
            'breeding_date' => ['required', 'date', 'before_or_equal:today'],
            'breeding_type' => ['required', Rule::in(config('modules.breeding_types'))],
            'animal_type' => ['required', Rule::in(config('modules.breeding_animal_types'))],
            'heat_detection_method' => ['nullable', Rule::in(config('modules.heat_detection_methods'))],
            'heat_detected_date' => ['nullable', 'date', 'before_or_equal:today'],
            'technician_name' => ['nullable', 'string', 'max:255'],
            'semen_batch_number' => ['nullable', 'string', 'max:100'],
            'semen_straw_code' => ['nullable', 'string', 'max:100'],
            'semen_source' => ['nullable', 'string', 'max:255'],
            'gestation_period_days' => ['nullable', 'integer', 'min:1', 'max:400'],
            'notes' => ['nullable', 'string'],
        ], $this->linkedExpenseRules());
    }

    public function recordAttributes(): array
    {
        return $this->safe()->only([
            'farm_id',
            'female_animal_id',
            'male_animal_id',
            'external_sire_name',
            'external_sire_breed',
            'external_sire_code',
            'breeding_date',
            'breeding_type',
            'animal_type',
            'heat_detection_method',
            'heat_detected_date',
            'technician_name',
            'semen_batch_number',
            'semen_straw_code',
            'semen_source',
            'gestation_period_days',
            'notes',
        ]);
    }
}
