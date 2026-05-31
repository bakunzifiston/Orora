<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contract_type' => ['required', Rule::in(array_keys(config('modules.employee_contract_types')))],
            'contract_start' => ['nullable', 'date'],
            'contract_end' => ['nullable', 'date', 'after_or_equal:contract_start'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:3'],
            'pay_frequency' => ['required', Rule::in(array_keys(config('modules.employee_pay_frequencies')))],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account' => ['nullable', 'string', 'max:100'],
            'mobile_money_number' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
