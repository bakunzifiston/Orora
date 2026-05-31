<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MilkStorageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'farm_id' => ['required', 'exists:farms,id'],
            'container_name' => ['required', 'string', 'max:255'],
            'container_type' => ['required', Rule::in(config('modules.milk_storage_container_types'))],
            'capacity_liters' => ['required', 'numeric', 'min:1'],
            'storage_temperature' => ['nullable', 'numeric'],
            'storage_location' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(config('modules.milk_storage_statuses'))],
            'notes' => ['nullable', 'string'],
        ];
    }
}
