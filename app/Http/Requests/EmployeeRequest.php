<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'display_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(config('modules.employee_statuses'))],
            'employment_type' => ['required', Rule::in(array_keys(config('modules.employee_employment_types')))],
            'job_role' => ['required', Rule::in(array_keys(config('modules.employee_job_roles')))],
            'primary_farm_id' => ['nullable', 'exists:farms,id'],
            'hire_date' => ['nullable', 'date'],
            'termination_date' => ['nullable', 'date', 'after_or_equal:hire_date'],
            'notes' => ['nullable', 'string'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(array_keys(config('modules.customer_genders')))],
            'marital_status' => ['nullable', Rule::in(array_keys(config('modules.employee_marital_statuses')))],
            'nationality' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'education_level' => ['nullable', 'string', 'max:100'],
            'skills' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_relationship' => ['nullable', 'string', 'max:100'],
            'emergency_phone' => ['nullable', 'string', 'max:50'],
            'emergency_email' => ['nullable', 'email', 'max:255'],
            'contract_type' => ['nullable', Rule::in(array_keys(config('modules.employee_contract_types')))],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'pay_frequency' => ['nullable', Rule::in(array_keys(config('modules.employee_pay_frequencies')))],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function employeeAttributes(): array
    {
        return $this->safe()->only([
            'display_name',
            'status',
            'employment_type',
            'job_role',
            'primary_farm_id',
            'hire_date',
            'termination_date',
            'notes',
        ]);
    }

    public function profileAttributes(): array
    {
        return $this->safe()->only([
            'first_name',
            'last_name',
            'national_id',
            'passport_number',
            'date_of_birth',
            'gender',
            'marital_status',
            'nationality',
            'phone',
            'email',
            'education_level',
            'skills',
        ]);
    }

    public function emergencyContactAttributes(): ?array
    {
        if (! $this->filled('emergency_contact_name') && ! $this->filled('emergency_phone')) {
            return null;
        }

        return [
            'contact_name' => $this->input('emergency_contact_name'),
            'relationship' => $this->input('emergency_relationship'),
            'phone' => $this->input('emergency_phone'),
            'email' => $this->input('emergency_email'),
        ];
    }

    public function payrollAttributes(): array
    {
        return array_filter($this->safe()->only([
            'contract_type',
            'base_salary',
            'currency',
            'pay_frequency',
            'payment_method',
        ]), fn ($value) => $value !== null && $value !== '');
    }
}
