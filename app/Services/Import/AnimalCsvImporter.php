<?php

namespace App\Services\Import;

use App\Models\Animal;
use App\Models\Farm;
use App\Models\Livestock;
use App\Services\Import\Concerns\NormalizesDates;
use App\Services\Import\Concerns\ParsesCsv;
use App\Services\ImportExport\AnimalCsvSchema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Throwable;

class AnimalCsvImporter
{
    use NormalizesDates;
    use ParsesCsv;

    public const MAX_ROWS = 2000;

    /**
     * @return array{created: int, failed: int, errors: list<array{row: int, message: string}>, warnings: list<array{row: int, message: string}>}
     */
    public function import(UploadedFile $file): array
    {
        $created = 0;
        $failed = 0;
        $errors = [];
        $warnings = [];

        try {
            // Parsing errors surface lazily, so the rows have to be pulled
            // inside the try. Reading them up front also lets the date
            // convention be settled from the file as a whole.
            $rows = iterator_to_array($this->parseCsvRows($file, AnimalCsvSchema::headers(), self::MAX_ROWS));

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
                'errors' => [['row' => 0, 'message' => $e->getMessage()]],
                'warnings' => [],
            ];
        }

        foreach ($rows as $rowNumber => $row) {
            try {
                [$attributes, $rowWarnings] = $this->attributesForRow($row);
                Animal::create($attributes);
                $created++;

                foreach ($rowWarnings as $message) {
                    $warnings[] = ['row' => $rowNumber, 'message' => $message];
                }
            } catch (Throwable $e) {
                $failed++;
                $errors[] = [
                    'row' => $rowNumber,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return compact('created', 'failed', 'errors', 'warnings');
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array{0: array<string, mixed>, 1: list<string>}
     */
    protected function attributesForRow(array $row): array
    {
        $farm = $this->resolveFarm($row['farm_name'] ?? null);
        $livestock = $this->resolveLivestock($farm, $row['livestock_name'] ?? null);

        $warnings = [];
        $tagNumber = $row['tag_number'] ?? null;

        if ($tagNumber === null) {
            $tagNumber = $this->generateTagNumber($farm, $livestock);
            $warnings[] = __('Tag number was blank, so :tag was assigned.', ['tag' => $tagNumber]);
        }

        $dateOfBirth = $this->normalizeImportDate($row['date_of_birth'] ?? null);

        if ($this->isNormalizedDate($dateOfBirth) && $dateOfBirth > now()->toDateString()) {
            $warnings[] = __('Date of birth :date is in the future, so it was left blank.', ['date' => $dateOfBirth]);
            $dateOfBirth = null;
        }

        $payload = [
            'farm_id' => $farm->id,
            'livestock_id' => $livestock->id,
            'tag_number' => $tagNumber,
            'name' => $row['name'] ?? null,
            'gender' => isset($row['gender']) ? strtolower((string) $row['gender']) : null,
            'health_status' => $row['health_status'] ?? null,
            'lifecycle_status' => $row['lifecycle_status'] ?? null,
            'date_of_birth' => $dateOfBirth,
            'weight_kg' => $row['weight_kg'] ?? null,
            'color_markings' => $row['color_markings'] ?? null,
            'species' => $row['species'] ?? null,
            'breed' => $row['breed'] ?? null,
            'acquisition_type' => $row['acquisition_type'] ?? null,
            'acquisition_date' => $this->normalizeImportDate($row['acquisition_date'] ?? null),
            'source' => $row['source'] ?? null,
            'mother_tag' => $row['mother_tag'] ?? null,
            'father_tag' => $row['father_tag'] ?? null,
            'production_status' => $row['production_status'] ?? null,
            'current_condition' => $row['current_condition'] ?? null,
            'notes' => $row['notes'] ?? null,
        ];

        $validator = Validator::make($payload, [
            'farm_id' => ['required', 'exists:farms,id'],
            'livestock_id' => [
                'required',
                Rule::exists('livestock', 'id')->where(fn ($query) => $query->where('farm_id', $farm->id)),
            ],
            'tag_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('animals', 'tag_number')
                    ->where(fn ($query) => $query->where('livestock_id', $livestock->id)),
            ],
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
            throw new InvalidArgumentException($validator->errors()->first());
        }

        return [$validator->validated(), $warnings];
    }

    /**
     * Tag numbers are required and unique per livestock group, so rows without
     * one get the next free sequence number for that group.
     */
    protected function generateTagNumber(Farm $farm, Livestock $livestock): string
    {
        $letters = preg_replace('/[^A-Za-z]/', '', (string) $farm->name) ?? '';
        $prefix = $letters === '' ? 'ANM' : strtoupper(substr($letters, 0, 3));

        $sequence = Animal::query()->where('livestock_id', $livestock->id)->count() + 1;

        while (true) {
            $candidate = sprintf('%s-%04d', $prefix, $sequence);

            $taken = Animal::query()
                ->where('livestock_id', $livestock->id)
                ->where('tag_number', $candidate)
                ->exists();

            if (! $taken) {
                return $candidate;
            }

            $sequence++;
        }
    }

    protected function resolveFarm(?string $farmName): Farm
    {
        if ($farmName === null || $farmName === '') {
            throw new InvalidArgumentException('Farm name is required.');
        }

        $farms = Farm::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($farmName)])
            ->get();

        if ($farms->isEmpty()) {
            throw new InvalidArgumentException("Farm \"{$farmName}\" was not found.");
        }

        if ($farms->count() > 1) {
            throw new InvalidArgumentException("Farm name \"{$farmName}\" matches more than one farm.");
        }

        return $farms->first();
    }

    protected function resolveLivestock(Farm $farm, ?string $livestockName): Livestock
    {
        if ($livestockName === null || $livestockName === '') {
            throw new InvalidArgumentException('Livestock group name is required.');
        }

        $groups = Livestock::query()
            ->where('farm_id', $farm->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($livestockName)])
            ->get();

        if ($groups->isEmpty()) {
            throw new InvalidArgumentException(
                "Livestock group \"{$livestockName}\" was not found on farm \"{$farm->name}\"."
            );
        }

        if ($groups->count() > 1) {
            throw new InvalidArgumentException(
                "Livestock group \"{$livestockName}\" matches more than one group on farm \"{$farm->name}\"."
            );
        }

        return $groups->first();
    }
}
