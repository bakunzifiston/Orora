<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\MilkSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Models\MilkRecord;
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

        $stats = [
            'today_total' => MilkRecord::query()->whereDate('recorded_on', $today)->sum('quantity'),
            'today_count' => MilkRecord::query()->whereDate('recorded_on', $today)->count(),
            'month_total' => MilkRecord::query()->whereBetween('recorded_on', [$startOfMonth, $endOfMonth])->sum('quantity'),
            'month_count' => MilkRecord::query()->whereBetween('recorded_on', [$startOfMonth, $endOfMonth])->count(),
            'animals_milked_today' => MilkRecord::query()->whereDate('recorded_on', $today)->distinct('animal_id')->count('animal_id'),
        ];

        $recentRecords = MilkRecord::query()
            ->with(['farm', 'animal'])
            ->orderByDesc('recorded_on')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $topProducers = MilkRecord::query()
            ->join('animals', 'milk_records.animal_id', '=', 'animals.id')
            ->whereBetween('recorded_on', [$startOfMonth, $endOfMonth])
            ->select('animals.tag_number', 'animals.name', DB::raw('SUM(milk_records.quantity) as total'))
            ->groupBy('animals.id', 'animals.tag_number', 'animals.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $bySession = MilkRecord::query()
            ->whereDate('recorded_on', $today)
            ->select('session', DB::raw('SUM(quantity) as total'))
            ->groupBy('session')
            ->pluck('total', 'session');

        $charts = $analytics->chartPayloadHydrated();

        return view('modules.milk.overview', $this->milkSectionData('overview', compact(
            'stats',
            'recentRecords',
            'topProducers',
            'bySession',
            'charts',
        )));
    }
}
