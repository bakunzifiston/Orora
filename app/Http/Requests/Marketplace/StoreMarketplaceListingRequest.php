<?php

namespace App\Http\Requests\Marketplace;

use App\Models\Animal;
use App\Models\Farm;
use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMarketplaceListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return $this->listingRules();
    }

    protected function listingRules(): array
    {
        return [
            'category_id' => ['required', 'exists:marketplace_categories,id'],
            'listing_type' => ['required', Rule::in(array_keys(config('marketplace.shop.listing_types', [])))],
            'farm_id' => ['nullable', 'integer', 'exists:farms,id'],
            'animal_id' => ['nullable', 'integer', 'exists:animals,id'],
            'animal_species' => ['nullable', 'string', 'max:80'],
            'animal_sex' => ['nullable', 'string', 'max:40'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'breed' => ['nullable', 'string', 'max:120'],
            'age' => ['nullable', 'string', 'max:60'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit' => ['required', Rule::in(array_keys(config('marketplace.shop.units', [])))],
            'price' => ['required', 'numeric', 'min:0'],
            'price_type' => ['required', Rule::in(array_keys(config('marketplace.shop.price_types', [])))],
            'seller_name' => ['required', 'string', 'max:120'],
            'seller_phone' => ['required', 'string', 'max:40'],
            'seller_email' => ['nullable', 'email', 'max:255'],
            'seller_type' => ['required', Rule::in(array_keys(config('marketplace.shop.seller_types', [])))],
            'location_district' => ['required', 'string', 'max:120'],
            'location_sector' => ['nullable', 'string', 'max:120'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $tenantId = TenantContext::id() ?? auth()->user()?->tenant_id;

            if (! $tenantId) {
                $validator->errors()->add('farm_id', __('Your workspace could not be identified.'));
                return;
            }

            if ($this->filled('farm_id') && ! Farm::query()->whereKey($this->input('farm_id'))->where('tenant_id', $tenantId)->exists()) {
                $validator->errors()->add('farm_id', __('Select a farm in your workspace.'));
            }

            if ($this->filled('animal_id')) {
                $animal = Animal::query()->with('farm')->find($this->input('animal_id'));

                if (! $animal || $animal->farm?->tenant_id !== $tenantId) {
                    $validator->errors()->add('animal_id', __('Select an animal in your workspace.'));
                } elseif ($this->filled('farm_id') && (int) $animal->farm_id !== (int) $this->input('farm_id')) {
                    $validator->errors()->add('animal_id', __('The selected animal does not belong to the selected farm.'));
                }

                $category = \App\Models\Central\MarketplaceCategory::query()->find($this->input('category_id'));
                if (config('marketplace.shop.category_type_map.'.$category?->slug) !== 'animal') {
                    $validator->errors()->add('animal_id', __('Link an animal only to a live animal listing.'));
                }
            }
        });
    }
}
