<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesFarmRelations;
use App\Models\Animal;
use App\Models\Farm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class FeedingScheduleRequest extends FormRequest
{
    use ValidatesFarmRelations;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $target = $this->input('feed_target', 'group');
        $isPoultry = $this->filled('farm_id')
            && Farm::query()->whereKey($this->input('farm_id'))->where('primary_species', 'poultry')->exists();

        return [
            'farm_id' => ['required', 'exists:farms,id'],
            'feed_type_id' => ['required', 'exists:feed_types,id'],
            'feed_inventory_id' => [
                'nullable',
                Rule::exists('feed_inventories', 'id')->where(function ($q) {
                    $q->where('farm_id', $this->input('farm_id'));

                    if ($this->filled('feed_type_id')) {
                        $q->where('feed_type_id', $this->input('feed_type_id'));
                    }
                }),
            ],
            'feed_target' => $isPoultry
                ? ['nullable', Rule::in(['none', 'group', 'animal'])]
                : ['required', Rule::in(['group', 'animal'])],
            'livestock_id' => $this->livestockRulesForTarget($isPoultry ? 'none' : $target),
            'animal_id' => $this->animalRulesForTarget($isPoultry ? 'none' : $target),
            'flock_id' => array_merge($this->optionalFlockBelongsToFarm(), ['prohibits:animal_id']),
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $farm = $this->filled('farm_id')
                ? Farm::query()->find($this->input('farm_id'))
                : null;

            if ($farm && ! $farm->isPoultry() && $this->filled('flock_id')) {
                $validator->errors()->add(
                    'flock_id',
                    __('Choose either a flock or a livestock group/animal, not both.')
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['feed_inventory_id', 'livestock_id', 'animal_id', 'flock_id', 'scheduled_time', 'end_date', 'next_due_date'] as $field) {
            if ($this->input($field) === '') {
                $merge[$field] = null;
            }
        }

        $farm = $this->filled('farm_id')
            ? Farm::query()->find($this->input('farm_id'))
            : null;

        if ($farm?->isPoultry()) {
            $merge['feed_target'] = 'none';
            $merge['livestock_id'] = null;
            $merge['animal_id'] = null;
        } else {
            $merge['flock_id'] = null;

            $target = $this->input('feed_target');

            if (! in_array($target, ['group', 'animal'], true)) {
                $target = ($merge['animal_id'] ?? $this->input('animal_id')) ? 'animal' : 'group';
                $merge['feed_target'] = $target;
            }

            if ($target === 'group') {
                $merge['animal_id'] = null;
            }

            $animalId = $merge['animal_id'] ?? $this->input('animal_id');
            $livestockId = $merge['livestock_id'] ?? $this->input('livestock_id');

            if ($target === 'animal' && $animalId && ! $livestockId) {
                $animal = Animal::query()->find($animalId);

                if ($animal?->livestock_id) {
                    $merge['livestock_id'] = $animal->livestock_id;
                }
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return list<string|\Illuminate\Validation\Rules\Exists>
     */
    private function livestockRulesForTarget(string $target): array
    {
        if ($target === 'none') {
            return ['nullable'];
        }

        $rules = $this->optionalLivestockBelongsToFarm();
        $rules[0] = 'required';

        return $rules;
    }

    /**
     * @return list<string|\Illuminate\Validation\Rules\Exists>
     */
    private function animalRulesForTarget(string $target): array
    {
        if ($target !== 'animal') {
            return ['nullable', 'prohibits:flock_id'];
        }

        if ($this->filled('livestock_id') && $this->filled('farm_id')) {
            return ['required', $this->animalBelongsToLivestock(), 'prohibits:flock_id'];
        }

        $rules = $this->optionalAnimalBelongsToFarm();
        $rules[0] = 'required';
        $rules[] = 'prohibits:flock_id';

        return $rules;
    }
}
