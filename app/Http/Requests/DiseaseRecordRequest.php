<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\LinkedExpenseRules;
use App\Http\Requests\Concerns\ValidatesFarmRelations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiseaseRecordRequest extends FormRequest
{
    use LinkedExpenseRules;
    use ValidatesFarmRelations;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'quarantine_required' => $this->boolean('quarantine_required'),
        ]);

        $this->prepareLinkedExpenseValidation();
    }

    public function rules(): array
    {
        return array_merge([
            'farm_id' => ['required', 'exists:farms,id'],
            'livestock_id' => ['required', $this->livestockBelongsToFarm()],
            'animal_id' => ['required', $this->animalBelongsToLivestock()],
            'disease_name' => ['required', 'string', 'max:255'],
            'diagnosis_date' => ['required', 'date', 'before_or_equal:today'],
            'severity_level' => ['required', Rule::in(config('modules.disease_severity_levels'))],
            'recovery_status' => ['required', Rule::in(config('modules.disease_recovery_statuses'))],
            'contagious_status' => ['required', Rule::in(config('modules.disease_contagious_statuses'))],
            'quarantine_required' => ['boolean'],
            'symptoms' => ['nullable', 'string'],
            'veterinarian_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:4096', 'mimes:pdf,jpg,jpeg,png'],
        ], $this->linkedExpenseRules());
    }

    public function diseaseRecordAttributes(): array
    {
        return $this->safe()->except(array_merge(
            ['attachment'],
            $this->linkedExpenseAttributeKeys(),
        ));
    }
}
