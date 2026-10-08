<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Services\DashboardAnalyticsService;
use App\Services\Species\SpeciesProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardAnalyticsService $analytics, SpeciesProfile $species): View
    {
        $filters = $analytics->resolveFilters($request);

        $access = app(\App\Services\WorkspaceAccessService::class);
        $groups = $access->filterNavigationGroups($species->navigationGroups(), auth()->user());

        return view('dashboard.index', [
            'navigation' => collect($groups)->flatMap(fn (array $g) => $g['items'] ?? [])->values()->all(),
            'navigationGroups' => $groups,
            'activeNav' => 'dashboard',
            'dashboard' => $analytics->build($filters),
            'farms' => Farm::query()->orderBy('name')->get(),
            'speciesKey' => $species->key($filters['farm_id'] ? Farm::query()->find($filters['farm_id']) : null),
        ]);
    }
}
