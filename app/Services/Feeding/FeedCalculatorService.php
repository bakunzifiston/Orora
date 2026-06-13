<?php

namespace App\Services\Feeding;

use App\Models\Animal;
use App\Models\Livestock;
use App\Models\MilkRecord;

class FeedCalculatorService
{
    public function calculateForAnimal(Animal $animal): array
    {
        $animal->loadMissing(['livestock.farm']);

        $type = $this->resolveAnimalType($animal);
        $weight = (float) ($animal->weight_kg ?? 0);
        $status = $this->normalizeProductionStatus($animal->production_status, $type);
        $ageMonths = $this->estimateAgeMonths($animal);
        $warnings = [];

        if (! $rule = $this->getRule($type, $status, $ageMonths, $weight, $animal)) {
            return $this->emptyResult('No feeding rule matches this animal profile.');
        }

        if (! $rule['is_fixed'] && $weight <= 0) {
            $warnings[] = 'Body weight is missing — enter weight on the animal profile for an accurate recommendation.';
        }

        $avgMilkYield = $status === 'lactating' && $type === 'cattle'
            ? $this->averageMilkYield($animal)
            : null;

        $result = $this->buildResult(
            rule: $rule,
            weight: $weight,
            count: 1,
            type: $type,
            label: collect([$animal->tag_number, $animal->name])->filter()->implode(' · '),
            level: 'individual',
        );

        $explanation = $this->buildExplanation(
            rule: $rule,
            type: $type,
            productionStatus: $animal->production_status,
            normalizedStatus: $status,
            ageMonths: $ageMonths,
            weight: $weight,
            totalFeed: $result['total_feed_kg'],
            avgMilkYield: $avgMilkYield,
        );

        return array_merge($result, [
            'animal_id' => $animal->id,
            'tag_number' => $animal->tag_number,
            'animal_name' => $animal->name,
            'production_status' => $animal->production_status,
            'normalized_status' => $status,
            'age_months' => round($ageMonths, 1),
            'age_weeks' => round($ageMonths * 4.345, 1),
            'farm_name' => $animal->farm?->name ?? $animal->livestock?->farm?->name,
            'livestock_name' => $animal->livestock?->name,
            'basis' => $this->basisLabel($rule, $weight, $type),
            'warnings' => $warnings,
            'explanation' => $explanation,
        ]);
    }

    public function calculateForHerd(Livestock $livestock): array
    {
        $livestock->loadMissing('farm');

        $animals = Animal::query()
            ->where('livestock_id', $livestock->id)
            ->where('lifecycle_status', 'Active')
            ->orderBy('tag_number')
            ->get();

        if ($animals->isEmpty()) {
            return $this->emptyResult('No active animals in this herd.');
        }

        $totalRoughage = 0.0;
        $totalConcentrate = 0.0;
        $totalSupplement = 0.0;
        $totalFeed = 0.0;
        $breakdown = [];
        $warnings = [];

        foreach ($animals as $animal) {
            $result = $this->calculateForAnimal($animal);

            if (! ($result['has_data'] ?? false)) {
                continue;
            }

            $totalRoughage += $result['roughage_kg'];
            $totalConcentrate += $result['concentrate_kg'];
            $totalSupplement += $result['supplement_kg'];
            $totalFeed += $result['total_feed_kg'];

            if (! empty($result['warnings'])) {
                $warnings = array_merge($warnings, $result['warnings']);
            }

            $breakdown[] = [
                'tag_number' => $animal->tag_number,
                'animal_name' => $animal->name,
                'weight_kg' => $animal->weight_kg,
                'production_status' => $animal->production_status,
                'rule_label' => $result['explanation']['rule_label'] ?? '—',
                'total_feed_kg' => $result['total_feed_kg'],
                'roughage_kg' => $result['roughage_kg'],
                'concentrate_kg' => $result['concentrate_kg'],
                'supplement_kg' => $result['supplement_kg'],
            ];
        }

        $warnings = array_values(array_unique($warnings));
        $count = count($breakdown);

        if ($count === 0) {
            return $this->emptyResult('Could not calculate feed for any animals in this herd.');
        }

        $typeLabel = $this->livestockTypeLabel($livestock);

        return [
            'level' => 'herd',
            'label' => collect([$typeLabel, $livestock->breed, $livestock->name])->filter()->implode(' · '),
            'farm_name' => $livestock->farm?->name,
            'livestock_name' => $livestock->name,
            'animal_type' => $this->resolveLivestockType($livestock),
            'animal_count' => $count,
            'total_feed_kg' => round($totalFeed, 2),
            'roughage_kg' => round($totalRoughage, 2),
            'concentrate_kg' => round($totalConcentrate, 2),
            'supplement_kg' => round($totalSupplement, 2),
            'roughage_pct' => $totalFeed > 0 ? round($totalRoughage / $totalFeed * 100) : 0,
            'concentrate_pct' => $totalFeed > 0 ? round($totalConcentrate / $totalFeed * 100) : 0,
            'supplement_pct' => $totalFeed > 0 ? round($totalSupplement / $totalFeed * 100) : 0,
            'per_animal_avg' => round($totalFeed / $count, 2),
            'breakdown' => $breakdown,
            'has_data' => true,
            'reason' => null,
            'warnings' => $warnings,
            'basis' => 'Sum of per-animal recommendations (each animal matched to its own rule)',
            'explanation' => [
                'rule_label' => 'Per-animal rules (see breakdown table)',
                'method_label' => 'Each active animal is calculated individually, then totals are summed',
                'inputs' => [
                    'animal_type' => $this->resolveLivestockType($livestock),
                    'animal_count' => $count,
                ],
                'parameters' => [],
                'steps' => [
                    'Load all active animals in the selected livestock group.',
                    'Match each animal to a feed rule using species, weight, age, and production status.',
                    'Sum roughage, concentrate, supplement, and total feed across the herd.',
                ],
            ],
        ];
    }

