<?php

namespace App\Http\Requests\Marketplace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
}
