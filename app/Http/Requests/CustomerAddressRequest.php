<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_type' => ['required', Rule::in(array_keys(config('modules.customer_address_types')))],
            'address_label' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'sector' => ['nullable', 'string', 'max:100'],
            'cell' => ['nullable', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'street_address' => ['nullable', 'string'],
            'gps_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_default' => $this->boolean('is_default')]);
    }
}
