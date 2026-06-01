<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BreedingSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Models\BirthRecord;
use App\Models\BreedingRecord;
use App\Models\PregnancyCheck;
use App\Services\BreedingReminderService;
use Illuminate\View\View;

class BreedingModuleController extends Controller
{
    use BreedingSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(private readonly BreedingReminderService $breedingReminders) {}

    public function overview(): View
    {
        $stats = [
            'active_breedings' => BreedingRecord::query()
                ->whereIn('breeding_status', ['pending', 'confirmed_pregnant'])
                ->count(),
            'confirmed_pregnant' => BreedingRecord::query()
                ->where('breeding_status', 'confirmed_pregnant')
                ->count(),
            'due_this_month' => BreedingRecord::query()
                ->where('breeding_status', 'confirmed_pregnant')
                ->whereBetween('expected_calving_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'births_this_month' => BirthRecord::query()
                ->whereBetween('birth_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'pregnancy_checks_due' => $this->breedingReminders->dueCount(),
        ];

        $recentBreedings = BreedingRecord::query()
            ->with(['farm', 'femaleAnimal'])
            ->orderByDesc('breeding_date')
            ->limit(8)
            ->get();

        $upcomingCalvings = BreedingRecord::query()
            ->with(['farm', 'femaleAnimal'])
            ->where('breeding_status', 'confirmed_pregnant')
            ->whereNotNull('expected_calving_date')
            ->orderBy('expected_calving_date')
            ->limit(8)
            ->get();

        $recentChecks = PregnancyCheck::query()
            ->with(['animal', 'breedingRecord'])
            ->orderByDesc('check_date')
            ->limit(6)
            ->get();

        $pregnancyChecksDue = $this->breedingReminders->dueRecords(limit: 10);

        return view('modules.breeding.overview', $this->breedingSectionData('overview', compact(
            'stats',
            'recentBreedings',
            'upcomingCalvings',
            'recentChecks',
            'pregnancyChecksDue',
        )));
    }
}
