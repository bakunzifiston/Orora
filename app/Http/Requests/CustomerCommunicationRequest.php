<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerCommunicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'communication_type' => ['required', Rule::in(array_keys(config('modules.customer_communication_types')))],
            'direction' => ['nullable', Rule::in(array_keys(config('modules.customer_communication_directions')))],
            'subject' => ['nullable', 'string', 'max:255'],
            'summary' => ['required', 'string'],
            'communication_date' => ['required', 'date'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'follow_up_required' => ['boolean'],
            'follow_up_date' => ['nullable', 'date'],
            'follow_up_notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['follow_up_required' => $this->boolean('follow_up_required')]);
    }
}
