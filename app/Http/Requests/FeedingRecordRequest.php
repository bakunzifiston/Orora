<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesFarmRelations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeedingRecordRequest extends FormRequest
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
            'feed_inventory_id' => [
                'required',
                Rule::exists('feed_inventories', 'id')->where(fn ($q) => $q->where('farm_id', $this->input('farm_id'))),
            ],
            'feeding_schedule_id' => [
                'nullable',
                Rule::exists('feeding_schedules', 'id')->where(fn ($q) => $q->where('farm_id', $this->input('farm_id'))),
            ],
            'livestock_id' => $this->optionalLivestockBelongsToFarm(),
            'animal_id' => array_merge($this->optionalAnimalBelongsToFarm(), ['prohibits:flock_id']),
            'flock_id' => array_merge($this->optionalFlockBelongsToFarm(), ['prohibits:animal_id']),
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'fed_on' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['livestock_id', 'animal_id', 'flock_id', 'feeding_schedule_id'] as $field) {
            if ($this->input($field) === '') {
                $merge[$field] = null;
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }
}
