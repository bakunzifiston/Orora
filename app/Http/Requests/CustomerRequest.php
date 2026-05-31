<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('customer_type', 'individual');

        $rules = [
            'customer_type' => ['required', Rule::in(array_keys(config('modules.customer_types')))],
            'display_name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(config('modules.customer_statuses'))],
            'trust_level' => ['required', Rule::in(array_keys(config('modules.customer_trust_levels')))],
            'preferred_payment_method' => ['nullable', Rule::in(config('modules.expense_payment_methods'))],
            'currency' => ['required', 'string', 'max:3'],
            'notes' => ['nullable', 'string'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_role' => ['nullable', 'string', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ];

        if ($type === 'individual') {
            $rules += [
                'first_name' => ['nullable', 'string', 'max:100'],
                'last_name' => ['nullable', 'string', 'max:100'],
                'national_id' => ['nullable', 'string', 'max:50'],
                'date_of_birth' => ['nullable', 'date'],
                'gender' => ['nullable', Rule::in(array_keys(config('modules.customer_genders')))],
            ];
        } else {
            $rules += [
                'organization_name' => ['nullable', 'string', 'max:255'],
                'registration_number' => ['nullable', 'string', 'max:100'],
                'tax_id' => ['nullable', 'string', 'max:100'],
                'license_number' => ['nullable', 'string', 'max:100'],
                'license_expiry_date' => ['nullable', 'date'],
                'website' => ['nullable', 'string', 'max:255'],
                'industry' => ['nullable', 'string', 'max:100'],
                'number_of_employees' => ['nullable', 'integer', 'min:0'],
                'established_date' => ['nullable', 'date'],
            ];
        }

        return $rules;
    }

    public function customerAttributes(): array
    {
        return $this->safe()->only([
            'customer_type',
            'display_name',
            'status',
            'trust_level',
            'preferred_payment_method',
            'currency',
            'notes',
        ]);
    }

    public function profileAttributes(): array
    {
        $type = $this->input('customer_type', 'individual');

        if ($type === 'individual') {
            return $this->safe()->only(['first_name', 'last_name', 'national_id', 'date_of_birth', 'gender']);
        }

        return $this->safe()->only([
            'organization_name',
            'registration_number',
            'tax_id',
            'license_number',
            'license_expiry_date',
            'website',
            'industry',
            'number_of_employees',
            'established_date',
        ]);
    }

    public function primaryContactAttributes(): ?array
    {
        if (! $this->filled('contact_name') && ! $this->filled('contact_phone') && ! $this->filled('contact_email')) {
            return null;
        }

        return [
            'contact_name' => $this->input('contact_name') ?: $this->input('display_name'),
            'role' => $this->input('contact_role'),
            'phone' => $this->input('contact_phone'),
            'email' => $this->input('contact_email'),
        ];
    }
}
