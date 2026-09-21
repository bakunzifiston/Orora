<?php

namespace App\Services\Import;

use App\Models\Animal;
use App\Models\Farm;
use App\Models\Livestock;
use App\Services\Import\Concerns\NormalizesAnimalStatuses;
use App\Services\Import\Concerns\NormalizesDates;
use App\Services\Import\Concerns\ParsesAnimalImportFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Throwable;

class AnimalCsvImporter
{
    use NormalizesAnimalStatuses;
    use NormalizesDates;
    use ParsesAnimalImportFile;

    public const MAX_ROWS = 2000;

    /** @var array<string, Farm> */
    protected array $farmCache = [];

    /** @var array<string, Livestock> */
    protected array $livestockCache = [];

    /** @var array<string, int> tag keys already accepted in this import file */
    protected array $seenTags = [];

    /**
     * @return array{
     *     created: int,
     *     failed: int,
     *     total: int,
     *     errors: list<array{row: int, message: string, messages: list<string>}>,
     *     warnings: list<array{row: int, message: string}>
     * }
     */
    public function import(UploadedFile $file): array
    {
        $this->farmCache = [];
        $this->livestockCache = [];
        $this->seenTags = [];

        $created = 0;
        $failed = 0;
        $errors = [];
        $warnings = [];

        try {
            [, $rows] = $this->readAnimalImportRows($file, self::MAX_ROWS);

            $this->detectImportDateConvention(
                array_merge(
                    array_column($rows, 'date_of_birth'),
                    array_column($rows, 'acquisition_date')
                )
            );
        } catch (InvalidArgumentException $e) {
            return [
                'created' => 0,
                'failed' => 1,
                'total' => 0,
                'errors' => [[
                    'row' => 0,
                    'message' => $e->getMessage(),
                    'messages' => [$e->getMessage()],
                ]],
                'warnings' => [],
            ];
        }

        $total = count($rows);

        foreach ($rows as $rowNumber => $row) {
            try {
                $result = DB::transaction(function () use ($row, $rowNumber) {
                    return $this->importRow($row, $rowNumber);
                });

                $created++;

                foreach ($result['warnings'] as $message) {
                    $warnings[] = ['row' => $rowNumber, 'message' => $message];
                }
            } catch (InvalidArgumentException $e) {
                $failed++;
                $messages = array_values(array_filter(array_map('trim', explode("\n", $e->getMessage()))));

                if ($messages === []) {
                    $messages = [$e->getMessage()];
                }

                $errors[] = [
                    'row' => $rowNumber,
                    'message' => implode(' ', $messages),
                    'messages' => $messages,
                ];
            } catch (Throwable $e) {
                $failed++;
                $message = 'Unexpected error while saving this row.';
                $errors[] = [
                    'row' => $rowNumber,
                    'message' => $message,
                    'messages' => [$message],
                ];
                report($e);
            }
        }

        return compact('created', 'failed', 'total', 'errors', 'warnings');
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array{warnings: list<string>}
     */
    protected function importRow(array $row, int $rowNumber): array
    {
        $warnings = [];
        $messages = [];

        $farmName = trim((string) ($row['farm_name'] ?? ''));
        $livestockName = trim((string) ($row['livestock_name'] ?? ''));

        if ($farmName === '') {
            $messages[] = 'Farm name is required.';
        }

        if ($livestockName === '') {
            $messages[] = 'Livestock group name is required.';
        }

        if ($messages !== []) {
            throw new InvalidArgumentException(implode("\n", $messages));
        }

        $farm = $this->resolveFarm($farmName);
        $livestock = $this->resolveLivestock($farm, $livestockName, $row, $warnings);

        $tagNumber = filled($row['tag_number'] ?? null) ? trim((string) $row['tag_number']) : null;

        if ($tagNumber === null) {
            $tagNumber = $this->generateTagNumber($farm, $livestock);
            $warnings[] = __('Tag number was blank, so :tag was assigned.', ['tag' => $tagNumber]);
        }

        $tagKey = $livestock->id.'|'.mb_strtolower($tagNumber);

        if (isset($this->seenTags[$tagKey])) {
            throw new InvalidArgumentException(
                "Duplicate tag number \"{$tagNumber}\" in this file (also on row {$this->seenTags[$tagKey]})."
            );
        }

        $exists = Animal::query()
            ->where('livestock_id', $livestock->id)
            ->where('tag_number', $tagNumber)
            ->exists();

        if ($exists) {
            throw new InvalidArgumentException(
                "An animal with tag number \"{$tagNumber}\" already exists in group \"{$livestock->name}\"."
            );
        }

        $name = trim((string) ($row['name'] ?? ''));

        if ($name === '') {
            $name = $tagNumber;
            $warnings[] = __('Name was blank, so the tag number was used as the name.');
        }

        $rawGender = trim((string) ($row['gender'] ?? ''));
        $gender = match (strtolower($rawGender)) {
            'm', 'male' => 'male',
            'f', 'female' => 'female',
            'u', 'unknown', '' => 'unknown',
            default => 'unknown',
        };

        if ($rawGender === '') {
            $warnings[] = __('Gender was blank, so unknown was used.');
        } elseif (! in_array(strtolower($rawGender), ['male', 'female', 'unknown', 'm', 'f', 'u'], true)) {
            $warnings[] = __('Gender ":from" was not recognised, so unknown was used.', ['from' => $rawGender]);
        }

        $dateOfBirth = $this->normalizeImportDate($row['date_of_birth'] ?? null);

        if ($dateOfBirth !== null && ! $this->isNormalizedDate($dateOfBirth)) {
            $warnings[] = __('Date of birth ":date" was not recognised, so it was left blank.', ['date' => $dateOfBirth]);
            $dateOfBirth = null;
        }

        if ($this->isNormalizedDate($dateOfBirth) && $dateOfBirth > now()->toDateString()) {
            $warnings[] = __('Date of birth :date is in the future, so it was left blank.', ['date' => $dateOfBirth]);
            $dateOfBirth = null;
        }

        $rawHealth = $row['health_status'] ?? null;
        $rawProduction = $row['production_status'] ?? null;
        [$healthStatus, $productionStatus] = $this->normalizeHealthAndProduction($rawHealth, $rawProduction);

        if ($healthStatus === null || ! in_array($healthStatus, config('modules.health_statuses'), true)) {
            $warnings[] = filled($rawHealth)
                ? __('Health status ":from" was not recognised, so Healthy was used.', ['from' => $rawHealth])
                : __('Health status was blank, so Healthy was used.');
            $healthStatus = 'Healthy';
        } elseif (
            $productionStatus === 'Gestating'
            && $healthStatus === 'Pregnant'
            && in_array($this->aliasKey((string) ($rawHealth ?? '')), ['', 'healthy'], true)
        ) {
            $warnings[] = __('Health status was set to Pregnant so pregnancy filters can find this animal.');
        } elseif (filled($rawHealth) && $rawHealth !== $healthStatus) {
            $warnings[] = __('Health status ":from" was mapped to :to.', [
                'from' => $rawHealth,
                'to' => $healthStatus,
            ]);
        }

        if ($productionStatus !== null && ! in_array($productionStatus, config('modules.production_statuses'), true)) {
            $warnings[] = __('Production status ":from" was not recognised, so it was left blank.', [
                'from' => $rawProduction ?? $productionStatus,
            ]);
            $productionStatus = null;
        } elseif (filled($rawProduction) && $productionStatus !== null && $rawProduction !== $productionStatus) {
            $warnings[] = __('Production status ":from" was mapped to :to.', [
                'from' => $rawProduction,
                'to' => $productionStatus,
            ]);
        }

        $rawLifecycle = $row['lifecycle_status'] ?? null;
        $lifecycleStatus = $this->normalizeLifecycleStatus($rawLifecycle);

        if ($lifecycleStatus === null || ! in_array($lifecycleStatus, config('modules.lifecycle_statuses'), true)) {
            $warnings[] = filled($rawLifecycle)
                ? __('Lifecycle status ":from" was not recognised, so Active was used.', ['from' => $rawLifecycle])
                : __('Lifecycle status was blank, so Active was used.');
            $lifecycleStatus = 'Active';
        }

        $rawAcquisition = $row['acquisition_type'] ?? null;
        $acquisitionType = $this->normalizeAcquisitionType($rawAcquisition);

        if ($acquisitionType !== null && ! in_array($acquisitionType, config('modules.acquisition_types'), true)) {
            $warnings[] = __('Acquisition type ":from" was not recognised, so it was left blank.', [
                'from' => $rawAcquisition ?? $acquisitionType,
            ]);
            $acquisitionType = null;
        } elseif (filled($rawAcquisition) && $acquisitionType !== null && $rawAcquisition !== $acquisitionType) {
            $warnings[] = __('Acquisition type ":from" was mapped to :to.', [
                'from' => $rawAcquisition,
                'to' => $acquisitionType,
            ]);
        }

        $rawCondition = $row['current_condition'] ?? null;
        $currentCondition = $this->normalizeCurrentCondition($rawCondition);

        if ($currentCondition !== null && ! in_array($currentCondition, config('modules.current_conditions'), true)) {
            $warnings[] = __('Current condition ":from" was not recognised, so it was left blank.', [
                'from' => $rawCondition ?? $currentCondition,
            ]);
            $currentCondition = null;
        }

        $acquisitionDate = $this->normalizeImportDate($row['acquisition_date'] ?? null);

        if ($acquisitionDate !== null && ! $this->isNormalizedDate($acquisitionDate)) {
            $warnings[] = __('Acquisition date ":date" was not recognised, so it was left blank.', ['date' => $acquisitionDate]);
            $acquisitionDate = null;
        }

        $weight = $row['weight_kg'] ?? null;

        if ($weight !== null && $weight !== '' && ! is_numeric($weight)) {
            $warnings[] = __('Weight ":weight" was not a number, so it was left blank.', ['weight' => $weight]);
            $weight = null;
        }

        $payload = [
            'farm_id' => $farm->id,
            'livestock_id' => $livestock->id,
            'tag_number' => $tagNumber,
            'name' => $name,
            'gender' => $gender,
            'health_status' => $healthStatus,
            'lifecycle_status' => $lifecycleStatus,
            'date_of_birth' => $dateOfBirth,
            'weight_kg' => $weight === '' ? null : $weight,
            'color_markings' => $row['color_markings'] ?? null,
            'species' => $row['species'] ?? null,
            'breed' => $row['breed'] ?? null,
            'acquisition_type' => $acquisitionType,
            'acquisition_date' => $acquisitionDate,
            'source' => $row['source'] ?? null,
            'mother_tag' => $row['mother_tag'] ?? null,
            'father_tag' => $row['father_tag'] ?? null,
            'production_status' => $productionStatus,
            'current_condition' => $currentCondition,
            'notes' => $row['notes'] ?? null,
        ];

        $validator = Validator::make($payload, [
            'farm_id' => ['required', 'exists:farms,id'],
            'livestock_id' => [
                'required',
                Rule::exists('livestock', 'id')->where(fn ($query) => $query->where('farm_id', $farm->id)),
            ],
            'tag_number' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(array_keys(config('modules.animal_genders')))],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'color_markings' => ['nullable', 'string', 'max:255'],
            'acquisition_type' => ['nullable', Rule::in(config('modules.acquisition_types'))],
            'acquisition_date' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:255'],
            'mother_tag' => ['nullable', 'string', 'max:100'],
            'father_tag' => ['nullable', 'string', 'max:100'],
            'health_status' => ['required', Rule::in(config('modules.health_statuses'))],
            'production_status' => ['nullable', Rule::in(config('modules.production_statuses'))],
            'lifecycle_status' => ['required', Rule::in(config('modules.lifecycle_statuses'))],
            'current_condition' => ['nullable', Rule::in(config('modules.current_conditions'))],
            'species' => ['nullable', 'string', 'max:100'],
            'breed' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException(implode("\n", $validator->errors()->all()));
        }

        Animal::create($validator->validated());

        $this->seenTags[$tagKey] = $rowNumber;

        return ['warnings' => $warnings];
    }

    protected function generateTagNumber(Farm $farm, Livestock $livestock): string
    {
        $letters = preg_replace('/[^A-Za-z]/', '', (string) $farm->name) ?? '';
        $prefix = $letters === '' ? 'ANM' : strtoupper(substr($letters, 0, 3));
        $sequence = Animal::query()->where('livestock_id', $livestock->id)->count() + 1;

        while (true) {
            $candidate = sprintf('%s-%04d', $prefix, $sequence);
            $tagKey = $livestock->id.'|'.mb_strtolower($candidate);

            $takenInFile = isset($this->seenTags[$tagKey]);
            $takenInDb = Animal::query()
                ->where('livestock_id', $livestock->id)
                ->where('tag_number', $candidate)
                ->exists();

            if (! $takenInFile && ! $takenInDb) {
                return $candidate;
            }

            $sequence++;
        }
    }

    /**
     * @param  array<string, string|null>  $row
     * @param  list<string>  $warnings
     */
    protected function resolveLivestock(Farm $farm, string $livestockName, array $row, array &$warnings): Livestock
    {
        $cacheKey = $farm->id.'|'.mb_strtolower($livestockName);

        if (isset($this->livestockCache[$cacheKey])) {
            return $this->livestockCache[$cacheKey];
        }

        $groups = Livestock::query()
            ->where('farm_id', $farm->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($livestockName)])
            ->get();

        if ($groups->count() > 1) {
            throw new InvalidArgumentException(
                "Livestock group \"{$livestockName}\" matches more than one group on farm \"{$farm->name}\"."
            );
        }

        if ($groups->isNotEmpty()) {
            return $this->livestockCache[$cacheKey] = $groups->first();
        }

        $group = $this->createLivestockGroup($farm, $livestockName, $row);
        $warnings[] = __('Livestock group ":name" did not exist on :farm, so it was created.', [
            'name' => $group->name,
            'farm' => $farm->name,
        ]);

        return $this->livestockCache[$cacheKey] = $group;
    }

