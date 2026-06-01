<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Services\DashboardAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardAnalyticsService $analytics): View
    {
        $filters = $analytics->resolveFilters($request);

        return view('dashboard.index', [
            'navigation' => config('modules.navigation'),
            'activeNav' => 'dashboard',
            'dashboard' => $analytics->build($filters),
            'farms' => Farm::query()->orderBy('name')->get(),
        ]);
    }
}
