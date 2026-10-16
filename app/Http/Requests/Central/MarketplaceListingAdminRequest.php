<?php

namespace App\Http\Requests\Central;

use App\Models\Animal;
use App\Models\Central\MarketplaceCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class MarketplaceListingAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'farm_id' => ['required', 'integer', 'exists:farms,id'],
            'animal_id' => ['nullable', 'integer', 'exists:animals,id'],
            'animal_species' => ['nullable', 'string', 'max:80'],
            'animal_sex' => ['nullable', 'string', 'max:40'],
            'category_id' => ['required', 'integer', 'exists:marketplace_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'breed' => ['nullable', 'string', 'max:120'],
            'age' => ['nullable', 'string', 'max:60'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit' => ['required', 'in:head,kg,liter,bag,ton'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_type' => ['required', 'in:fixed,negotiable,per_kg,per_liter'],
            'seller_name' => ['required', 'string', 'max:120'],
            'seller_phone' => ['required', 'string', 'max:40'],
            'seller_email' => ['nullable', 'email', 'max:255'],
            'seller_type' => ['required', 'in:individual,cooperative,company,abattoir'],
            'location_district' => ['required', 'string', 'max:120'],
            'location_sector' => ['nullable', 'string', 'max:120'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'keep_images' => ['nullable', 'array', 'max:5'],
            'keep_images.*' => ['string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('animal_id') && $this->filled('farm_id')) {
                $animal = Animal::query()
                    ->withoutGlobalScope('tenant')
                    ->find($this->input('animal_id'));

                if (! $animal || (int) $animal->farm_id !== (int) $this->input('farm_id')) {
                    $validator->errors()->add('animal_id', __('The selected animal does not belong to the selected farm.'));
                }
            }

            if ($this->filled('category_id')) {
                $category = MarketplaceCategory::query()->find($this->input('category_id'));
                $type = config('marketplace.shop.category_type_map.'.$category?->slug);

                if ($type !== 'animal') {
                    $validator->errors()->add('category_id', __('Select a live animal category.'));
                }
            }
        });
    }
}
