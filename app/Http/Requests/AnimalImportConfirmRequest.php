<?php

namespace App\Http\Requests;

use App\Services\Import\AnimalCsvImporter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnimalImportConfirmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'duplicate_action' => [
                'required',
                Rule::in([
                    AnimalCsvImporter::ACTION_KEEP,
                    AnimalCsvImporter::ACTION_REPLACE,
                    'cancel',
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'duplicate_action.required' => __('Choose what to do with existing animals.'),
            'duplicate_action.in' => __('Choose a valid import action.'),
        ];
    }
}
