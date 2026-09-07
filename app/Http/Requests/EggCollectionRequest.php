<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesFarmRelations;
use App\Models\Farm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EggCollectionRequest extends FormRequest
{
    use ValidatesFarmRelations;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ignore = $this->route('eggCollection')?->id;

        return [
            'farm_id' => ['required', 'exists:farms,id'],
            'flock_id' => [
                'required',
                Rule::exists('flocks', 'id')->where(fn ($q) => $q->where('farm_id', $this->input('farm_id'))),
            ],
            'collected_on' => ['required', 'date'],
            'shift' => ['nullable', Rule::in(array_keys(config('modules.egg_collection_shifts', [])))],
            'eggs_count' => ['required', 'integer', 'min:0'],
            'cracked_count' => ['nullable', 'integer', 'min:0'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'collected_by' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $farm = Farm::query()->find($this->input('farm_id'));

            if ($farm && ! $farm->isPoultry()) {
                $validator->errors()->add('farm_id', 'Egg collections can only be recorded on poultry farms.');
            }

            $cracked = (int) $this->input('cracked_count', 0);
            $eggs = (int) $this->input('eggs_count', 0);

            if ($cracked > $eggs) {
                $validator->errors()->add('cracked_count', 'Cracked eggs cannot exceed the total collected.');
            }
        });
    }

    public function collectionAttributes(): array
    {
        return [
            'farm_id' => (int) $this->input('farm_id'),
            'flock_id' => (int) $this->input('flock_id'),
            'collected_on' => $this->input('collected_on'),
            'shift' => $this->input('shift') ?: null,
            'eggs_count' => (int) $this->input('eggs_count'),
            'cracked_count' => (int) $this->input('cracked_count', 0),
            'weight_kg' => $this->input('weight_kg'),
            'collected_by' => $this->input('collected_by'),
            'notes' => $this->input('notes'),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('shift') === '') {
            $this->merge(['shift' => null]);
        }
    }
}
