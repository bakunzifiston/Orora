<?php

namespace App\Services\Import\Concerns;

trait NormalizesAnimalStatuses
{
    /**
     * Spreadsheet wording mapped to Orora production_status values.
     *
     * @var array<string, string>
     */
    protected array $productionStatusAliases = [
        'pregnancy' => 'Pregnancy',
        'pregnant' => 'Pregnancy',
        'pregnancy cow' => 'Pregnancy',
        'pregnant cow' => 'Pregnancy',
        'gestating' => 'Pregnancy',
        'gestating cow' => 'Pregnancy',
        'in calf' => 'Pregnancy',
        'with calf' => 'Pregnancy',
        'milking' => 'Lactating',
        'lactating cow' => 'Lactating',
        'in milk' => 'Lactating',
        'dry cow' => 'Dry',
        'not applicable' => 'Not applicable',
        'n/a' => 'Not applicable',
        'na' => 'Not applicable',
    ];

    /**
     * Spreadsheet wording mapped to Orora health_status values.
     *
     * @var array<string, string>
     */
    protected array $healthStatusAliases = [
        'pregnancy' => 'Pregnant',
        'pregnant' => 'Pregnant',
        'pregnancy cow' => 'Pregnant',
        'pregnant cow' => 'Pregnant',
        'gestating' => 'Pregnant',
        'in calf' => 'Pregnant',
        'ok' => 'Healthy',
        'well' => 'Healthy',
        'treatment' => 'Under treatment',
        'under treatment' => 'Under treatment',
        'quarantine' => 'Quarantined',
        'dead' => 'Deceased',
        'died' => 'Deceased',
    ];

    /**
     * @var array<string, string>
     */
    protected array $lifecycleStatusAliases = [
        'alive' => 'Active',
        'live' => 'Active',
        'dead' => 'Deceased',
        'died' => 'Deceased',
        'death' => 'Deceased',
        'sold out' => 'Sold',
        'moved' => 'Transferred',
        'transfer' => 'Transferred',
    ];

    /**
     * @var array<string, string>
     */
    protected array $currentConditionAliases = [
        'ok' => 'Good',
        'fine' => 'Good',
        'average' => 'Fair',
        'bad' => 'Poor',
        'observation' => 'Under observation',
        'under observation' => 'Under observation',
    ];

    /**
     * @var array<string, string>
     */
    protected array $acquisitionTypeAliases = [
        'born' => 'Born on farm',
        'born on farm' => 'Born on farm',
        'born in the farm' => 'Born on farm',
        'birth' => 'Born on farm',
        'purchase' => 'Purchased',
        'bought' => 'Purchased',
        'gifted' => 'Gift',
        'transferred' => 'Transfer',
        'loaned' => 'Loan',
    ];

    /**
     * @return array{0: string|null, 1: string|null}
     */
    protected function normalizeHealthAndProduction(?string $health, ?string $production): array
    {
        $normalizedHealth = $this->normalizeListedValue(
            $health,
            config('modules.health_statuses'),
            $this->healthStatusAliases,
        );

        $normalizedProduction = $this->normalizeListedValue(
            $production,
            config('modules.production_statuses'),
            $this->productionStatusAliases,
        );

        // Spreadsheets often put pregnancy only in production_status. The Animals
        // Health filter looks at health_status, so promote Healthy → Pregnant.
        if ($normalizedProduction === 'Pregnancy' && in_array($normalizedHealth, [null, 'Healthy'], true)) {
            $normalizedHealth = 'Pregnant';
        }

        return [$normalizedHealth, $normalizedProduction];
    }

    protected function normalizeLifecycleStatus(?string $value): ?string
    {
        return $this->normalizeListedValue(
            $value,
            config('modules.lifecycle_statuses'),
            $this->lifecycleStatusAliases,
        );
    }

    protected function normalizeCurrentCondition(?string $value): ?string
    {
        return $this->normalizeListedValue(
            $value,
            config('modules.current_conditions'),
            $this->currentConditionAliases,
        );
    }

    protected function normalizeAcquisitionType(?string $value): ?string
    {
        return $this->normalizeListedValue(
            $value,
            config('modules.acquisition_types'),
            $this->acquisitionTypeAliases,
        );
    }

    /**
     * @param  list<string>  $allowed
     * @param  array<string, string>  $aliases
     */
    protected function normalizeListedValue(?string $value, array $allowed, array $aliases): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        foreach ($allowed as $option) {
            if (strcasecmp($trimmed, $option) === 0) {
                return $option;
            }
        }

        $key = $this->aliasKey($trimmed);

        return $aliases[$key] ?? $trimmed;
    }

    protected function aliasKey(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return $value;
    }
}
