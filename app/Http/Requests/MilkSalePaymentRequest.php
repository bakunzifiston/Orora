<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MilkSalePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', Rule::in(config('modules.expense_payment_methods'))],
            'paid_on' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
