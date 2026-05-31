<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\BirthRecord;
use App\Models\BreedingLog;
use App\Models\BreedingRecord;
use App\Models\Offspring;
use App\Models\PregnancyCheck;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BreedingService
{
    public function createBreedingRecord(array $attributes): BreedingRecord
    {
        $female = Animal::query()->findOrFail($attributes['female_animal_id']);
        $this->assertFemaleAnimal($female);

        if (! empty($attributes['male_animal_id'])) {
            $male = Animal::query()->findOrFail($attributes['male_animal_id']);
            $this->assertMaleAnimal($male);

            if ((int) $male->farm_id !== (int) $female->farm_id) {
                throw new InvalidArgumentException('Male and female must belong to the same farm.');
            }
        } elseif (empty($attributes['external_sire_name'])) {
            throw new InvalidArgumentException('Select an internal sire or enter an external sire name.');
        }

        if ((int) $attributes['farm_id'] !== (int) $female->farm_id) {
            throw new InvalidArgumentException('Female animal must belong to the selected farm.');
        }

        $gestationDays = $attributes['gestation_period_days']
            ?? $this->defaultGestationDays($attributes['animal_type']);
        $breedingDate = Carbon::parse($attributes['breeding_date']);

        return DB::transaction(function () use ($attributes, $gestationDays, $breedingDate) {
            $record = BreedingRecord::create([
                ...$attributes,
                'breeding_code' => $this->generateCode('BR', 'breeding_code', BreedingRecord::class, $breedingDate),
                'gestation_period_days' => $gestationDays,
                'expected_calving_date' => $breedingDate->copy()->addDays($gestationDays)->toDateString(),
                'breeding_status' => $attributes['breeding_status'] ?? 'pending',
                'created_by' => auth()->id(),
            ]);

            $this->log($record, 'created', 'Breeding event recorded.');

            return $record->fresh(['farm', 'femaleAnimal', 'maleAnimal']);
        });
    }

    public function updateBreedingRecord(BreedingRecord $record, array $attributes): BreedingRecord
    {
        if ($record->birthRecord) {
            throw new InvalidArgumentException('Cannot edit a breeding record that already has a birth recorded.');
        }

        $female = Animal::query()->findOrFail($attributes['female_animal_id'] ?? $record->female_animal_id);
        $this->assertFemaleAnimal($female);

        $animalType = $attributes['animal_type'] ?? $record->animal_type;
        $gestationDays = $attributes['gestation_period_days']
            ?? $record->gestation_period_days
            ?? $this->defaultGestationDays($animalType);
        $breedingDate = Carbon::parse($attributes['breeding_date'] ?? $record->breeding_date);

        $attributes['gestation_period_days'] = $gestationDays;
        $attributes['expected_calving_date'] = $breedingDate->copy()->addDays($gestationDays)->toDateString();

        $record->update($attributes);

        return $record->fresh(['farm', 'femaleAnimal', 'maleAnimal']);
    }

    public function createPregnancyCheck(array $attributes, ?UploadedFile $attachment = null): PregnancyCheck
    {
        $breeding = BreedingRecord::query()->findOrFail($attributes['breeding_record_id']);

        if ($breeding->birthRecord) {
            throw new InvalidArgumentException('This breeding cycle already has a birth record.');
        }

        $checkDate = Carbon::parse($attributes['check_date']);
        $breedingDate = $breeding->breeding_date;

        if ($checkDate->lt($breedingDate)) {
            throw new InvalidArgumentException('Check date must be on or after the breeding date.');
        }

        if ((int) $attributes['animal_id'] !== (int) $breeding->female_animal_id) {
            throw new InvalidArgumentException('Check must be for the bred female animal.');
        }

        if ($attachment) {
            $attributes['attachment_path'] = $attachment->store('breeding/checks', 'public');
        }

        return DB::transaction(function () use ($attributes, $breeding, $checkDate) {
            $check = PregnancyCheck::create([
                ...$attributes,
                'check_code' => $this->generateCode('PC', 'check_code', PregnancyCheck::class, $checkDate),
            ]);

            $this->applyPregnancyCheckResult($breeding, $check);
            $this->log($breeding, 'pregnancy_checked', "Check {$check->check_code}: {$check->resultLabel()}.");

            return $check->fresh(['breedingRecord', 'animal']);
        });
    }

    public function createBirthRecord(array $attributes, ?UploadedFile $attachment = null): BirthRecord
    {
        $breeding = BreedingRecord::query()->findOrFail($attributes['breeding_record_id']);

        if ($breeding->birthRecord) {
            throw new InvalidArgumentException('A birth record already exists for this breeding.');
        }

        if (! in_array($breeding->breeding_status, ['pending', 'confirmed_pregnant'], true)) {
            throw new InvalidArgumentException('Birth can only be recorded for pending or confirmed pregnant breedings.');
        }

        if ((int) $attributes['mother_animal_id'] !== (int) $breeding->female_animal_id) {
            throw new InvalidArgumentException('Mother must match the breeding female.');
        }

        if ($attributes['total_offspring'] < $attributes['alive_offspring']) {
            throw new InvalidArgumentException('Alive offspring cannot exceed total offspring.');
        }

        if ($attachment) {
            $attributes['attachment_path'] = $attachment->store('breeding/births', 'public');
        }

        $birthDate = Carbon::parse($attributes['birth_date']);

        return DB::transaction(function () use ($attributes, $breeding, $birthDate) {
            $birth = BirthRecord::create([
                ...$attributes,
                'birth_code' => $this->generateCode('BIR', 'birth_code', BirthRecord::class, $birthDate),
            ]);

            $breeding->update(['breeding_status' => 'calved']);

            $mother = Animal::query()->findOrFail($birth->mother_animal_id);
            $motherUpdates = $this->motherUpdatesAfterBirth($breeding->animal_type, $attributes['mother_condition_after']);
            $mother->update($motherUpdates);

            $this->seedOffspringRows($birth, $breeding);
            $this->log($breeding, 'calved', "Birth {$birth->birth_code} recorded.");

            return $birth->fresh(['breedingRecord', 'motherAnimal', 'offspring']);
        });
    }

    public function updateOffspring(Offspring $offspring, array $attributes): Offspring
    {
        if ($offspring->is_registered) {
            throw new InvalidArgumentException('Registered offspring cannot be edited here.');
        }

        $offspring->update($attributes);

        return $offspring->fresh();
    }

    public function registerOffspring(Offspring $offspring, array $animalAttributes): Offspring
    {
        if ($offspring->is_registered) {
            throw new InvalidArgumentException('This offspring is already registered as an animal.');
        }

        if ($offspring->health_status_at_birth === 'stillborn') {
            throw new InvalidArgumentException('Stillborn offspring cannot be registered as live animals.');
        }

        $birth = $offspring->birthRecord()->with('breedingRecord')->firstOrFail();
        $mother = $offspring->motherAnimal;
        $breeding = $birth->breedingRecord;

        return DB::transaction(function () use ($offspring, $animalAttributes, $birth, $mother, $breeding) {
            $animal = Animal::create([
                'farm_id' => $mother->farm_id,
                'livestock_id' => $mother->livestock_id,
                'tag_number' => $animalAttributes['tag_number'],
                'name' => $animalAttributes['name'],
                'gender' => $offspring->gender === 'unknown' ? ($animalAttributes['gender'] ?? 'unknown') : $offspring->gender,
                'species' => $breeding->animal_type,
                'breed' => $animalAttributes['breed'] ?? $mother->breed,
                'date_of_birth' => $birth->birth_date,
                'weight_kg' => $offspring->birth_weight_kg,
                'color_markings' => $offspring->color_markings,
                'acquisition_type' => 'Born on farm',
                'acquisition_date' => $birth->birth_date,
                'mother_tag' => $mother->tag_number,
                'father_tag' => $offspring->fatherAnimal?->tag_number,
                'health_status' => $this->mapOffspringHealthToAnimal($offspring->health_status_at_birth),
                'production_status' => null,
                'lifecycle_status' => 'Active',
                'notes' => $offspring->notes,
            ]);

            $offspring->update([
                'animal_id' => $animal->id,
                'is_registered' => true,
            ]);

            $this->log($breeding, 'offspring_registered', "Offspring {$offspring->offspring_code} registered as {$animal->tag_number}.");

            return $offspring->fresh(['animal', 'birthRecord']);
        });
    }

    public function defaultGestationDays(string $animalType): int
    {
        return (int) (config('modules.breeding_gestation_days')[$animalType] ?? 283);
    }

    public function generateCode(string $prefix, string $column, string $modelClass, Carbon|string $date): string
    {
        $dateKey = Carbon::parse($date)->format('Ymd');
        $codePrefix = "{$prefix}-{$dateKey}-";
        $last = $modelClass::query()
            ->where($column, 'like', $codePrefix.'%')
            ->orderByDesc($column)
            ->value($column);
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('%s%04d', $codePrefix, $seq);
    }

    private function applyPregnancyCheckResult(BreedingRecord $breeding, PregnancyCheck $check): void
    {
        $female = $breeding->femaleAnimal;

        match ($check->result) {
            'confirmed_pregnant' => (function () use ($breeding, $check, $female) {
                $breeding->update([
                    'breeding_status' => 'confirmed_pregnant',
                    'expected_calving_date' => $check->expected_calving_date ?? $breeding->expected_calving_date,
                ]);
                $female?->update([
                    'health_status' => 'Pregnant',
                    'production_status' => 'Gestating',
                ]);
                $this->log($breeding, 'confirmed_pregnant', 'Pregnancy confirmed.');
            })(),
            'not_pregnant' => (function () use ($breeding, $female) {
                $breeding->update(['breeding_status' => 'failed']);
                if ($female && in_array($female->health_status, ['Pregnant'], true)) {
                    $female->update([
                        'health_status' => 'Healthy',
                        'production_status' => null,
                    ]);
                }
                $this->log($breeding, 'failed', 'Pregnancy check: not pregnant.');
            })(),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function motherUpdatesAfterBirth(string $animalType, string $condition): array
    {
        $updates = [
            'health_status' => in_array($condition, ['weak', 'critical'], true) ? 'Sick' : 'Healthy',
            'production_status' => in_array($animalType, config('modules.lactating_after_birth_types', []), true)
                ? 'Lactating'
                : null,
        ];

        if ($condition === 'died') {
            $updates['health_status'] = 'Deceased';
            $updates['lifecycle_status'] = 'Deceased';
            $updates['production_status'] = null;
        }

        return $updates;
    }

    private function seedOffspringRows(BirthRecord $birth, BreedingRecord $breeding): void
    {
        $existing = $birth->offspring()->count();

        if ($existing >= $birth->alive_offspring) {
            return;
        }

        $toCreate = $birth->alive_offspring - $existing;
        $birthDate = $birth->birth_date;

        for ($i = 0; $i < $toCreate; $i++) {
            Offspring::create([
                'birth_record_id' => $birth->id,
                'mother_animal_id' => $birth->mother_animal_id,
                'father_animal_id' => $breeding->male_animal_id,
                'external_sire_name' => $breeding->external_sire_name,
                'offspring_code' => $this->generateCode('OFF', 'offspring_code', Offspring::class, $birthDate),
                'gender' => 'unknown',
                'health_status_at_birth' => 'healthy',
                'is_registered' => false,
            ]);
        }
    }

    private function mapOffspringHealthToAnimal(string $status): string
    {
        return match ($status) {
            'weak' => 'Recovering',
            'sick' => 'Sick',
            default => 'Healthy',
        };
    }

    private function assertFemaleAnimal(Animal $animal): void
    {
        if ($animal->gender !== 'female') {
            throw new InvalidArgumentException('Breeding female must be a female animal.');
        }
    }

    private function assertMaleAnimal(Animal $animal): void
    {
        if ($animal->gender !== 'male') {
            throw new InvalidArgumentException('Internal sire must be a male animal.');
        }
    }

    private function log(BreedingRecord $record, string $actionType, ?string $notes = null): void
    {
        BreedingLog::create([
            'breeding_record_id' => $record->id,
            'action_type' => $actionType,
            'action_by' => auth()->id(),
            'action_date' => now(),
            'notes' => $notes,
        ]);
    }
}
