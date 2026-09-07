<?php

namespace App\Http\Requests\Concerns;

use App\Models\Animal;
use App\Models\Farm;
use App\Models\Flock;
use Illuminate\Validation\Validator;

trait ValidatesStockSubject
{
    /**
     * @return array<string, mixed>
     */
    protected function stockSubjectRules(bool $required = true): array
    {
        $presence = $required ? 'required_without' : 'nullable';

        return [
            'animal_id' => [$presence.':flock_id', 'nullable', 'exists:animals,id', 'prohibits:flock_id'],
            'flock_id' => [$presence.':animal_id', 'nullable', 'exists:flocks,id', 'prohibits:animal_id'],
        ];
    }

    protected function prepareStockSubject(): void
    {
        $merge = [];

        foreach (['animal_id', 'flock_id'] as $field) {
            if ($this->input($field) === '') {
                $merge[$field] = null;
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    protected function validateStockMatchesFarm(Validator $validator, string $farmKey = 'farm_id'): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $farmId = $this->filled($farmKey) ? (int) $this->input($farmKey) : null;

        if ($this->filled('animal_id')) {
            $animal = Animal::query()->find($this->input('animal_id'));

            if ($animal && $farmId && (int) $animal->farm_id !== $farmId) {
                $validator->errors()->add('animal_id', 'The selected animal does not belong to this farm.');
            }
        }

        if ($this->filled('flock_id')) {
            $flock = Flock::query()->find($this->input('flock_id'));

            if ($flock && $farmId && (int) $flock->farm_id !== $farmId) {
                $validator->errors()->add('flock_id', 'The selected flock does not belong to this farm.');
            }
        }
    }

    /**
     * @return array{farm_id: int, animal_id: int|null, flock_id: int|null, livestock_id: int|null}
     */
    public function resolvedStockAttributes(): array
    {
        if ($this->filled('flock_id')) {
            $flock = Flock::query()->findOrFail($this->input('flock_id'));

            return [
                'farm_id' => $flock->farm_id,
                'animal_id' => null,
                'flock_id' => $flock->id,
                'livestock_id' => $flock->livestock_id,
            ];
        }

        $animal = Animal::query()->findOrFail($this->input('animal_id'));

        return [
            'farm_id' => $animal->farm_id,
            'animal_id' => $animal->id,
            'flock_id' => null,
            'livestock_id' => $animal->livestock_id,
        ];
    }

    protected function stockFarm(?int $fallbackFarmId = null): ?Farm
    {
        if ($this->filled('flock_id')) {
            return Flock::query()->find($this->input('flock_id'))?->farm;
        }

        if ($this->filled('animal_id')) {
            return Animal::query()->find($this->input('animal_id'))?->farm;
        }

        return $fallbackFarmId ? Farm::query()->find($fallbackFarmId) : null;
    }
}
