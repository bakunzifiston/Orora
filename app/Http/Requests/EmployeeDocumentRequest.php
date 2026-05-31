<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', Rule::in(array_keys(config('modules.employee_document_types')))],
            'document_name' => ['required', 'string', 'max:255'],
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issued_date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
