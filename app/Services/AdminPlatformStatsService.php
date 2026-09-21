<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\EggCollection;
use App\Models\Farm;
use App\Models\Flock;
use App\Models\Livestock;
use App\Models\MilkSession;
use App\Models\SaleItem;
use App\Models\SaleTransaction;
use App\Models\TenantAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class AdminPlatformStatsService
{
    /**
     * @param  array{from: string, to: string, farm_id?: ?int, province_code?: ?int, district_code?: ?int}  $filters
     * @param  list<int>|null  $farmIds
     * @return array{
     *     accounts_without_farm: int,
     *     users_with_farm: int,
     *     farms: int,
     *     livestock_groups: int,
     *     head_count: int,
     *     animals: int,
     *     flocks: int,
     *     birds: int,
     *     liter_yield: float,
     *     liters_sold: float,
     *     eggs_collected: int,
     *     eggs_sold: float,
     *     animals_sold: int,
     *     cattle_farms: int,
     *     poultry_farms: int,
     *     primary_species: ?string
     * }
     */
    public function platformStats(array $filters, Carbon $rangeStart, Carbon $rangeEnd, ?array $farmIds = null): array
    {
        if ($farmIds !== null && count($farmIds) === 1) {
            $farm = Farm::query()->withoutGlobalScope('tenant')->find($farmIds[0]);

            if ($farm) {
                return $this->platformStatsForFarm($farm, $filters, $rangeStart, $rangeEnd);
            }
        }

        $farmsReady = Schema::hasTable('farms');
        $inRange = fn ($query) => $query->whereBetween('created_at', [$rangeStart, $rangeEnd]);
        $scopeFarms = fn ($query) => $this->applyFarmIds($query, $farmIds);

        return [
            'accounts_without_farm' => ($farmIds === null && $farmsReady)
                ? $this->safeQuery(
                    TenantAccount::class,
                    fn ($query) => $inRange($query)->whereNotExists(function ($subquery) {
                        $subquery->selectRaw('1')
                            ->from('farms')
                            ->whereColumn('farms.tenant_id', 'tenant_accounts.tenant_id');
                    })->count(),
                    0,
                )
                : ($farmIds === null
                    ? $this->safeCount(TenantAccount::class, $inRange)
                    : 0),
            'users_with_farm' => $farmsReady
                ? $this->safeQuery(
                    User::class,
                    fn ($query) => $inRange($query)->whereExists(function ($subquery) use ($farmIds) {
                        $subquery->selectRaw('1')
                            ->from('farms')
                            ->whereColumn('farms.tenant_id', 'users.tenant_id');

                        if ($farmIds !== null) {
                            $subquery->whereIn('farms.id', $farmIds);
                        }
                    })->count(),
                    0,
                )
                : 0,
            'farms' => $this->safeCount(
                Farm::class,
                fn ($query) => $scopeFarms($inRange($query)),
            ),
            'livestock_groups' => $this->safeCount(
                Livestock::class,
                fn ($query) => $scopeFarms($inRange($query)),
            ),
            'head_count' => (int) $this->safeQuery(
                Livestock::class,
                fn ($query) => (float) $scopeFarms($inRange($query))->sum('head_count'),
                0,
            ),
            'animals' => $this->safeCount(
                Animal::class,
                fn ($query) => $scopeFarms($inRange($query)),
            ),
            'flocks' => $this->safeCount(
                Flock::class,
                fn ($query) => $scopeFarms($inRange($query)),
            ),
            'birds' => $this->totalBirds(null, $farmIds),
            'liter_yield' => $this->totalLiterYield($filters, null, $farmIds),
            'liters_sold' => $this->totalLitersSold($filters, null, $farmIds),
            'eggs_collected' => $this->totalEggsCollected($filters, null, $farmIds),
            'eggs_sold' => $this->totalEggsSold($filters, null, $farmIds),
            'animals_sold' => $this->totalAnimalsSold($filters, null, $farmIds),
            'cattle_farms' => $this->countFarmsBySpecies('cattle', $rangeStart, $rangeEnd, $farmIds),
            'poultry_farms' => $this->countFarmsBySpecies('poultry', $rangeStart, $rangeEnd, $farmIds),
            'primary_species' => null,
        ];
    }

    /**
     * @return array{
     *     accounts_without_farm: int,
     *     users_with_farm: int,
     *     farms: int,
     *     livestock_groups: int,
     *     head_count: int,
     *     animals: int,
     *     flocks: int,
     *     birds: int,
     *     liter_yield: float,
     *     liters_sold: float,
     *     eggs_collected: int,
     *     eggs_sold: float,
     *     animals_sold: int,
     *     cattle_farms: int,
     *     poultry_farms: int,
     *     primary_species: ?string
     * }
     */
    private function platformStatsForFarm(Farm $farm, array $filters, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $farmStats = $this->farmStats($farm, $filters, $rangeStart, $rangeEnd);
        $inRange = fn ($query) => $query->whereBetween('created_at', [$rangeStart, $rangeEnd]);
        $isPoultry = $farm->isPoultry();

        return [
            'accounts_without_farm' => 0,
            'users_with_farm' => 0,
            'farms' => $this->safeCount(
                Farm::class,
                fn ($query) => $inRange($query)->whereKey($farm->id),
            ),
            'livestock_groups' => $farmStats['livestock_groups'],
            'head_count' => $farmStats['head_count'],
            'animals' => $farmStats['animals'],
            'flocks' => $farmStats['flocks'],
            'birds' => $farmStats['birds'],
            'liter_yield' => $farmStats['liter_yield'],
            'liters_sold' => $farmStats['liters_sold'],
            'eggs_collected' => $farmStats['eggs_collected'],
            'eggs_sold' => $farmStats['eggs_sold'],
            'animals_sold' => $farmStats['animals_sold'],
            'cattle_farms' => $isPoultry ? 0 : 1,
            'poultry_farms' => $isPoultry ? 1 : 0,
            'primary_species' => $farm->primary_species,
        ];
    }

    /**
     * @return array{
     *     livestock_groups: int,
     *     head_count: int,
     *     animals: int,
     *     flocks: int,
     *     birds: int,
     *     certificates: int,
     *     sales: int,
     *     liter_yield: float,
     *     liters_sold: float,
     *     eggs_collected: int,
     *     eggs_sold: float,
     *     animals_sold: int,
     *     primary_species: ?string
     * }
     */
    public function farmStats(Farm $farm, array $filters, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $inRange = fn ($query) => $query->whereBetween('created_at', [$rangeStart, $rangeEnd]);

        return [
            'livestock_groups' => $this->safeCount(
                Livestock::class,
                fn ($query) => $inRange($query)->where('farm_id', $farm->id),
            ),
            'head_count' => (int) $this->safeQuery(
                Livestock::class,
                fn ($query) => (float) $inRange($query)->where('farm_id', $farm->id)->sum('head_count'),
                0,
            ),
            'animals' => $this->safeCount(
                Animal::class,
                fn ($query) => $inRange($query)->where('farm_id', $farm->id),
            ),
            'flocks' => $this->safeCount(
                Flock::class,
                fn ($query) => $inRange($query)->where('farm_id', $farm->id),
            ),
            'birds' => $this->totalBirds($farm->id),
            'certificates' => Schema::hasTable('certificates')
                ? $this->safeCount(
                    \App\Models\Certificate::class,
                    fn ($query) => $inRange($query)->where('farm_id', $farm->id),
                )
                : 0,
            'sales' => Schema::hasTable('sale_transactions')
                ? $this->safeCount(
                    SaleTransaction::class,
                    fn ($query) => $inRange($query)->where('farm_id', $farm->id),
                )
                : 0,
            'liter_yield' => $this->totalLiterYield($filters, $farm->id),
            'liters_sold' => $this->totalLitersSold($filters, $farm->id),
            'eggs_collected' => $this->totalEggsCollected($filters, $farm->id),
            'eggs_sold' => $this->totalEggsSold($filters, $farm->id),
            'animals_sold' => $this->totalAnimalsSold($filters, $farm->id),
            'primary_species' => $farm->primary_species,
        ];
    }

    /**
     * @param  list<int>|null  $farmIds
     */
    public function totalBirds(?int $farmId = null, ?array $farmIds = null): int
    {
        return (int) $this->safeQuery(
            Flock::class,
            fn ($query) => (float) $this->applyFarmIds(
                $query->when($farmId, fn ($q) => $q->where('farm_id', $farmId)),
                $farmId ? null : $farmIds,
            )->sum('current_count'),
            0,
        );
    }

    /**
     * @param  array{from: string, to: string}  $filters
     * @param  list<int>|null  $farmIds
     */
    public function totalEggsCollected(array $filters, ?int $farmId = null, ?array $farmIds = null): int
    {
        return (int) $this->safeQuery(
            EggCollection::class,
            fn ($query) => (float) $this->applyFarmIds(
                $query
                    ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
                    ->whereBetween('collected_on', [$filters['from'], $filters['to']]),
                $farmId ? null : $farmIds,
            )->sum('eggs_count'),
            0,
        );
    }

    /**
     * @param  array{from: string, to: string}  $filters
     * @param  list<int>|null  $farmIds
     */
    public function totalAnimalsSold(array $filters, ?int $farmId = null, ?array $farmIds = null): int
    {
        return (int) $this->safeQuery(
            SaleItem::class,
            fn ($query) => (float) $this->applyFarmIds(
                $query
                    ->join('sale_transactions', 'sale_items.sale_transaction_id', '=', 'sale_transactions.id')
                    ->where('sale_transactions.sale_type', 'animal_sale')
                    ->where('sale_transactions.sale_status', 'completed')
                    ->whereIn('sale_items.item_type', ['animal', 'flock_birds'])
                    ->when($farmId, fn ($q) => $q->where('sale_transactions.farm_id', $farmId))
                    ->whereBetween('sale_transactions.sale_date', [$filters['from'], $filters['to']]),
                $farmId ? null : $farmIds,
                'sale_transactions.farm_id',
            )->sum('sale_items.quantity'),
            0,
        );
    }

    /**
     * @param  list<int>|null  $farmIds
     */
    public function totalEggsSold(array $filters, ?int $farmId = null, ?array $farmIds = null): float
    {
        return (float) $this->safeQuery(
            SaleItem::class,
            fn ($query) => (float) $this->applyFarmIds(
                $query
                    ->join('sale_transactions', 'sale_items.sale_transaction_id', '=', 'sale_transactions.id')
                    ->where('sale_transactions.sale_type', 'egg_sale')
                    ->where('sale_transactions.sale_status', 'completed')
                    ->when($farmId, fn ($q) => $q->where('sale_transactions.farm_id', $farmId))
                    ->whereBetween('sale_transactions.sale_date', [$filters['from'], $filters['to']]),
                $farmId ? null : $farmIds,
                'sale_transactions.farm_id',
            )->sum('sale_items.quantity'),
            0.0,
        );
    }

    /**
     * @param  list<int>|null  $farmIds
     */
    private function countFarmsBySpecies(string $species, Carbon $rangeStart, Carbon $rangeEnd, ?array $farmIds = null): int
    {
        return $this->safeCount(
            Farm::class,
            function ($query) use ($species, $rangeStart, $rangeEnd, $farmIds) {
                $query
                    ->whereBetween('created_at', [$rangeStart, $rangeEnd])
                    ->when($species === 'poultry', fn ($q) => $q->where('primary_species', 'poultry'))
                    ->when($species === 'cattle', fn ($q) => $q->where(function ($inner) {
                        $inner->where('primary_species', 'cattle')
                            ->orWhereNull('primary_species')
                            ->orWhere('primary_species', '');
                    }));

                $this->applyFarmIds($query, $farmIds, 'id');
            },
        );
    }

    /**
     * @param  array{from: string, to: string}  $filters
     * @param  list<int>|null  $farmIds
     */
    public function totalLiterYield(array $filters, ?int $farmId = null, ?array $farmIds = null): float
    {
        return (float) $this->safeQuery(
            MilkSession::class,
            fn ($query) => (float) $this->applyFarmIds(
                $query
                    ->where('status', 'completed')
                    ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
                    ->whereBetween('session_date', [$filters['from'], $filters['to']]),
                $farmId ? null : $farmIds,
            )->sum('total_yield_liters'),
            0.0,
        );
    }

    /**
     * @param  array{from: string, to: string}  $filters
     * @param  list<int>|null  $farmIds
     */
    public function totalLitersSold(array $filters, ?int $farmId = null, ?array $farmIds = null): float
    {
        return (float) $this->safeQuery(
            SaleItem::class,
            fn ($query) => (float) $this->applyFarmIds(
                $query
                    ->join('sale_transactions', 'sale_items.sale_transaction_id', '=', 'sale_transactions.id')
                    ->where('sale_transactions.sale_type', 'milk_sale')
                    ->where('sale_transactions.sale_status', 'completed')
                    ->when($farmId, fn ($q) => $q->where('sale_transactions.farm_id', $farmId))
                    ->whereBetween('sale_transactions.sale_date', [$filters['from'], $filters['to']]),
                $farmId ? null : $farmIds,
                'sale_transactions.farm_id',
            )->sum('sale_items.quantity'),
            0.0,
        );
    }

    /**
     * @param  list<int>|null  $farmIds
     */
    private function applyFarmIds($query, ?array $farmIds, string $column = 'farm_id')
    {
        if ($farmIds === null) {
            return $query;
        }

        if ($farmIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($column, $farmIds);
    }

    private function safeCount(string $model, ?callable $callback = null): int
    {
        return $this->safeQuery($model, function ($query) use ($callback) {
            if ($callback) {
                $callback($query);
            }

            return $query->count();
        }, 0);
    }

    private function safeQuery(string $model, callable $callback, mixed $default): mixed
    {
        try {
            $table = (new $model)->getTable();

            if (! Schema::connection((new $model)->getConnectionName())->hasTable($table)) {
                return $default;
            }

            return $callback($model::query());
        } catch (\Throwable) {
            return $default;
        }
    }
}
