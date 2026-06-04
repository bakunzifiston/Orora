<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\MilkSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Models\Farm;
use App\Models\MilkRecord;
use App\Models\MilkSession;
use App\Services\Milk\MilkCostPerLitreService;
use App\Services\MilkOverviewAnalyticsService;
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
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $completedToday = MilkSession::query()
            ->where('status', 'completed')
            ->whereDate('session_date', $today)
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId));

        $completedMonth = MilkSession::query()
            ->where('status', 'completed')
            ->whereBetween('session_date', [$startOfMonth, $endOfMonth])
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId));

        $stats = [
            'today_total' => (float) $completedToday->sum('total_yield_liters'),
            'today_sessions' => $completedToday->count(),
            'animals_milked_today' => MilkRecord::query()
                ->whereHas('session', fn ($q) => $q
                    ->whereDate('session_date', $today)
                    ->where('status', 'completed')
                    ->when($farmId, fn ($sq) => $sq->where('farm_id', $farmId)))
                ->distinct('animal_id')
                ->count('animal_id'),
            'month_total' => (float) $completedMonth->sum('total_yield_liters'),
            'month_sessions' => $completedMonth->count(),
        ];

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
            ->whereBetween('milk_sessions.session_date', [$startOfMonth, $endOfMonth])
            ->when($farmId, fn ($q) => $q->where('milk_sessions.farm_id', $farmId))
            ->select('animals.tag_number', 'animals.name', DB::raw('SUM(milk_records.yield_liters) as total'))
            ->groupBy('animals.id', 'animals.tag_number', 'animals.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $byShift = MilkSession::query()
            ->where('status', 'completed')
            ->whereDate('session_date', $today)
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->select('session_shift', DB::raw('SUM(total_yield_liters) as total'))
            ->groupBy('session_shift')
            ->pluck('total', 'session_shift');

        $charts = $analytics->chartPayloadHydrated();

        $costToday = $costPerLitre->daily($farmId, $today);
        $costMonthly = $costPerLitre->monthly($farmId, now()->year, now()->month);
        $costTrend = $costPerLitre->trend($farmId, 6);

        $costTodayCompare = $this->costCompareDelta(
            $costToday,
            $costPerLitre->daily($farmId, now()->subDay()->toDateString()),
        );

        $costMonthlyCompare = $this->costCompareDelta(
            $costMonthly,
            $costPerLitre->monthly($farmId, now()->subMonth()->year, now()->subMonth()->month),
        );

        return view('modules.milk.overview', $this->milkSectionData('overview', compact(
            'stats',
            'recentSessions',
            'topProducers',
            'byShift',
            'charts',
            'farmId',
            'selectedFarm',
            'costToday',
            'costMonthly',
            'costTrend',
            'costTodayCompare',
            'costMonthlyCompare',
        ) + [
            'farms' => Farm::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ]));
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