    public static function rulesReference(): array
    {
        return config('feed_calculator', []);
    }

    private function buildExplanation(
        array $rule,
        string $type,
        ?string $productionStatus,
        string $normalizedStatus,
        float $ageMonths,
        float $weight,
        float $totalFeed,
        ?float $avgMilkYield = null,
    ): array {
        $typeConfig = config("feed_calculator.animal_types.{$type}", []);
        $methodKey = $rule['is_fixed'] ? 'fixed' : 'weight_based';
        $method = config("feed_calculator.calculation_methods.{$methodKey}", []);

        $parameters = [];
        if ($rule['is_fixed']) {
            if (isset($rule['fixed_grams'])) {
                $parameters['Fixed daily amount'] = $rule['fixed_grams'].' g per bird';
            } else {
                $parameters['Fixed daily amount'] = rtrim(rtrim(number_format($rule['fixed_kg'], 2), '0'), '.').' kg per animal';
            }
        } else {
            $parameters['Dry matter % of body weight'] = rtrim(rtrim(number_format($rule['dm_percent'], 1), '0'), '.').'%';
        }
        $parameters['Roughage share'] = $rule['roughage_pct'].'%';
        $parameters['Concentrate share'] = $rule['concentrate_pct'].'%';
        $parameters['Supplement share'] = $rule['supplement_pct'].'%';

        $steps = [];
        if ($rule['is_fixed']) {
            $amount = isset($rule['fixed_grams'])
                ? $rule['fixed_grams'].' g'
                : rtrim(rtrim(number_format($rule['fixed_kg'], 2), '0'), '.').' kg';
            $steps[] = "Apply fixed daily amount: {$amount} per animal.";
            $steps[] = 'Total feed = '.$amount.' = '.number_format($totalFeed, 2).' kg/day.';
        } else {
            $dm = rtrim(rtrim(number_format($rule['dm_percent'], 1), '0'), '.');
            if ($weight > 0) {
                $steps[] = "Total feed = weight ({$weight} kg) × DM% ({$dm}%) ÷ 100 = ".number_format($totalFeed, 2).' kg/day.';
            } else {
                $steps[] = "Formula: weight × {$dm}% ÷ 100 (weight not recorded — result is 0 until weight is added).";
            }
        }
        $steps[] = 'Roughage = '.number_format($totalFeed * $rule['roughage_pct'] / 100, 2).' kg ('.$rule['roughage_pct'].'%).';
        $steps[] = 'Concentrate = '.number_format($totalFeed * $rule['concentrate_pct'] / 100, 2).' kg ('.$rule['concentrate_pct'].'%).';
        $steps[] = 'Supplement = '.number_format($totalFeed * $rule['supplement_pct'] / 100, 2).' kg ('.$rule['supplement_pct'].'%).';

        $inputs = [
            'animal_type' => $type,
            'production_status' => $productionStatus,
            'normalized_status' => $normalizedStatus,
            'age_months' => round($ageMonths, 1),
            'weight_kg' => $weight,
        ];

        if ($avgMilkYield !== null && $avgMilkYield > 0) {
            $inputs['avg_milk_yield_liters'] = round($avgMilkYield, 2);
            $steps[] = 'Lactating tier based on average milk yield: '.number_format($avgMilkYield, 2).' L/day (last 5 records).';
        } elseif ($type === 'cattle' && $normalizedStatus === 'lactating') {
            $steps[] = 'No recent milk records — medium lactation tier (10–20 L) used by default.';
        }

        return [
            'rule_label' => $rule['rule_label'] ?? ucfirst($type).' rule',
            'method_label' => $method['label'] ?? $methodKey,
            'formula' => $method['formula'] ?? null,
            'inputs' => $inputs,
            'parameters' => $parameters,
            'steps' => $steps,
            'config_source' => 'config/feed_calculator.php',
            'type_label' => $typeConfig['label'] ?? ucfirst($type),
        ];
    }

