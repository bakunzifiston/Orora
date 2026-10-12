<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['category', 'category_other', 'active_ingredient', 'dosage_instructions', 'administration_method', 'administration_method_other', 'reorder_level', 'expiry_date', 'supplier', 'notes'] as $field) {
            if ($this->input($field) === '') {
                $merge[$field] = null;
            }
        }

        if (! $this->has('is_active')) {
            $merge['is_active'] = false;
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', Rule::in(config('modules.medicine_categories'))],
            'category_other' => [
                Rule::requiredIf(fn () => $this->input('category') === 'Other'),
                'nullable',
                'string',
                'max:255',
            ],
            'active_ingredient' => ['nullable', 'string', 'max:255'],
            'dosage_instructions' => ['nullable', 'string'],
            'administration_method' => ['nullable', Rule::in(config('modules.administration_methods'))],
            'administration_method_other' => [
                Rule::requiredIf(fn () => $this->input('administration_method') === 'Other'),
                'nullable',
                'string',
                'max:255',
            ],
            'unit' => ['required', Rule::in(config('modules.medicine_units'))],
            'quantity_on_hand' => ['required', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_other' => 'other category',
            'administration_method_other' => 'other administration method',
        ];
    }

    public function medicineAttributes(): array
    {
        $attributes = $this->safe()->except([
            'is_active',
            'category_other',
            'administration_method_other',
        ]);

        if (($attributes['category'] ?? null) === 'Other') {
            $attributes['category'] = $this->input('category_other');
        }

        if (($attributes['administration_method'] ?? null) === 'Other') {
            $attributes['administration_method'] = $this->input('administration_method_other');
        }

        return [
            ...$attributes,
            'is_active' => $this->boolean('is_active'),
        ];
    }
}
