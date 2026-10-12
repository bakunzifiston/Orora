<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesFarmRelations;
use App\Models\Animal;
use App\Models\Farm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class FeedingRecordRequest extends FormRequest
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
            'feed_inventory_id' => [
                'required',
                Rule::exists('feed_inventories', 'id')->where(function ($q) {
                    $currentInventoryId = $this->route('feeding')?->feed_inventory_id;

                    $q->where('farm_id', $this->input('farm_id'))
                        ->where(function ($inner) use ($currentInventoryId) {
                            $inner->whereHas('feedType', fn ($type) => $type->where('is_active', true));

                            if ($currentInventoryId) {
                                $inner->orWhere('id', $currentInventoryId);
                            }
                        });
                }),
            ],
            'feeding_schedule_id' => [
                'nullable',
                Rule::exists('feeding_schedules', 'id')->where(fn ($q) => $q->where('farm_id', $this->input('farm_id'))),
            ],
            'feed_target' => $isPoultry
                ? ['nullable', Rule::in(['none', 'group', 'animal'])]
                : ['required', Rule::in(['group', 'animal'])],
            'livestock_id' => $this->livestockRulesForTarget($isPoultry ? 'none' : $target),
            'animal_id' => $this->animalRulesForTarget($isPoultry ? 'none' : $target),
            'flock_id' => array_merge($this->optionalFlockBelongsToFarm(), ['prohibits:animal_id']),
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'fed_on' => ['required', 'date'],
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

        foreach (['livestock_id', 'animal_id', 'flock_id', 'feeding_schedule_id'] as $field) {
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
