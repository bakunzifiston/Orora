<?php

namespace App\Http\Requests;

use App\Models\Animal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MilkRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('livestock_id') === '') {
            $this->merge(['livestock_id' => null]);
        }

        if ($this->input('session') === '') {
            $this->merge(['session' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'animal_id' => ['required', 'exists:animals,id'],
            'livestock_id' => ['nullable', 'exists:livestock,id'],
            'recorded_on' => ['required', 'date'],
            'session' => ['nullable', Rule::in(config('modules.milk_sessions'))],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['required', 'string', 'max:10'],
            'fat_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'quality_grade' => ['nullable', Rule::in(config('modules.milk_quality_grades'))],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function milkRecordAttributes(): array
    {
        $animal = Animal::query()->findOrFail($this->input('animal_id'));

        return array_merge($this->safe()->only([
            'animal_id',
            'livestock_id',
            'recorded_on',
            'session',
            'quantity',
            'unit',
            'fat_percentage',
            'quality_grade',
            'notes',
        ]), [
            'farm_id' => $animal->farm_id,
        ]);
    }
}
