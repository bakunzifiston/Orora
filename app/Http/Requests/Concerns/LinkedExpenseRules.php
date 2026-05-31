<?php

namespace App\Http\Requests\Concerns;

trait LinkedExpenseRules
{
    protected function linkedExpenseRules(): array
    {
        return [
            'log_expense' => ['boolean'],
            'expense_amount' => ['required_if:log_expense,1', 'nullable', 'numeric', 'min:0.01'],
            'expense_currency' => ['nullable', 'string', 'max:3'],
            'expense_vendor_id' => ['nullable', 'exists:expense_vendors,id'],
            'expense_vendor_name' => ['nullable', 'string', 'max:255'],
            'expense_payment_method' => ['nullable', 'string', 'max:50'],
            'expense_paid_by' => ['nullable', 'string', 'max:255'],
            'expense_notes' => ['nullable', 'string'],
            'expense_attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }

    protected function prepareLinkedExpenseValidation(): void
    {
        $this->merge(['log_expense' => $this->boolean('log_expense')]);
    }
}
