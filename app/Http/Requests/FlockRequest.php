<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesFarmRelations;
use App\Models\Farm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FlockRequest extends FormRequest
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
            'livestock_id' => $this->optionalLivestockBelongsToFarm(),
            'name' => ['required', 'string', 'max:255'],
            'production_type' => ['required', Rule::in(array_keys(config('modules.flock_production_types', [])))],
            'breed' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', Rule::in(array_keys(config('modules.flock_sources', [])))],
            'placed_on' => ['required', 'date'],
            'placed_count' => ['required', 'integer', 'min:1'],
            'current_count' => ['nullable', 'integer', 'min:0'],
            'male_count' => ['nullable', 'integer', 'min:0'],
            'female_count' => ['nullable', 'integer', 'min:0'],
            'house_or_pen' => ['nullable', 'string', 'max:255'],
            'expected_end_on' => ['nullable', 'date', 'after_or_equal:placed_on'],
            'lifecycle_status' => ['required', Rule::in(config('modules.lifecycle_statuses'))],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $farm = Farm::query()->find($this->input('farm_id'));

            if ($farm && ! $farm->isPoultry()) {
                $validator->errors()->add('farm_id', 'Flocks can only be registered on poultry farms.');
            }
        });
    }

    public function flockAttributes(): array
    {
        $attributes = $this->safe()->only([
            'farm_id',
            'livestock_id',
            'name',
            'production_type',
            'breed',
            'source',
            'placed_on',
            'placed_count',
            'male_count',
            'female_count',
            'house_or_pen',
            'expected_end_on',
            'lifecycle_status',
            'notes',
        ]);

        if (! $this->filled('current_count')) {
            unset($attributes['current_count']);
        } else {
            $attributes['current_count'] = (int) $this->input('current_count');
        }

        return $attributes;
    }

    protected function prepareForValidation(): void
    {
        $nullable = [
            'livestock_id',
            'breed',
            'source',
            'current_count',
            'male_count',
            'female_count',
            'house_or_pen',
            'expected_end_on',
            'notes',
        ];

        $merge = [];

        foreach ($nullable as $key) {
            if ($this->input($key) === '') {
                $merge[$key] = null;
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }
}
