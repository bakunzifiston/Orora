<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MilkSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'farm_id' => ['required', 'exists:farms,id'],
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_contact' => ['nullable', 'string', 'max:255'],
            'sold_on' => ['required', 'date'],
            'currency' => ['required', 'string', 'max:10'],
            'notes' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.milk_storage_id' => ['nullable', 'exists:milk_storage,id'],
            'items.*.quantity_liters' => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.line_total' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    public function saleAttributes(): array
    {
        return $this->safe()->only([
            'farm_id',
            'buyer_name',
            'buyer_contact',
            'sold_on',
            'currency',
            'notes',
        ]);
    }
}