    private function labelRule(array $rule): array
    {
        if (! isset($rule['rule_label'])) {
            $rule['rule_label'] = 'Custom rule';
        }

        return $rule;
    }

    private function getRule(string $type, string $status, float $ageMonths, float $weight, ?Animal $animal = null): ?array
    {
        $rule = match ($type) {
            'cattle' => $this->cattleRule($status, $ageMonths, $weight, $animal),
            'goat' => $this->goatRule($status, $ageMonths),
            'pig' => $this->pigRule($status, $ageMonths),
            'poultry' => $this->poultryRule($status, $ageMonths),
            default => null,
        };

        return $rule ? $this->labelRule($rule) : null;
    }

    private function cattleRule(string $status, float $ageMonths, float $weight, ?Animal $animal): array
    {
        if ($status === 'lactating') {
            $tier = $this->lactatingCattleTier($animal);
            $tierConfig = collect(config('feed_calculator.lactating_cattle_tiers', []))
                ->firstWhere('key', $tier) ?? [];

            return match ($tier) {
                'low' => ['rule_label' => $tierConfig['label'] ?? 'Lactating — low yield', 'dm_percent' => 3.5, 'roughage_pct' => 50, 'concentrate_pct' => 45, 'supplement_pct' => 5, 'is_fixed' => false, 'tier' => 'low'],
                'high' => ['rule_label' => $tierConfig['label'] ?? 'Lactating — high yield', 'dm_percent' => 4.5, 'roughage_pct' => 40, 'concentrate_pct' => 55, 'supplement_pct' => 5, 'is_fixed' => false, 'tier' => 'high'],
                default => ['rule_label' => $tierConfig['label'] ?? 'Lactating — medium yield', 'dm_percent' => 4.0, 'roughage_pct' => 45, 'concentrate_pct' => 50, 'supplement_pct' => 5, 'is_fixed' => false, 'tier' => 'medium'],
            };
        }

        $dmPercent = match (true) {
            $status === 'pregnant' => 2.5,
            $status === 'breeding' => 2.5,
            $status === 'dry' => 2.0,
            $status === 'growing' && $ageMonths < 6 => 3.5,
            $status === 'growing' && $ageMonths < 12 => 3.0,
            $status === 'growing' && $ageMonths < 24 => 2.5,
            default => 2.5,
        };

        [$roughage, $concentrate] = match (true) {
            $status === 'pregnant' => [60, 35],
            $status === 'breeding' => [60, 35],
            $status === 'dry' => [70, 25],
            $status === 'growing' && $ageMonths < 6 => [40, 55],
            $status === 'growing' && $ageMonths < 12 => [50, 45],
            $status === 'growing' && $ageMonths < 24 => [60, 35],
            default => [55, 40],
        };

        return [
            'rule_label' => $this->configRuleLabel('cattle', $status, $ageMonths),
            'dm_percent' => $dmPercent,
            'roughage_pct' => $roughage,
            'concentrate_pct' => $concentrate,
            'supplement_pct' => 5,
            'is_fixed' => false,
        ];
    }

    private function averageMilkYield(?Animal $animal): ?float
    {
        if (! $animal) {
            return null;
        }

        $avg = (float) MilkRecord::query()
            ->where('animal_id', $animal->id)
            ->orderByDesc('id')
            ->limit(5)
            ->avg('yield_liters');

        return $avg > 0 ? $avg : null;
    }

