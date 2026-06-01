<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\LinkedExpenseRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PregnancyCheckRequest extends FormRequest
{
    use LinkedExpenseRules;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareLinkedExpenseValidation();

        if ($this->input('clinic_name') === '') {
            $this->merge(['clinic_name' => null]);
        }
    }

    public function rules(): array
    {
        return array_merge([
            'breeding_record_id' => ['required', 'exists:breeding_records,id'],
            'animal_id' => ['required', 'exists:animals,id'],
            'check_date' => ['required', 'date', 'before_or_equal:today'],
            'check_method' => ['required', Rule::in(config('modules.pregnancy_check_methods'))],
            'result' => ['required', Rule::in(config('modules.pregnancy_check_results'))],
            'pregnancy_age_days' => ['nullable', 'integer', 'min:0'],
            'expected_calving_date' => ['nullable', 'date'],
            'checked_by' => ['required', 'string', 'max:255'],
            'clinic_name' => ['nullable', 'string', 'max:255'],
            'next_check_date' => [
                Rule::requiredIf(fn () => $this->input('result') === 'inconclusive'),
                'nullable',
                'date',
                'after:check_date',
            ],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ], $this->linkedExpenseRules());
    }

    public function checkAttributes(): array
    {
        return $this->safe()->except(array_merge(['attachment'], $this->linkedExpenseAttributeKeys()));
    }
}
