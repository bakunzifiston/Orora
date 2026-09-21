<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnimalImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'extensions:csv,txt,xlsx,xls', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => __('Please choose a CSV or Excel file to import.'),
            'file.extensions' => __('The import file must be a CSV (.csv) or Excel (.xlsx) file.'),
            'file.max' => __('The import file may not be greater than 5 MB.'),
        ];
    }
}
