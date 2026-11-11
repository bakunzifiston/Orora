<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleItemUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $farmId = $this->route('transaction')?->farm_id;

        return [
            'description' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'live_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'price_per_kg' => ['nullable', 'numeric', 'min:0'],
            'milk_storage_id' => $farmId
                ? ['nullable', Rule::exists('milk_storage', 'id')->where(fn ($q) => $q->where('farm_id', $farmId))]
                : ['nullable', 'exists:milk_storage,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('milk_storage_id') === '') {
            $this->merge(['milk_storage_id' => null]);
        }
    }
}
