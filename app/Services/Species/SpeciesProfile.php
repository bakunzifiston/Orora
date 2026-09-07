<?php

namespace App\Services\Species;

use App\Models\Farm;
use Illuminate\Support\Collection;

class SpeciesProfile
{
    public function __construct(private readonly ActiveFarmContext $farms) {}

    public function defaultKey(): string
    {
        return (string) config('species.default', 'cattle');
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_values(config('species.keys', ['cattle', 'poultry']));
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return config('species.labels', [
            'cattle' => 'Cattle / dairy',
            'poultry' => 'Poultry',
        ]);
    }

    public function key(?Farm $farm = null): string
    {
        $farm ??= $this->farms->farm();
        $key = $farm?->primary_species ?: $this->inferredTenantKey();

        return in_array($key, $this->keys(), true) ? $key : $this->defaultKey();
    }

    public function isPoultry(?Farm $farm = null): bool
    {
        return $this->key($farm) === 'poultry';
    }

    public function isCattle(?Farm $farm = null): bool
    {
        return $this->key($farm) === 'cattle';
    }

    public function stockUnit(?Farm $farm = null): string
    {
        return (string) $this->profile($farm)['stock_unit'];
    }

    /**
     * @return array<string, mixed>
     */
    public function profile(?Farm $farm = null): array
    {
        $key = $this->key($farm);
        $profiles = config('species.profiles', []);

        return $profiles[$key] ?? $profiles[$this->defaultKey()] ?? [];
    }

    /**
     * @return list<string>
     */
    public function modules(?Farm $farm = null): array
    {
        return array_values($this->profile($farm)['modules'] ?? []);
    }

    public function allows(string $moduleKey, ?Farm $farm = null): bool
    {
        return in_array($moduleKey, $this->modules($farm), true);
    }

    /**
     * @return list<string>
     */
    public function saleTypes(?Farm $farm = null): array
    {
        return array_values($this->profile($farm)['sale_types'] ?? config('modules.sale_types', []));
    }

    /**
     * @return list<string>
     */
    public function herdGroups(?Farm $farm = null): array
    {
        return array_values($this->profile($farm)['herd_groups'] ?? config('modules.herd_groups', []));
    }

    /**
     * @return list<string>
     */
    public function livestockTypes(?Farm $farm = null): array
    {
        return array_values($this->profile($farm)['livestock_types'] ?? config('modules.livestock_types', []));
    }

    /**
     * @return list<string>
     */
    public function productionPurposes(?Farm $farm = null): array
    {
        return array_values($this->profile($farm)['production_purposes'] ?? config('modules.production_purposes', []));
    }

    /**
     * @return list<string>
     */
    public function farmingMethods(?Farm $farm = null): array
    {
        return array_values($this->profile($farm)['farming_methods'] ?? config('modules.farming_methods', []));
    }

    /**
     * @return list<string>
     */
    public function feedingMethods(?Farm $farm = null): array
    {
        return array_values($this->profile($farm)['feeding_methods'] ?? config('modules.feeding_methods', []));
    }

    /**
     * @return array<string, string>
     */
    public function copy(?Farm $farm = null): array
    {
        return $this->profile($farm)['labels'] ?? [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function navigationGroups(?Farm $farm = null): array
    {
        $allowed = $this->modules($farm);
        $hiddenSales = $this->profile($farm)['hidden_sale_sections'] ?? [];

        return collect(config('modules.navigation_groups', []))
            ->map(function (array $group) use ($allowed, $hiddenSales) {
                $group['items'] = collect($group['items'] ?? [])
                    ->filter(function (array $item) use ($allowed, $hiddenSales) {
                        $key = $item['key'] ?? '';

                        if ($key === 'abattoir' || ($item['route'] ?? '') === 'sales.abattoir') {
                            return ! in_array('abattoir', $hiddenSales, true);
                        }

                        return in_array($key, $allowed, true);
                    })
                    ->values()
                    ->all();

                return $group;
            })
            ->filter(fn (array $group) => ($group['items'] ?? []) !== [])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function navigation(?Farm $farm = null): array
    {
        return collect($this->navigationGroups($farm))
            ->flatMap(fn (array $group) => $group['items'] ?? [])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function saleSections(?Farm $farm = null): array
    {
        $hidden = $this->profile($farm)['hidden_sale_sections'] ?? [];

        return collect(config('modules.sale_sections', []))
            ->reject(fn (array $section) => in_array($section['key'] ?? '', $hidden, true))
            ->values()
            ->all();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function catalogsByFarm(Collection $farms): array
    {
        return $farms->mapWithKeys(fn (Farm $farm) => [
            (string) $farm->id => [
                'species' => $this->key($farm),
                'herd_groups' => $this->herdGroups($farm),
                'livestock_types' => $this->livestockTypes($farm),
                'production_purposes' => $this->productionPurposes($farm),
                'farming_methods' => $this->farmingMethods($farm),
                'feeding_methods' => $this->feedingMethods($farm),
                'labels' => $this->copy($farm),
            ],
        ])->all();
    }

    public function moduleForRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        $map = config('species.route_modules', []);

        foreach ($map as $prefix => $module) {
            if ($routeName === $prefix || str_starts_with($routeName, $prefix.'.')) {
                return $module;
            }
        }

        return null;
    }

    private function inferredTenantKey(): string
    {
        $farms = $this->farms->tenantFarms();

        if ($farms->isEmpty()) {
            return $this->defaultKey();
        }

        $keys = $farms->pluck('primary_species')->unique()->filter()->values();

        if ($keys->count() === 1 && in_array($keys->first(), $this->keys(), true)) {
            return $keys->first();
        }

        return $this->defaultKey();
    }
}
