<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\LinkedExpenseRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeedInventoryMovementRequest extends FormRequest
{
    use LinkedExpenseRules;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareLinkedExpenseValidation();
    }

    public function rules(): array
    {
        return array_merge([
            'movement_type' => ['required', Rule::in(['purchase', 'adjustment_in', 'adjustment_out'])],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
            'moved_at' => ['nullable', 'date'],
        ], $this->linkedExpenseRules());
    }
}
