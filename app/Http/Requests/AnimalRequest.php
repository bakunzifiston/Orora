<?php

namespace App\Http\Requests;

use App\Models\Animal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnimalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'acquisition_type' => $this->filled('acquisition_type') ? $this->input('acquisition_type') : null,
            'production_status' => $this->filled('production_status') ? $this->input('production_status') : null,
            'current_condition' => $this->filled('current_condition') ? $this->input('current_condition') : null,
        ]);
    }

    public function rules(): array
    {
        $animal = $this->route('animal');

        return [
            'farm_id' => ['required', 'exists:farms,id'],
            'livestock_id' => ['required', 'exists:livestock,id'],
            'tag_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('animals', 'tag_number')
                    ->where(fn ($query) => $query->where('livestock_id', $this->input('livestock_id')))
                    ->ignore($animal?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(array_keys(config('modules.animal_genders')))],
            'photo' => ['nullable', 'image', 'max:2048'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'color_markings' => ['nullable', 'string', 'max:255'],
            'acquisition_type' => ['nullable', Rule::in(config('modules.acquisition_types'))],
            'acquisition_date' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:255'],
            'mother_tag' => ['nullable', 'string', 'max:100'],
            'father_tag' => ['nullable', 'string', 'max:100'],
            'health_status' => ['required', Rule::in(config('modules.health_statuses'))],
            'production_status' => ['nullable', Rule::in(config('modules.production_statuses'))],
            'lifecycle_status' => ['required', Rule::in(config('modules.lifecycle_statuses'))],
            'current_condition' => ['nullable', Rule::in(config('modules.current_conditions'))],
            'species' => ['nullable', 'string', 'max:100'],
            'breed' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function animalAttributes(): array
    {
        return $this->safe()->except(['photo']);
    }
}
