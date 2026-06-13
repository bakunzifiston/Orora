<?php

namespace App\Http\Requests\Marketplace;

use Illuminate\Foundation\Http\FormRequest;

class MarketplaceInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buyer_name' => ['required', 'string', 'max:120'],
            'buyer_phone' => ['required', 'string', 'max:40'],
            'buyer_email' => ['nullable', 'email', 'max:255'],
            'buyer_location' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:1000'],
        ];
    }
}