    private function lactatingCattleTier(?Animal $animal): string
    {
        $avgYield = $this->averageMilkYield($animal);

        if ($avgYield === null) {
            return 'medium';
        }

        return match (true) {
            $avgYield < 10 => 'low',
            $avgYield > 20 => 'high',
            default => 'medium',
        };
    }

    private function goatRule(string $status, float $ageMonths): array
    {
        $dmPercent = match (true) {
            $status === 'lactating' => 4.0,
            $status === 'pregnant' => 3.0,
            $status === 'breeding' => 3.0,
            $status === 'dry' => 2.5,
            $status === 'growing' && $ageMonths < 6 => 4.0,
            $status === 'growing' => 3.5,
            default => 3.5,
        };

        [$roughage, $concentrate] = match (true) {
            $status === 'dry' => [70, 25],
            $status === 'lactating' => [50, 45],
            $status === 'pregnant', $status === 'breeding' => [60, 35],
            $status === 'growing' && $ageMonths < 6 => [45, 50],
            default => [55, 40],
        };

        return [
            'rule_label' => $this->configRuleLabel('goat', $status, $ageMonths),
            'dm_percent' => $dmPercent,
            'roughage_pct' => $roughage,
            'concentrate_pct' => $concentrate,
            'supplement_pct' => 5,
            'is_fixed' => false,
        ];
    }

    private function pigRule(string $status, float $ageMonths): array
    {
        $fixedKg = match (true) {
            $status === 'lactating' => 5.0,
            $status === 'pregnant' => 2.5,
            $status === 'breeding' => 2.0,
            $status === 'growing' && $ageMonths < 3 => 0.5,
            $status === 'growing' && $ageMonths < 6 => 1.5,
            default => 2.5,
        };

        [$roughage, $concentrate] = match (true) {
            $status === 'lactating', $status === 'growing' && $ageMonths < 6 => [10, 85],
            default => [15, 80],
        };

        return [
            'rule_label' => $this->configRuleLabel('pig', $status, $ageMonths),
            'dm_percent' => 0,
            'fixed_kg' => $fixedKg,
            'roughage_pct' => $roughage,
            'concentrate_pct' => $concentrate,
            'supplement_pct' => 5,
            'is_fixed' => true,
        ];
    }

    private function poultryRule(string $status, float $ageMonths): array
    {
        $ageWeeks = $ageMonths * 4.345;

        $fixedGrams = match (true) {
            $status === 'broiler' => 150,
            $status === 'breeding' => 130,
            $status === 'laying' => 120,
            $ageWeeks < 4 => 30,
            $ageWeeks < 8 => 80,
            default => 120,
        };

        [$roughage, $concentrate, $supplement] = match (true) {
            $ageWeeks < 4 => [0, 90, 10],
            $status === 'broiler' => [5, 90, 5],
            $status === 'breeding' => [10, 80, 10],
            $status === 'laying' => [10, 80, 10],
            default => [10, 85, 5],
        };

        return [
            'rule_label' => $this->configRuleLabel('poultry', $status, $ageMonths, $ageWeeks),
            'dm_percent' => 0,
            'fixed_kg' => $fixedGrams / 1000,
            'fixed_grams' => $fixedGrams,
            'roughage_pct' => $roughage,
            'concentrate_pct' => $concentrate,
            'supplement_pct' => $supplement,
            'is_fixed' => true,
        ];
    }

    private function buildResult(
        array $rule,
        float $weight,
        int $count,
        string $type,
        string $label,
        string $level,
    ): array {
        if ($rule['is_fixed']) {
            $totalFeed = $rule['fixed_kg'] * $count;
        } else {
            $totalFeed = ($weight * $rule['dm_percent'] / 100) * $count;
        }

        $roughage = round($totalFeed * $rule['roughage_pct'] / 100, 2);
        $concentrate = round($totalFeed * $rule['concentrate_pct'] / 100, 2);
        $supplement = round($totalFeed * $rule['supplement_pct'] / 100, 2);

        return [
            'level' => $level,
            'label' => $label,
            'animal_type' => $type,
            'weight_kg' => $weight,
            'total_feed_kg' => round($totalFeed, 2),
            'roughage_kg' => $roughage,
            'concentrate_kg' => $concentrate,
            'supplement_kg' => $supplement,
            'roughage_pct' => $rule['roughage_pct'],
            'concentrate_pct' => $rule['concentrate_pct'],
            'supplement_pct' => $rule['supplement_pct'],
            'dm_percent' => $rule['dm_percent'] ?? 0,
            'is_fixed' => $rule['is_fixed'],
            'has_data' => true,
            'reason' => null,
            'warnings' => [],
        ];
    }

