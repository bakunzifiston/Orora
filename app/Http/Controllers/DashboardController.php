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

        return view('dashboard.index', [
            'navigation' => $species->navigation(),
            'navigationGroups' => $species->navigationGroups(),
            'activeNav' => 'dashboard',
            'dashboard' => $analytics->build($filters),
            'farms' => Farm::query()->orderBy('name')->get(),
            'speciesKey' => $species->key($filters['farm_id'] ? Farm::query()->find($filters['farm_id']) : null),
        ]);
    }
}
