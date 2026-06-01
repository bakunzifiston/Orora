<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesFarmRelations;
use App\Models\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FormRequest
{
    use ValidatesFarmRelations;
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['animal_id', 'livestock_id', 'expense_vendor_id'] as $field) {
            if ($this->input($field) === '') {
                $merge[$field] = null;
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        $category = ExpenseCategory::query()->find($this->input('expense_category_id'));
        $group = $category?->expense_group;

        return [
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'farm_id' => [
                Rule::requiredIf(fn () => $group !== 'general'),
                'nullable',
                'exists:farms,id',
            ],
            'animal_id' => $this->optionalAnimalBelongsToFarm(),
            'livestock_id' => $this->optionalLivestockBelongsToFarm(),
            'expense_vendor_id' => ['nullable', 'exists:expense_vendors,id'],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'max:3'],
            'payment_method' => ['nullable', Rule::in(config('modules.expense_payment_methods'))],
            'paid_by' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(config('modules.expense_statuses'))],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ];
    }
}
