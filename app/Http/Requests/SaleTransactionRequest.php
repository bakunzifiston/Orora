<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $mode = $this->input('customer_mode', 'existing');

        if ($mode === 'new') {
            $this->merge(['customer_id' => null]);
        }

        if ($mode === 'none') {
            $this->merge([
                'customer_id' => null,
                'new_customer_display_name' => null,
            ]);
        }
    }

    public function rules(): array
    {
        $type = $this->input('sale_type', 'animal_sale');
        $multiBuyerMilk = $type === 'milk_sale' && $this->boolean('multi_buyer');
        $mode = $this->input('customer_mode', 'existing');

        $rules = [
            'farm_id' => ['required', 'exists:farms,id'],
            'sale_type' => ['required', Rule::in(config('modules.sale_types'))],
            'sale_date' => ['required', 'date'],
            'customer_mode' => ['required', Rule::in(['existing', 'new', 'none'])],
            'pricing_method' => ['required', Rule::in(array_keys(config('modules.sale_pricing_methods')))],
            'currency' => ['required', 'string', 'max:3'],
            'delivery_method' => ['nullable', Rule::in(config('modules.sale_delivery_methods'))],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'multi_buyer' => ['boolean'],
        ];

        if ($multiBuyerMilk) {
            $rules['customer_id'] = ['nullable', 'exists:customers,id'];
        } elseif ($mode === 'new') {
            $rules['new_customer_display_name'] = ['required', 'string', 'max:255'];
            $rules['new_customer_type'] = ['nullable', Rule::in(array_keys(config('modules.customer_types')))];
            $rules['new_customer_phone'] = ['nullable', 'string', 'max:50'];
            $rules['new_customer_email'] = ['nullable', 'email', 'max:255'];
            $rules['customer_id'] = ['nullable'];
        } elseif ($mode === 'existing') {
            $rules['customer_id'] = ['nullable', 'exists:customers,id'];
        } else {
            $rules['customer_id'] = ['nullable'];
        }

        return $rules;
    }

    public function headerAttributes(): array
    {
        return $this->safe()->except([
            'multi_buyer',
            'customer_mode',
            'new_customer_display_name',
            'new_customer_type',
            'new_customer_phone',
            'new_customer_email',
        ]);
    }

    public function isNewCustomerMode(): bool
    {
        return $this->input('customer_mode') === 'new';
    }
}
