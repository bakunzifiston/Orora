<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MilkSessionCompleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination_storage_id' => ['nullable', 'exists:milk_storage,id'],
        ];
    }
}
