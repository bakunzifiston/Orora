<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'extensions:csv,txt', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => __('Please choose a CSV file to import.'),
            'file.extensions' => __('The import file must be a CSV.'),
            'file.max' => __('The CSV file may not be greater than 2 MB.'),
        ];
    }
}
