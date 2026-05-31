<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerCreditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'last_reviewed_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
