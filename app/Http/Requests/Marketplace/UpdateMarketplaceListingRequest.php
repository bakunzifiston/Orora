<?php

namespace App\Http\Requests\Marketplace;

class UpdateMarketplaceListingRequest extends StoreMarketplaceListingRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['images'] = ['nullable', 'array', 'max:5'];
        $rules['keep_images'] = ['nullable', 'array'];
        $rules['keep_images.*'] = ['string', 'max:500'];

        return $rules;
    }
}