    private function basisLabel(array $rule, float $weight, string $type): string
    {
        if ($rule['is_fixed']) {
            if ($type === 'poultry' && isset($rule['fixed_grams'])) {
                return sprintf('Fixed amount: %d g per bird per day', $rule['fixed_grams']);
            }

            return sprintf('Fixed amount: %s kg per animal per day', rtrim(rtrim(number_format($rule['fixed_kg'], 2), '0'), '.'));
        }

        if ($weight <= 0) {
            return sprintf('%s%% of body weight (weight not recorded)', rtrim(rtrim(number_format($rule['dm_percent'], 1), '0'), '.'));
        }

        $tierNote = isset($rule['tier'])
            ? ' ('.match ($rule['tier']) {
                'low' => 'low yield <10 L',
                'high' => 'high yield >20 L',
                default => 'medium yield 10–20 L',
            }.')'
            : '';

        return sprintf(
            '%s%% of body weight (%s kg)%s',
            rtrim(rtrim(number_format($rule['dm_percent'], 1), '0'), '.'),
            number_format($weight, 1),
            $tierNote,
        );
    }

    private function resolveAnimalType(Animal $animal): string
    {
        if ($animal->species) {
            return strtolower($animal->species);
        }

        return $this->resolveLivestockType($animal->livestock);
    }

    private function resolveLivestockType(?Livestock $livestock): string
    {
        $types = $livestock?->livestock_types ?? [];
        $first = $types[0] ?? 'Cattle';

        return match (strtolower($first)) {
            'cattle', 'cows', 'dairy cattle', 'beef cattle' => 'cattle',
            'goat', 'goats' => 'goat',
            'pig', 'pigs', 'swine' => 'pig',
            'poultry', 'chicken', 'chickens', 'layers', 'broilers' => 'poultry',
            default => strtolower($first),
        };
    }

    private function livestockTypeLabel(Livestock $livestock): string
    {
        return $livestock->livestock_types_label ?: ucfirst($this->resolveLivestockType($livestock));
    }

    private function normalizeProductionStatus(?string $status, string $type): string
    {
        $status = strtolower(trim((string) $status));

        return match ($status) {
            'lactating' => $type === 'poultry' ? 'laying' : 'lactating',
            'dry' => 'dry',
            'growing' => $type === 'poultry' ? 'growing' : 'growing',
            'breeding' => 'breeding',
            'gestating', 'pregnant' => 'pregnant',
            'fattening' => $type === 'poultry' ? 'broiler' : 'growing',
            default => match ($type) {
                'poultry' => 'growing',
                'pig' => 'growing',
                default => 'dry',
            },
        };
    }

    private function estimateAgeMonths(Animal $animal): float
    {
        if ($animal->date_of_birth) {
            return max(0, $animal->date_of_birth->diffInMonths(now()));
        }

        return 12.0;
    }

    private function emptyResult(string $reason): array
    {
        return [
            'level' => null,
            'label' => null,
            'total_feed_kg' => 0,
            'roughage_kg' => 0,
            'concentrate_kg' => 0,
            'supplement_kg' => 0,
            'has_data' => false,
            'reason' => $reason,
            'warnings' => [],
            'explanation' => null,
        ];
    }

    private function configRuleLabel(string $type, string $status, float $ageMonths, ?float $ageWeeks = null): string
    {
        $rules = config("feed_calculator.animal_types.{$type}.rules", []);

        foreach ($rules as $rule) {
            $ruleStatus = $rule['status'] ?? null;

            if ($ruleStatus !== $status) {
                continue;
            }

            if ($type === 'poultry' && $status === 'growing' && $ageWeeks !== null) {
                if (isset($rule['max_age_weeks']) && $ageWeeks >= $rule['max_age_weeks']) {
                    continue;
                }

                if (isset($rule['min_age_weeks']) && $ageWeeks < $rule['min_age_weeks']) {
                    continue;
                }
            } else {
                if (isset($rule['min_age_months']) && $ageMonths < $rule['min_age_months']) {
                    continue;
                }

                if (isset($rule['max_age_months']) && $ageMonths >= $rule['max_age_months']) {
                    continue;
                }
            }

            return $rule['label'];
        }

        if ($type === 'cattle' && $status === 'lactating') {
            return 'Lactating cattle (milk-yield tier)';
        }

        return ucfirst($type).' — '.$status;
    }
}
