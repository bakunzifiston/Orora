<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HealthSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Models\Animal;
use App\Models\DiseaseRecord;
use App\Models\HealthRecord;
use App\Models\Mortality;
use App\Models\Treatment;
use App\Models\VetVisit;
use App\Models\Vaccination;
use App\Services\HealthOverviewAnalyticsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HealthController extends Controller
{
    use HealthSectionViews;
    use ProvidesModuleNavigation;

    public function overview(HealthOverviewAnalyticsService $analytics): View
    {
        $stats = [
            'total_animals' => Animal::query()->count(),
            'healthy' => Animal::query()->where('health_status', 'Healthy')->count(),
            'needs_attention' => Animal::query()->whereIn('health_status', ['Sick', 'Under treatment', 'Quarantined', 'Recovering'])->count(),
            'deceased' => Animal::query()->where(function (Builder $query) {
                $query->where('health_status', 'Deceased')
                    ->orWhere('lifecycle_status', 'Deceased');
            })->count(),
            'upcoming_followups' => HealthRecord::query()
                ->whereNotNull('next_follow_up')
                ->whereDate('next_follow_up', '>=', now()->toDateString())
                ->whereDate('next_follow_up', '<=', now()->addDays(30)->toDateString())
                ->count(),
        ];

        $recentRecords = HealthRecord::query()
            ->with(['animal', 'farm'])
            ->orderByDesc('recorded_on')
            ->limit(8)
            ->get();

        $charts = $analytics->chartPayload();

        return view('modules.health.overview', $this->healthSectionData('overview', compact('stats', 'recentRecords', 'charts')));
    }

    public function vaccinations(): View
    {
        $vaccinations = Vaccination::query()
            ->with(['farm', 'animal'])
            ->orderByDesc('vaccination_date')
            ->paginate(15);

        return view('modules.health.vaccinations.index', $this->healthSectionData('vaccinations', compact('vaccinations')));
    }

    public function treatments(): View
    {
        $treatments = Treatment::query()
            ->with(['farm', 'animal'])
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('modules.health.treatments.index', $this->healthSectionData('treatments', compact('treatments')));
    }

    public function disease(): View
    {
        $diseaseReady = Schema::hasTable('disease_records');

        $diseaseRecords = $diseaseReady
            ? DiseaseRecord::query()
                ->with(['farm', 'livestock', 'animal'])
                ->orderByDesc('diagnosis_date')
                ->paginate(15)
            : new LengthAwarePaginator([], 0, 15);

        return view('modules.health.disease.index', $this->healthSectionData('disease', compact('diseaseRecords', 'diseaseReady')));
    }

    public function vetVisits(): View
    {
        $vetVisits = VetVisit::query()
            ->with(['farm', 'animal'])
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('modules.health.vet-visits.index', $this->healthSectionData('vet-visits', compact('vetVisits')));
    }

    public function mortality(): View
    {
        $mortalities = Mortality::query()
            ->with(['farm', 'animal'])
            ->orderByDesc('death_date')
            ->paginate(15);

        $deceasedAnimals = Animal::query()
            ->with('farm')
            ->where(function (Builder $query) {
                $query->where('health_status', 'Deceased')
                    ->orWhere('lifecycle_status', 'Deceased');
            })
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return view('modules.health.mortalities.index', $this->healthSectionData('mortality', compact('mortalities', 'deceasedAnimals')));
    }

    public function timeline(): View
    {
        $healthRecords = HealthRecord::query()
            ->with(['farm', 'animal'])
            ->orderByDesc('recorded_on')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('modules.health.timeline', $this->healthSectionData('timeline', compact('healthRecords')));
    }

    private function recordsSection(string $section, string $title, string $subtitle): View
    {
        $types = config("modules.health_section_record_types.{$section}", []);

        $healthRecords = HealthRecord::query()
            ->with(['farm', 'animal'])
            ->when($types !== [], fn (Builder $query) => $query->whereIn('record_type', $types))
            ->orderByDesc('recorded_on')
            ->paginate(15);

        return view('modules.health.records', $this->healthSectionData($section, compact('healthRecords', 'title', 'subtitle', 'section')));
    }
}
