<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $itemType = $this->input('item_type');

        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'item_type' => ['required', 'in:animal,meat_cut,milk'],
            'animal_id' => [
                $itemType === 'animal' ? 'required' : 'nullable',
                'exists:animals,id',
            ],
            'livestock_id' => ['nullable', 'exists:livestock,id'],
            'abattoir_return_id' => ['nullable', 'exists:abattoir_returns,id'],
            'milk_storage_id' => ['nullable', 'exists:milk_storage,id'],
            'description' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit' => ['required', 'string', 'max:20'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'live_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'carcass_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'price_per_kg' => ['nullable', 'numeric', 'min:0'],
            'total_price' => ['nullable', 'numeric', 'min:0'],
            'animal_condition' => ['nullable', 'string', 'max:50'],
            'certificate_verified' => ['boolean'],
            'permit_verified' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'certificate_verified' => $this->boolean('certificate_verified'),
            'permit_verified' => $this->boolean('permit_verified'),
        ]);
    }
}
