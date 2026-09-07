<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EggSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Models\EggCollection;
use App\Models\Farm;
use App\Models\Flock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EggModuleController extends Controller
{
    use EggSectionViews;
    use ProvidesModuleNavigation;

    public function overview(Request $request): View
    {
        $farmId = $request->filled('farm_id') ? $request->integer('farm_id') : null;
        $from = Carbon::now()->subDays(30)->toDateString();
        $to = Carbon::now()->toDateString();

        $period = EggCollection::query()
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->whereBetween('collected_on', [$from, $to]);

        $birds = (int) Flock::query()
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->where('lifecycle_status', 'Active')
            ->sum('current_count');

        $eggs = (int) (clone $period)->sum('eggs_count');
        $cracked = (int) (clone $period)->sum('cracked_count');
        $days = 30;

        $trend = EggCollection::query()
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->whereBetween('collected_on', [$from, $to])
            ->select('collected_on', DB::raw('SUM(eggs_count) as eggs'), DB::raw('SUM(cracked_count) as cracked'))
            ->groupBy('collected_on')
            ->orderBy('collected_on')
            ->get();

        $stats = [
            'eggs' => $eggs,
            'cracked' => $cracked,
            'saleable' => max(0, $eggs - $cracked),
            'birds' => $birds,
            'lay_rate' => $birds > 0 ? round(($eggs / $days / $birds) * 100, 1) : 0,
            'collections' => (clone $period)->count(),
        ];

        $recent = EggCollection::query()
            ->with(['farm', 'flock'])
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->orderByDesc('collected_on')
            ->limit(10)
            ->get();

        return view('modules.eggs.overview', $this->eggSectionData('overview', [
            'stats' => $stats,
            'trend' => $trend,
            'recent' => $recent,
            'farms' => Farm::query()->where('primary_species', 'poultry')->orderBy('name')->get(),
            'farmId' => $farmId,
        ]));
    }
}
