<?php

namespace App\Services\Milk;

use App\Models\Animal;
use App\Services\TenantContext;
use App\Models\Expense;
use App\Models\Farm;
use App\Models\MilkRecord;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;

class MilkCostPerLitreService
{
    /**
     * File (or other non-tagged) store — stancl tenancy wraps the default Cache facade with tags,
     * which the database driver does not support.
     */
    private function cache(): CacheRepository
    {
        return Cache::store('file');
    }
    /**
     * @return array<string, mixed>
     */
    public function calculate(?int $farmId, string $from, string $to): array
    {
        if ($farmId === null) {
            return $this->calculateAllFarms($from, $to);
        }

        return $this->calculateSingleFarm($farmId, $from, $to);
    }

    /**
     * @return array<string, mixed>
     */
    public function daily(?int $farmId, string $date): array
    {
        return $this->cache()->remember(
            $this->cacheKey('daily', $farmId, $date),
            300,
            fn () => $this->calculate($farmId, $date, $date),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function monthly(?int $farmId, int $year, int $month): array
    {
        $from = Carbon::create($year, $month)->startOfMonth()->toDateString();
        $to = Carbon::create($year, $month)->endOfMonth()->toDateString();

        return $this->cache()->remember(
            $this->cacheKey('monthly', $farmId, "{$year}.{$month}"),
            600,
            fn () => $this->calculate($farmId, $from, $to),
        );
    }

    /**
     * @return list<array{month: string, cost_per_litre: float, total_litres: float, has_data: bool}>
     */
    public function trend(?int $farmId, int $months = 6): array
    {
        return collect(range($months - 1, 0))
            ->map(function (int $monthsAgo) use ($farmId) {
                $date = now()->subMonths($monthsAgo);
                $result = $this->monthly($farmId, $date->year, $date->month);

                return [
                    'month' => $date->format('M Y'),
                    'cost_per_litre' => $result['cost_per_litre'],
                    'total_litres' => $result['total_litres'],
                    'has_data' => $result['has_data'],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function perFarmComparison(string $from, string $to): array
    {
        return Farm::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (Farm $farm) => $this->calculateSingleFarm($farm->id, $from, $to))
            ->all();
    }

    public function invalidateCache(?int $farmId, ?string $expenseDate = null): void
    {
        $farmKeys = $farmId ? [$farmId, null] : [null];
        $dates = collect([
            today()->toDateString(),
            today()->subDay()->toDateString(),
        ]);

        if ($expenseDate) {
            $dates->push(Carbon::parse($expenseDate)->toDateString());
        }

        foreach ($farmKeys as $key) {
            foreach ($dates->unique() as $date) {
                $this->cache()->forget($this->cacheKey('daily', $key, $date));
            }

            $now = now();
            $this->cache()->forget($this->cacheKey('monthly', $key, "{$now->year}.{$now->month}"));
            $this->cache()->forget($this->cacheKey('monthly', $key, "{$now->copy()->subMonth()->year}.{$now->copy()->subMonth()->month}"));

            if ($expenseDate) {
                $parsed = Carbon::parse($expenseDate);
                $this->cache()->forget($this->cacheKey('monthly', $key, "{$parsed->year}.{$parsed->month}"));
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateSingleFarm(int $farmId, string $from, string $to): array
    {
        $farm = Farm::query()->find($farmId);

        $totalExpense = (float) Expense::query()
            ->where('status', 'paid')
            ->where('farm_id', $farmId)
            ->whereBetween('expense_date', [$from, $to])
            ->sum('amount');

        $animalQuery = Animal::query()
            ->where('farm_id', $farmId)
            ->where('lifecycle_status', 'Active');

        $producingAnimals = (clone $animalQuery)
            ->where('production_status', 'Lactating')
            ->count();

        $totalAnimals = (clone $animalQuery)->count();

        $denominator = $totalAnimals > 0 ? $totalAnimals : $producingAnimals;

        $totalLitres = (float) MilkRecord::query()
            ->join('milk_sessions', 'milk_records.milk_session_id', '=', 'milk_sessions.id')
            ->where('milk_sessions.status', 'completed')
            ->where('milk_sessions.farm_id', $farmId)
            ->whereBetween('milk_sessions.session_date', [$from, $to])
            ->sum('milk_records.yield_liters');

        if ($totalLitres <= 0 || $producingAnimals <= 0) {
            return $this->emptyResult(
                farmId: $farmId,
                farmName: $farm?->name ?? 'Unknown',
                totalExpense: $totalExpense,
                producingAnimals: $producingAnimals,
                totalAnimals: $totalAnimals,
                totalLitres: $totalLitres,
            );
        }

        $producingRatio = $producingAnimals / $denominator;
        $allocatedExpense = $totalExpense * $producingRatio;
        $costPerLitre = $allocatedExpense / $totalLitres;

        return [
            'farm_id' => $farmId,
            'farm_name' => $farm?->name ?? 'Unknown',
            'cost_per_litre' => round($costPerLitre, 2),
            'total_expense' => $totalExpense,
            'allocated_expense' => round($allocatedExpense, 2),
            'producing_animals' => $producingAnimals,
            'total_animals' => $totalAnimals,
            'producing_ratio' => round($producingRatio * 100, 1),
            'total_litres' => round($totalLitres, 2),
            'currency' => 'RWF',
            'has_data' => true,
            'reason' => null,
            'is_combined' => false,
        ];
    }

    /**
     * Weighted average: SUM(allocated expense) / SUM(litres) across active farms.
     *
     * @return array<string, mixed>
     */
    private function calculateAllFarms(string $from, string $to): array
    {
        $farms = Farm::query()->where('status', 'active')->orderBy('name')->get();

        if ($farms->isEmpty()) {
            return $this->emptyResult(
                farmId: null,
                farmName: 'All farms',
                totalExpense: 0,
                producingAnimals: 0,
                totalAnimals: 0,
                totalLitres: 0,
            );
        }

        $perFarm = [];
        $totalExpenseAll = 0.0;
        $totalAllocatedAll = 0.0;
        $totalLitresAll = 0.0;
        $totalProducing = 0;
        $totalAnimalsAll = 0;

        foreach ($farms as $farm) {
            $result = $this->calculateSingleFarm($farm->id, $from, $to);
            $perFarm[] = $result;

            $totalExpenseAll += (float) $result['total_expense'];
            $totalAllocatedAll += (float) $result['allocated_expense'];
            $totalLitresAll += (float) $result['total_litres'];
            $totalProducing += (int) $result['producing_animals'];
            $totalAnimalsAll += (int) $result['total_animals'];
        }

        $weightedCost = $totalLitresAll > 0
            ? $totalAllocatedAll / $totalLitresAll
            : 0.0;

        return [
            'farm_id' => null,
            'farm_name' => 'All farms',
            'cost_per_litre' => round($weightedCost, 2),
            'total_expense' => $totalExpenseAll,
            'allocated_expense' => round($totalAllocatedAll, 2),
            'producing_animals' => $totalProducing,
            'total_animals' => $totalAnimalsAll,
            'producing_ratio' => $totalAnimalsAll > 0
                ? round(($totalProducing / $totalAnimalsAll) * 100, 1)
                : 0.0,
            'total_litres' => round($totalLitresAll, 2),
            'currency' => 'RWF',
            'has_data' => $totalLitresAll > 0,
            'reason' => $totalLitresAll <= 0 ? 'No milk recorded in this period' : null,
            'is_combined' => true,
            'per_farm' => $perFarm,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyResult(
        ?int $farmId,
        string $farmName,
        float $totalExpense,
        int $producingAnimals,
        int $totalAnimals,
        float $totalLitres,
    ): array {
        return [
            'farm_id' => $farmId,
            'farm_name' => $farmName,
            'cost_per_litre' => 0.0,
            'total_expense' => $totalExpense,
            'allocated_expense' => 0.0,
            'producing_animals' => $producingAnimals,
            'total_animals' => $totalAnimals,
            'producing_ratio' => 0.0,
            'total_litres' => round($totalLitres, 2),
            'currency' => 'RWF',
            'has_data' => false,
            'reason' => $totalLitres <= 0
                ? 'No milk recorded in this period'
                : 'No lactating animals found',
            'is_combined' => $farmId === null,
            'per_farm' => $farmId === null ? [] : null,
        ];
    }

    private function cacheKey(string $period, ?int $farmId, string $suffix): string
    {
        $tenantKey = TenantContext::id() ?? 'central';
        $farmKey = $farmId ?? 'all';

        return "milk.cost.{$tenantKey}.{$period}.{$farmKey}.{$suffix}";
    }
}
