<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $transaction = $this->route('transaction');

        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'amount_paid' => [
                'required',
                'numeric',
                'min:0.01',
                function (string $attribute, mixed $value, \Closure $fail) use ($transaction) {
                    if (! $transaction instanceof \App\Models\SaleTransaction) {
                        return;
                    }

                    $balance = $transaction->balanceDue();

                    if ($balance <= 0) {
                        $fail('This sale has no balance due.');

                        return;
                    }

                    if ((float) $value > $balance) {
                        $fail('Payment cannot exceed the balance due ('.number_format($balance, 0).' '.$transaction->currency.').');
                    }
                },
            ],
            'payment_method' => ['nullable', Rule::in(config('modules.expense_payment_methods'))],
            'payment_date' => ['required', 'date'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'received_by' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
