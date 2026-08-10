<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\MilkSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Models\Expense;
use App\Models\Farm;
use App\Models\MilkRecord;
use App\Models\MilkSession;
use App\Services\Milk\MilkCostPerLitreService;
use App\Services\MilkOverviewAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MilkModuleController extends Controller
{
    use MilkSectionViews;
    use ProvidesModuleNavigation;

    public function overview(
        Request $request,
        MilkOverviewAnalyticsService $analytics,
        MilkCostPerLitreService $costPerLitre,
    ): View {
        $selectedFarm = $request->filled('farm_id') ? $request->integer('farm_id') : null;
        $farmId = $selectedFarm;
        $period = $this->resolvePeriod($request->input('period', 'all'));
        [$rangeStart, $rangeEnd, $periodLabel] = $this->periodRange($period);

        $completedInPeriod = MilkSession::query()
            ->where('status', 'completed')
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->when(
                $rangeStart && $rangeEnd,
                fn ($q) => $q->whereBetween('session_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()]),
            );

        $stats = [
            'period_total' => (float) (clone $completedInPeriod)->sum('total_yield_liters'),
            'period_sessions' => (clone $completedInPeriod)->count(),
            'animals_milked' => MilkRecord::query()
                ->whereHas('session', fn ($q) => $q
                    ->where('status', 'completed')
                    ->when($farmId, fn ($sq) => $sq->where('farm_id', $farmId))
                    ->when(
                        $rangeStart && $rangeEnd,
                        fn ($sq) => $sq->whereBetween('session_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()]),
                    ))
                ->distinct('animal_id')
                ->count('animal_id'),
            'avg_per_session' => 0.0,
        ];

        if ($stats['period_sessions'] > 0) {
            $stats['avg_per_session'] = round($stats['period_total'] / $stats['period_sessions'], 2);
        }

        $today = now()->toDateString();
        $stats['today_total'] = (float) MilkSession::query()
            ->where('status', 'completed')
            ->whereDate('session_date', $today)
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->sum('total_yield_liters');
        $stats['today_sessions'] = MilkSession::query()
            ->where('status', 'completed')
            ->whereDate('session_date', $today)
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->count();

        $recentSessions = MilkSession::query()
            ->with(['farm', 'livestock'])
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $topProducers = MilkRecord::query()
            ->join('milk_sessions', 'milk_records.milk_session_id', '=', 'milk_sessions.id')
            ->join('animals', 'milk_records.animal_id', '=', 'animals.id')
            ->where('milk_sessions.status', 'completed')
            ->when($farmId, fn ($q) => $q->where('milk_sessions.farm_id', $farmId))
            ->when(
                $rangeStart && $rangeEnd,
                fn ($q) => $q->whereBetween('milk_sessions.session_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()]),
            )
            ->select('animals.tag_number', 'animals.name', DB::raw('SUM(milk_records.yield_liters) as total'))
            ->groupBy('animals.id', 'animals.tag_number', 'animals.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $byShift = MilkSession::query()
            ->where('status', 'completed')
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->when(
                $rangeStart && $rangeEnd,
                fn ($q) => $q->whereBetween('session_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()]),
                fn ($q) => $q->whereDate('session_date', $today),
            )
            ->select('session_shift', DB::raw('SUM(total_yield_liters) as total'))
            ->groupBy('session_shift')
            ->pluck('total', 'session_shift');

        $charts = $analytics->chartPayloadHydrated(
            farmId: $farmId,
            from: $rangeStart,
            to: $rangeEnd,
            allTime: $period === 'all',
        );

        $costFrom = $rangeStart?->toDateString() ?? $this->earliestCompletedSessionDate($farmId) ?? $today;
        $costTo = $rangeEnd?->toDateString() ?? $today;
        $costCurrent = $costPerLitre->calculate($farmId, $costFrom, $costTo);
        $costCompare = $this->previousPeriodCost($costPerLitre, $farmId, $period, $rangeStart, $rangeEnd);
        $costCompareDelta = $this->costCompareDelta($costCurrent, $costCompare);
        $costTrend = $costPerLitre->trend($farmId, 6);
        $expenseBreakdown = $this->expenseBreakdownPie($farmId, $costFrom, $costTo);

        return view('modules.milk.overview', $this->milkSectionData('overview', compact(
            'stats',
            'recentSessions',
            'topProducers',
            'byShift',
            'charts',
            'farmId',
            'selectedFarm',
            'period',
            'periodLabel',
            'costCurrent',
            'costCompareDelta',
            'costTrend',
            'expenseBreakdown',
        ) + [
            'farms' => Farm::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ]));
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    private function expenseBreakdownPie(?int $farmId, string $from, string $to): array
    {
        $rows = Expense::query()
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->where('expenses.status', 'paid')
            ->whereBetween('expenses.expense_date', [$from, $to])
            ->when($farmId, fn ($q) => $q->where('expenses.farm_id', $farmId))
            ->select('expense_categories.expense_group', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.expense_group')
            ->orderByDesc('total')
            ->get();

        $groups = config('modules.expense_groups', []);

        return [
            'labels' => $rows->map(function ($row) use ($groups) {
                $group = $groups[$row->expense_group] ?? null;

                return is_array($group)
                    ? ($group['label'] ?? ucfirst(str_replace('_', ' ', (string) $row->expense_group)))
                    : (is_string($group) ? $group : ucfirst(str_replace('_', ' ', (string) $row->expense_group)));
            })->values()->all(),
            'values' => $rows->pluck('total')->map(fn ($v) => (float) $v)->values()->all(),
        ];
    }

    private function resolvePeriod(?string $period): string
    {
        return in_array($period, ['all', 'today', 'monthly', 'yearly'], true) ? $period : 'all';
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon, 2: string}
     */
    private function periodRange(string $period): array
    {
        return match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay(), 'Today'],
            'monthly' => [now()->startOfMonth(), now()->endOfMonth(), 'This month'],
            'yearly' => [now()->startOfYear(), now()->endOfYear(), 'This year'],
            default => [null, null, 'All time'],
        };
    }

    private function earliestCompletedSessionDate(?int $farmId): ?string
    {
        $date = MilkSession::query()
            ->where('status', 'completed')
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->min('session_date');

        return $date ? Carbon::parse($date)->toDateString() : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function previousPeriodCost(
        MilkCostPerLitreService $costPerLitre,
        ?int $farmId,
        string $period,
        ?Carbon $rangeStart,
        ?Carbon $rangeEnd,
    ): array {
        if ($period === 'all' || ! $rangeStart || ! $rangeEnd) {
            return ['has_data' => false];
        }

        [$prevFrom, $prevTo] = match ($period) {
            'today' => [
                $rangeStart->copy()->subDay()->toDateString(),
                $rangeEnd->copy()->subDay()->toDateString(),
            ],
            'monthly' => [
                $rangeStart->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(),
                $rangeStart->copy()->subMonthNoOverflow()->endOfMonth()->toDateString(),
            ],
            'yearly' => [
                $rangeStart->copy()->subYear()->startOfYear()->toDateString(),
                $rangeStart->copy()->subYear()->endOfYear()->toDateString(),
            ],
            default => [null, null],
        };

        if (! $prevFrom || ! $prevTo) {
            return ['has_data' => false];
        }

        return $costPerLitre->calculate($farmId, $prevFrom, $prevTo);
    }

    /**
     * @param  array<string, mixed>  $current
     * @param  array<string, mixed>  $previous
     */
    private function costCompareDelta(array $current, array $previous): ?float
    {
        if (! ($current['has_data'] ?? false) || ! ($previous['has_data'] ?? false)) {
            return null;
        }

        return round((float) $current['cost_per_litre'] - (float) $previous['cost_per_litre'], 2);
    }
}
