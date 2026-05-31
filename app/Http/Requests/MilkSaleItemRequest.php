<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MilkSaleItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'milk_storage_id' => ['nullable', 'exists:milk_storage,id'],
            'quantity_liters' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'line_total' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
