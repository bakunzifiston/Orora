<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesFarmRelations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeedingScheduleRequest extends FormRequest
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
            'feed_type_id' => ['required', 'exists:feed_types,id'],
            'feed_inventory_id' => [
                'nullable',
                Rule::exists('feed_inventories', 'id')->where(fn ($q) => $q->where('farm_id', $this->input('farm_id'))),
            ],
            'livestock_id' => $this->optionalLivestockBelongsToFarm(),
            'animal_id' => $this->optionalAnimalBelongsToFarm(),
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['required', Rule::in(config('modules.feed_units'))],
            'frequency' => ['required', Rule::in(config('modules.schedule_frequencies'))],
            'scheduled_time' => ['nullable', 'date_format:H:i'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'next_due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(config('modules.schedule_statuses'))],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['feed_inventory_id', 'livestock_id', 'animal_id'] as $field) {
            if ($this->input($field) === '') {
                $merge[$field] = null;
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }
}
