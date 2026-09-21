<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\AnimalRequest;
use App\Models\Animal;
use App\Models\Farm;
use App\Models\Livestock;
use App\Services\DashboardAnalyticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnimalController extends Controller
{
    use ProvidesModuleNavigation;

    public function index(Request $request, DashboardAnalyticsService $analytics): View
    {
        $search = trim((string) $request->input('q', ''));
        $farmId = $request->filled('farm_id') ? $request->integer('farm_id') : null;
        $livestockId = $request->filled('livestock_id') ? $request->integer('livestock_id') : null;
        $gender = $request->string('gender')->toString();
        $lifecycleStatus = $request->string('lifecycle_status')->toString();
        $healthStatus = $request->string('health_status')->toString();

        $genders = array_keys(config('modules.animal_genders', []));
        $lifecycleStatuses = config('modules.lifecycle_statuses', []);
        $healthStatuses = config('modules.health_statuses', []);

        if ($gender !== '' && ! in_array($gender, $genders, true)) {
            $gender = '';
        }

        if ($lifecycleStatus !== '' && ! in_array($lifecycleStatus, $lifecycleStatuses, true)) {
            $lifecycleStatus = '';
        }

        if ($healthStatus !== '' && ! in_array($healthStatus, $healthStatuses, true)) {
            $healthStatus = '';
        }

        $animals = Animal::query()
            ->with(['farm', 'livestock'])
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';

                $query->where(function ($inner) use ($like) {
                    $inner->where('tag_number', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('breed', 'like', $like)
                        ->orWhere('species', 'like', $like)
                        ->orWhereHas('farm', fn ($farm) => $farm->where('name', 'like', $like))
                        ->orWhereHas('livestock', fn ($group) => $group->where('name', 'like', $like));
                });
            })
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId))
            ->when($livestockId, fn ($query) => $query->where('livestock_id', $livestockId))
            ->when($gender !== '', fn ($query) => $query->where('gender', $gender))
            ->when($lifecycleStatus !== '', fn ($query) => $query->where('lifecycle_status', $lifecycleStatus))
            ->when($healthStatus !== '', fn ($query) => $query->where('health_status', $healthStatus))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $farms = Farm::query()->orderBy('name')->get();
        $livestockGroups = Livestock::query()
            ->with('farm')
            ->withCount(['animals' => function ($query) use ($farmId) {
                $query->when($farmId, fn ($inner) => $inner->where('farm_id', $farmId));
            }])
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId))
            ->orderBy('name')
            ->get();

        $filtersActive = $search !== ''
            || $farmId !== null
            || $livestockId !== null
            || $gender !== ''
            || $lifecycleStatus !== ''
            || $healthStatus !== '';

        $statsQuery = Animal::query()->when($farmId, fn ($query) => $query->where('farm_id', $farmId));

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->where('lifecycle_status', 'Active')->count(),
            'female' => (clone $statsQuery)->where('gender', 'female')->count(),
            'male' => (clone $statsQuery)->where('gender', 'male')->count(),
        ];

        $moduleKpis = collect($analytics->operationModuleKpis($farmId))
            ->reject(fn (array $kpi) => in_array($kpi['key'] ?? '', ['health', 'breeding'], true))
            ->values()
            ->all();

        return view('modules.animals.index', $this->moduleViewData('animals', compact(
            'animals',
            'farms',
            'livestockGroups',
            'stats',
            'moduleKpis',
            'search',
            'farmId',
            'livestockId',
            'gender',
            'lifecycleStatus',
            'healthStatus',
            'filtersActive',
        )));
    }

    public function show(Animal $animal): View
    {
        $animal->load(['farm', 'livestock']);

        return view('modules.animals.show', $this->moduleViewData('animals', compact('animal')));
    }

    public function create(): View
    {
        return view('modules.animals.create', $this->moduleViewData('animals', $this->formOptions()));
    }

    public function store(AnimalRequest $request): RedirectResponse
    {
        $animal = Animal::create($request->animalAttributes());
        $this->storePhoto($request, $animal);

        return redirect()
            ->route('animals.show', $animal)
            ->with('success', 'Animal registered successfully.');
    }

    public function edit(Animal $animal): View
    {
        return view('modules.animals.edit', $this->moduleViewData('animals', array_merge($this->formOptions(), compact('animal'))));
    }

    public function update(AnimalRequest $request, Animal $animal): RedirectResponse
    {
        $animal->update($request->animalAttributes());
        $this->storePhoto($request, $animal);

        return redirect()
            ->route('animals.show', $animal)
            ->with('success', 'Animal updated successfully.');
    }

    public function destroy(Animal $animal): RedirectResponse
    {
        if ($animal->photo_path) {
            Storage::disk('public')->delete($animal->photo_path);
        }

        $animal->delete();

        return redirect()->route('animals.index')->with('success', 'Animal removed successfully.');
    }

    private function formOptions(): array
    {
        return [
            'farms' => Farm::query()->orderBy('name')->get(),
            'livestockGroups' => Livestock::query()->with('farm')->orderBy('name')->get(),
        ];
    }

    private function storePhoto(AnimalRequest $request, Animal $animal): void
    {
        if (! $request->hasFile('photo')) {
            return;
        }

        if ($animal->photo_path) {
            Storage::disk('public')->delete($animal->photo_path);
        }

        $path = $request->file('photo')->store('animals/'.$animal->id, 'public');
        $animal->update(['photo_path' => $path]);
    }
}