    /**
     * @param  array<string, string|null>  $row
     */
    protected function createLivestockGroup(Farm $farm, string $name, array $row): Livestock
    {
        $livestockType = $this->inferLivestockType($row['species'] ?? null, $farm);

        return Livestock::create([
            'farm_id' => $farm->id,
            'name' => mb_substr($name, 0, 255),
            'herd_groups' => ['Other'],
            'herd_group_other' => mb_substr($name, 0, 255),
            'livestock_types' => [$livestockType],
            'production_purposes' => ['Other'],
            'production_purpose_other' => 'Imported',
            'farming_methods' => ['Other'],
            'farming_method_other' => 'Imported',
            'feeding_methods' => ['Other'],
            'feeding_method_other' => 'Imported',
            'breed' => isset($row['breed']) ? mb_substr(trim((string) $row['breed']), 0, 255) ?: null : null,
            'head_count' => 0,
            'status' => 'active',
            'notes' => 'Created automatically during animal import.',
        ]);
    }

    protected function inferLivestockType(?string $species, Farm $farm): string
    {
        $species = mb_strtolower(trim((string) $species));

        $map = [
            'cattle' => 'Cattle',
            'cow' => 'Cattle',
            'cows' => 'Cattle',
            'bull' => 'Cattle',
            'bulls' => 'Cattle',
            'goat' => 'Goat',
            'goats' => 'Goat',
            'sheep' => 'Sheep',
            'pig' => 'Pig',
            'pigs' => 'Pig',
            'poultry' => 'Poultry',
            'chicken' => 'Poultry',
            'chickens' => 'Poultry',
            'layer' => 'Poultry',
            'layers' => 'Poultry',
        ];

        if ($species !== '' && isset($map[$species])) {
            return $map[$species];
        }

        return match ($farm->primary_species) {
            'poultry' => 'Poultry',
            'goats' => 'Goat',
            'sheep' => 'Sheep',
            'pigs' => 'Pig',
            default => 'Cattle',
        };
    }

    protected function resolveFarm(string $farmName): Farm
    {
        $key = mb_strtolower($farmName);

        if (isset($this->farmCache[$key])) {
            return $this->farmCache[$key];
        }

        $farms = Farm::query()
            ->whereRaw('LOWER(name) = ?', [$key])
            ->get();

        if ($farms->isEmpty()) {
            throw new InvalidArgumentException("Farm \"{$farmName}\" was not found.");
        }

        if ($farms->count() > 1) {
            throw new InvalidArgumentException("Farm name \"{$farmName}\" matches more than one farm.");
        }

        return $this->farmCache[$key] = $farms->first();
    }
}
