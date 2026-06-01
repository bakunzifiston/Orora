<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesFarmRelations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HealthRecordRequest extends FormRequest
{
    use ValidatesFarmRelations;
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'farm_id' => ['required', 'exists:farms,id'],
            'animal_id' => ['required', $this->animalBelongsToFarm()],
            'record_type' => ['required', Rule::in(config('modules.health_record_types'))],
            'recorded_on' => ['required', 'date'],
            'health_status' => ['required', Rule::in(config('modules.health_statuses'))],
            'title' => ['nullable', 'string', 'max:255'],
            'treatment' => ['nullable', 'string', 'max:255'],
            'medication' => ['nullable', 'string', 'max:255'],
            'veterinarian' => ['nullable', 'string', 'max:255'],
            'next_follow_up' => ['nullable', 'date', 'after_or_equal:recorded_on'],
            'notes' => ['nullable', 'string'],
            'return_section' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function healthRecordAttributes(): array
    {
        return $this->safe()->except(['return_section']);
    }
}
