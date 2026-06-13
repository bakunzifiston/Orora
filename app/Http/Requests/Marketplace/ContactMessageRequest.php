<?php

namespace App\Http\Requests\Marketplace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', 'string', 'max:200'],
            'inquiry_type' => ['required', 'string', Rule::in(array_keys(config('marketplace.contact.inquiry_types', [])))],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }
}
