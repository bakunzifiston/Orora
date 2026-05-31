<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OffspringRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $offspring = $this->route('offspring');
        $mother = $offspring?->motherAnimal;

        return [
            'tag_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('animals', 'tag_number')
                    ->where(fn ($query) => $query->where('livestock_id', $mother?->livestock_id)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(array_keys(config('modules.animal_genders')))],
            'breed' => ['nullable', 'string', 'max:255'],
        ];
    }
}
