<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\Farm;
use App\Models\Livestock;
use App\Models\MilkSession;
use App\Models\SaleItem;
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
     *     liter_yield: float,
     *     liters_sold: float
     * }
     */
    public function platformStats(array $filters, Carbon $rangeStart, Carbon $rangeEnd, ?array $farmIds = null): array
    {
        if ($farmIds !== null && count($farmIds) === 1) {
            $farm = Farm::query()->find($farmIds[0]);

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
            'liter_yield' => $this->totalLiterYield($filters, null, $farmIds),
            'liters_sold' => $this->totalLitersSold($filters, null, $farmIds),
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
     *     liter_yield: float,
     *     liters_sold: float
     * }
     */
    private function platformStatsForFarm(Farm $farm, array $filters, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $farmStats = $this->farmStats($farm, $filters, $rangeStart, $rangeEnd);
        $inRange = fn ($query) => $query->whereBetween('created_at', [$rangeStart, $rangeEnd]);

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
            'liter_yield' => $farmStats['liter_yield'],
            'liters_sold' => $farmStats['liters_sold'],
        ];
    }

    /**
     * @return array{
     *     livestock_groups: int,
     *     head_count: int,
     *     animals: int,
     *     certificates: int,
     *     sales: int,
     *     liter_yield: float,
     *     liters_sold: float
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
            'certificates' => Schema::hasTable('certificates')
                ? $this->safeCount(
                    \App\Models\Certificate::class,
                    fn ($query) => $inRange($query)->where('farm_id', $farm->id),
                )
                : 0,
            'sales' => Schema::hasTable('sale_transactions')
                ? $this->safeCount(
                    \App\Models\SaleTransaction::class,
                    fn ($query) => $inRange($query)->where('farm_id', $farm->id),
                )
                : 0,
            'liter_yield' => $this->totalLiterYield($filters, $farm->id),
            'liters_sold' => $this->totalLitersSold($filters, $farm->id),
        ];
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
