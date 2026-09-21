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

    public const ACTION_KEEP = 'keep';

    public const ACTION_REPLACE = 'replace';

    /** @var array<string, Farm> */
    protected array $farmCache = [];

    /** @var array<string, Livestock|null> */
    protected array $livestockCache = [];

    /** @var array<string, int> tag keys already accepted in this import file */
    protected array $seenTags = [];

    /**
     * Validate the file and classify rows without writing to the database.
     *
     * Duplicate detection uses the application's unique key: livestock_id + tag_number.
     *
     * @return array{
     *     total: int,
     *     new_count: int,
     *     existing_count: int,
     *     failed_count: int,
     *     new_rows: list<array{row: int, tag_number: string, name: string, farm_name: string, livestock_name: string}>,
     *     existing_rows: list<array{row: int, tag_number: string, name: string, existing_name: string, animal_id: int, farm_name: string, livestock_name: string}>,
     *     errors: list<array{row: int, message: string, messages: list<string>}>,
     *     warnings: list<array{row: int, message: string}>
     * }
     */
    public function preview(UploadedFile $file): array
    {
        $this->resetRuntimeState();

        try {
            $rows = $this->loadRows($file);
        } catch (InvalidArgumentException $e) {
            return $this->fileLevelFailurePreview($e->getMessage());
        }

        $newRows = [];
        $existingRows = [];
        $errors = [];
        $warnings = [];

        foreach ($rows as $rowNumber => $row) {
            $prepared = $this->prepareRow($row, $rowNumber, allowCreateLivestock: false);

            foreach ($prepared['warnings'] as $message) {
                $warnings[] = ['row' => $rowNumber, 'message' => $message];
            }

            if ($prepared['status'] === 'invalid') {
                $errors[] = [
                    'row' => $rowNumber,
                    'message' => implode(' ', $prepared['messages']),
                    'messages' => $prepared['messages'],
                ];

                continue;
            }

            $summary = [
                'row' => $rowNumber,
                'tag_number' => (string) $prepared['tag_number'],
                'name' => (string) $prepared['name'],
                'farm_name' => (string) ($row['farm_name'] ?? ''),
                'livestock_name' => (string) ($row['livestock_name'] ?? ''),
            ];

            if ($prepared['status'] === 'existing') {
                $existingRows[] = $summary + [
                    'existing_name' => (string) $prepared['existing_name'],
                    'animal_id' => (int) $prepared['animal_id'],
                ];
                $this->seenTags[$prepared['tag_key']] = $rowNumber;

                continue;
            }

            $newRows[] = $summary;
            $this->seenTags[$prepared['tag_key']] = $rowNumber;
        }

        return [
            'total' => count($rows),
            'new_count' => count($newRows),
            'existing_count' => count($existingRows),
            'failed_count' => count($errors),
            'new_rows' => $newRows,
            'existing_rows' => $existingRows,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Persist a previously validated import file.
     *
     * @param  self::ACTION_KEEP|self::ACTION_REPLACE  $duplicateAction
     * @return array{
     *     created: int,
     *     updated: int,
     *     skipped: int,
     *     failed: int,
     *     total: int,
     *     errors: list<array{row: int, message: string, messages: list<string>}>,
     *     warnings: list<array{row: int, message: string}>
     * }
     */
    public function import(UploadedFile $file, string $duplicateAction = self::ACTION_KEEP): array
    {
        if (! in_array($duplicateAction, [self::ACTION_KEEP, self::ACTION_REPLACE], true)) {
            throw new InvalidArgumentException('Invalid duplicate action.');
        }

        $this->resetRuntimeState();

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $failed = 0;
        $errors = [];
        $warnings = [];

        try {
            $rows = $this->loadRows($file);
        } catch (InvalidArgumentException $e) {
            return [
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
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
                $result = DB::transaction(function () use ($row, $rowNumber, $duplicateAction) {
                    return $this->persistRow($row, $rowNumber, $duplicateAction);
                });

                match ($result['outcome']) {
                    'created' => $created++,
                    'updated' => $updated++,
                    'skipped' => $skipped++,
                };

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

        return compact('created', 'updated', 'skipped', 'failed', 'total', 'errors', 'warnings');
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    protected function loadRows(UploadedFile $file): array
    {
        [, $rows] = $this->readAnimalImportRows($file, self::MAX_ROWS);

        $this->detectImportDateConvention(
            array_merge(
                array_column($rows, 'date_of_birth'),
                array_column($rows, 'acquisition_date')
            )
        );

        return $rows;
    }

    protected function resetRuntimeState(): void
    {
        $this->farmCache = [];
        $this->livestockCache = [];
        $this->seenTags = [];
    }

    /**
     * @return array{
     *     total: int,
     *     new_count: int,
     *     existing_count: int,
     *     failed_count: int,
     *     new_rows: list<array<string, mixed>>,
     *     existing_rows: list<array<string, mixed>>,
     *     errors: list<array{row: int, message: string, messages: list<string>}>,
     *     warnings: list<array{row: int, message: string}>
     * }
     */
    protected function fileLevelFailurePreview(string $message): array
    {
        return [
            'total' => 0,
            'new_count' => 0,
            'existing_count' => 0,
            'failed_count' => 1,
            'new_rows' => [],
            'existing_rows' => [],
            'errors' => [[
                'row' => 0,
                'message' => $message,
                'messages' => [$message],
            ]],
            'warnings' => [],
        ];
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array{outcome: 'created'|'updated'|'skipped', warnings: list<string>}
     */
    protected function persistRow(array $row, int $rowNumber, string $duplicateAction): array
    {
        $prepared = $this->prepareRow($row, $rowNumber, allowCreateLivestock: true);

        if ($prepared['status'] === 'invalid') {
            throw new InvalidArgumentException(implode("\n", $prepared['messages']));
        }

        $this->seenTags[$prepared['tag_key']] = $rowNumber;

        if ($prepared['status'] === 'existing') {
            if ($duplicateAction === self::ACTION_KEEP) {
                return ['outcome' => 'skipped', 'warnings' => $prepared['warnings']];
            }

            /** @var Animal $animal */
            $animal = Animal::query()->findOrFail($prepared['animal_id']);
            $animal->update($prepared['payload']);

            return ['outcome' => 'updated', 'warnings' => $prepared['warnings']];
        }

        Animal::create($prepared['payload']);

        return ['outcome' => 'created', 'warnings' => $prepared['warnings']];
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array{
     *     status: 'new'|'existing'|'invalid',
     *     messages: list<string>,
     *     warnings: list<string>,
     *     tag_number: ?string,
     *     tag_key: ?string,
     *     name: ?string,
     *     animal_id: ?int,
     *     existing_name: ?string,
     *     payload: ?array<string, mixed>
     * }
     */
    protected function prepareRow(array $row, int $rowNumber, bool $allowCreateLivestock): array
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
            return $this->invalidPreparation($messages, $warnings);
        }

        try {
            $farm = $this->resolveFarm($farmName);
            $livestock = $this->resolveLivestock($farm, $livestockName, $row, $warnings, $allowCreateLivestock);
        } catch (InvalidArgumentException $e) {
            return $this->invalidPreparation(
                array_values(array_filter(array_map('trim', explode("\n", $e->getMessage())))),
                $warnings
            );
        }

        $tagNumber = filled($row['tag_number'] ?? null) ? trim((string) $row['tag_number']) : null;

        if ($tagNumber === null) {
            if ($livestock === null) {
                // Preview without a livestock group yet: assign a unique display placeholder.
                $tagNumber = 'AUTO-'.$rowNumber;
                $warnings[] = __('Tag number was blank, so one will be assigned on import.');
            } else {
                $tagNumber = $this->generateTagNumber($farm, $livestock);
                $warnings[] = __('Tag number was blank, so :tag was assigned.', ['tag' => $tagNumber]);
            }
        }

        $tagKey = ($livestock?->id ?? 'new:'.mb_strtolower($farmName).':'.mb_strtolower($livestockName)).'|'
            .mb_strtolower($tagNumber);

        if (isset($this->seenTags[$tagKey])) {
            return $this->invalidPreparation([
                "Duplicate tag number \"{$tagNumber}\" in this file (also on row {$this->seenTags[$tagKey]}).",
            ], $warnings);
        }

        $existing = null;

        if ($livestock !== null) {
            $existing = Animal::query()
                ->where('livestock_id', $livestock->id)
                ->where('tag_number', $tagNumber)
                ->first();
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
            $productionStatus === 'Pregnancy'
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

        // Preview of a brand-new livestock group: classify as new without validating FKs yet.
        if ($livestock === null) {
            return [
                'status' => 'new',
                'messages' => [],
                'warnings' => $warnings,
                'tag_number' => $tagNumber,
                'tag_key' => $tagKey,
                'name' => $name,
                'animal_id' => null,
                'existing_name' => null,
                'payload' => null,
            ];
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
            return $this->invalidPreparation($validator->errors()->all(), $warnings);
        }

        $validated = $validator->validated();

        if ($existing !== null) {
            return [
                'status' => 'existing',
                'messages' => [],
                'warnings' => $warnings,
                'tag_number' => $tagNumber,
                'tag_key' => $tagKey,
                'name' => $name,
                'animal_id' => $existing->id,
                'existing_name' => $existing->name,
                'payload' => $validated,
            ];
        }

        return [
            'status' => 'new',
            'messages' => [],
            'warnings' => $warnings,
            'tag_number' => $tagNumber,
            'tag_key' => $tagKey,
            'name' => $name,
            'animal_id' => null,
            'existing_name' => null,
            'payload' => $validated,
        ];
    }

    /**
     * @param  list<string>  $messages
     * @param  list<string>  $warnings
     * @return array{
     *     status: 'invalid',
     *     messages: list<string>,
     *     warnings: list<string>,
     *     tag_number: null,
     *     tag_key: null,
     *     name: null,
     *     animal_id: null,
     *     existing_name: null,
     *     payload: null
     * }
     */
    protected function invalidPreparation(array $messages, array $warnings): array
    {
        return [
            'status' => 'invalid',
            'messages' => array_values($messages),
            'warnings' => $warnings,
            'tag_number' => null,
            'tag_key' => null,
            'name' => null,
            'animal_id' => null,
            'existing_name' => null,
            'payload' => null,
        ];
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
    protected function resolveLivestock(
        Farm $farm,
        string $livestockName,
        array $row,
        array &$warnings,
        bool $allowCreateLivestock
    ): ?Livestock {
        $cacheKey = $farm->id.'|'.mb_strtolower($livestockName);

        if (array_key_exists($cacheKey, $this->livestockCache)) {
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

        if (! $allowCreateLivestock) {
            return $this->livestockCache[$cacheKey] = null;
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
