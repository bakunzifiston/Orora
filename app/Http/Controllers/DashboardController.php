<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard.index', [
            'navigation' => config('modules.navigation'),
            'stats' => config('dashboard.stats'),
            'modules' => config('dashboard.modules'),
            'activeNav' => 'dashboard',
        ]);
    }
}
