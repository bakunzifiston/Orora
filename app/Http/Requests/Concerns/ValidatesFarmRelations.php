<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;

trait ValidatesFarmRelations
{
    protected function animalBelongsToFarm(string $animalKey = 'animal_id', string $farmKey = 'farm_id'): \Illuminate\Validation\Rules\Exists
    {
        return Rule::exists('animals', 'id')->where(
            fn ($query) => $query->where('farm_id', $this->input($farmKey))
        );
    }

    protected function livestockBelongsToFarm(string $livestockKey = 'livestock_id', string $farmKey = 'farm_id'): \Illuminate\Validation\Rules\Exists
    {
        return Rule::exists('livestock', 'id')->where(
            fn ($query) => $query->where('farm_id', $this->input($farmKey))
        );
    }

    protected function animalBelongsToFarmId(?int $farmId, string $animalKey = 'animal_id'): \Illuminate\Validation\Rules\Exists
    {
        return Rule::exists('animals', 'id')->where(
            fn ($query) => $query->where('farm_id', $farmId)
        );
    }

    protected function livestockBelongsToFarmId(?int $farmId, string $livestockKey = 'livestock_id'): \Illuminate\Validation\Rules\Exists
    {
        return Rule::exists('livestock', 'id')->where(
            fn ($query) => $query->where('farm_id', $farmId)
        );
    }

    protected function animalBelongsToLivestock(
        string $animalKey = 'animal_id',
        string $livestockKey = 'livestock_id',
        string $farmKey = 'farm_id',
    ): \Illuminate\Validation\Rules\Exists {
        return Rule::exists('animals', 'id')->where(function ($query) use ($farmKey, $livestockKey): void {
            $query->where('farm_id', $this->input($farmKey))
                ->where('livestock_id', $this->input($livestockKey));
        });
    }

    /**
     * @return list<string|\Illuminate\Validation\Rules\Exists>
     */
    protected function optionalAnimalBelongsToFarm(string $animalKey = 'animal_id', string $farmKey = 'farm_id'): array
    {
        if (! $this->filled($farmKey)) {
            return ['nullable', 'exists:animals,id'];
        }

        return ['nullable', $this->animalBelongsToFarm($animalKey, $farmKey)];
    }

    /**
     * @return list<string|\Illuminate\Validation\Rules\Exists>
     */
    protected function optionalLivestockBelongsToFarm(string $livestockKey = 'livestock_id', string $farmKey = 'farm_id'): array
    {
        if (! $this->filled($farmKey)) {
            return ['nullable', 'exists:livestock,id'];
        }

        return ['nullable', $this->livestockBelongsToFarm($livestockKey, $farmKey)];
    }
}
