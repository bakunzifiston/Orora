<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\LinkedExpenseRules;
use App\Models\BreedingRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BirthRecordRequest extends FormRequest
{
    use LinkedExpenseRules;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareLinkedExpenseValidation();

        foreach (['assisted_by', 'veterinarian_name'] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }
    }

    public function rules(): array
    {
        return array_merge([
            'breeding_record_id' => ['required', 'exists:breeding_records,id'],
            'mother_animal_id' => [
                'required',
                'exists:animals,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $breeding = BreedingRecord::query()->find($this->input('breeding_record_id'));
                    if ($breeding && (int) $value !== (int) $breeding->female_animal_id) {
                        $fail('The mother must match the female on the breeding record.');
                    }
                },
            ],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'birth_type' => ['required', Rule::in(config('modules.birth_types'))],
            'total_offspring' => ['required', 'integer', 'min:1'],
            'alive_offspring' => ['required', 'integer', 'min:0'],
            'stillborn_offspring' => ['nullable', 'integer', 'min:0'],
            'birth_difficulty' => ['required', Rule::in(config('modules.birth_difficulties'))],
            'birth_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'assisted_by' => ['nullable', 'string', 'max:255'],
            'veterinarian_name' => ['nullable', 'string', 'max:255'],
            'mother_condition_after' => ['required', Rule::in(config('modules.mother_conditions_after_birth'))],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ], $this->linkedExpenseRules());
    }

    public function birthAttributes(): array
    {
        $attrs = $this->safe()->except(array_merge(['attachment'], $this->linkedExpenseAttributeKeys()));
        $attrs['stillborn_offspring'] = $attrs['stillborn_offspring'] ?? 0;

        return $attrs;
    }
}
