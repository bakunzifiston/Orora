<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\MilkSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Models\MilkRecord;
use App\Models\MilkSession;
use App\Services\MilkOverviewAnalyticsService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MilkModuleController extends Controller
{
    use MilkSectionViews;
    use ProvidesModuleNavigation;

    public function overview(MilkOverviewAnalyticsService $analytics): View
    {
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $completedToday = MilkSession::query()
            ->where('status', 'completed')
            ->whereDate('session_date', $today);

        $completedMonth = MilkSession::query()
            ->where('status', 'completed')
            ->whereBetween('session_date', [$startOfMonth, $endOfMonth]);

        $stats = [
            'today_total' => (float) $completedToday->sum('total_yield_liters'),
            'today_sessions' => $completedToday->count(),
            'animals_milked_today' => MilkRecord::query()
                ->whereHas('session', fn ($q) => $q->whereDate('session_date', $today)->where('status', 'completed'))
                ->distinct('animal_id')
                ->count('animal_id'),
            'month_total' => (float) $completedMonth->sum('total_yield_liters'),
            'month_sessions' => $completedMonth->count(),
        ];

        $recentSessions = MilkSession::query()
            ->with(['farm', 'livestock'])
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $topProducers = MilkRecord::query()
            ->join('milk_sessions', 'milk_records.milk_session_id', '=', 'milk_sessions.id')
            ->join('animals', 'milk_records.animal_id', '=', 'animals.id')
            ->where('milk_sessions.status', 'completed')
            ->whereBetween('milk_sessions.session_date', [$startOfMonth, $endOfMonth])
            ->select('animals.tag_number', 'animals.name', DB::raw('SUM(milk_records.yield_liters) as total'))
            ->groupBy('animals.id', 'animals.tag_number', 'animals.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $byShift = MilkSession::query()
            ->where('status', 'completed')
            ->whereDate('session_date', $today)
            ->select('session_shift', DB::raw('SUM(total_yield_liters) as total'))
            ->groupBy('session_shift')
            ->pluck('total', 'session_shift');

        $charts = $analytics->chartPayloadHydrated();

        return view('modules.milk.overview', $this->milkSectionData('overview', compact(
            'stats',
            'recentSessions',
            'topProducers',
            'byShift',
            'charts',
        )));
    }
}
