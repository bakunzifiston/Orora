<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesFarmRelations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleItemRequest extends FormRequest
{
    use ValidatesFarmRelations;
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $itemType = $this->input('item_type');
        $transaction = $this->route('transaction');
        $farmId = $transaction?->farm_id;

        $animalRule = $farmId
            ? $this->animalBelongsToFarmId($farmId)
            : Rule::exists('animals', 'id');

        $flockRule = $farmId
            ? $this->flockBelongsToFarmId($farmId)
            : Rule::exists('flocks', 'id');

        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'item_type' => ['required', 'in:animal,meat_cut,milk,egg,flock_birds'],
            'animal_id' => [
                $itemType === 'animal' ? 'required' : 'nullable',
                'prohibits:flock_id',
                $animalRule,
            ],
            'flock_id' => [
                Rule::requiredIf(fn () => $itemType === 'flock_birds'),
                'nullable',
                $flockRule,
            ],
            'livestock_id' => $farmId
                ? ['nullable', $this->livestockBelongsToFarmId($farmId)]
                : ['nullable', 'exists:livestock,id'],
            'abattoir_return_id' => ['nullable', 'exists:abattoir_returns,id'],
            'milk_storage_id' => $farmId
                ? ['nullable', Rule::exists('milk_storage', 'id')->where(fn ($q) => $q->where('farm_id', $farmId))]
                : ['nullable', 'exists:milk_storage,id'],
            'description' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit' => ['required', 'string', 'max:20'],
            'unit_price' => [
                Rule::requiredIf(fn () => in_array($itemType, ['milk', 'meat_cut', 'egg', 'flock_birds'], true)),
                'nullable',
                'numeric',
                'min:0.01',
            ],
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

    public function messages(): array
    {
        return [
            'unit_price.required' => 'Unit price is required for milk, meat, egg, and live-bird items.',
            'unit_price.min' => 'Unit price must be greater than zero.',
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['animal_id', 'flock_id', 'livestock_id', 'milk_storage_id', 'abattoir_return_id'] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }

        $this->merge([
            'certificate_verified' => $this->boolean('certificate_verified'),
            'permit_verified' => $this->boolean('permit_verified'),
        ]);
    }
}
