<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\FlockRequest;
use App\Models\Farm;
use App\Models\Flock;
use App\Models\Livestock;
use App\Services\FlockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FlockController extends Controller
{
    use ProvidesModuleNavigation;

    public function __construct(private readonly FlockService $flocks) {}

    public function index(Request $request): View
    {
        $flocks = Flock::query()
            ->with(['farm', 'livestock'])
            ->when($request->filled('farm_id'), fn ($q) => $q->where('farm_id', $request->integer('farm_id')))
            ->when($request->filled('production_type'), fn ($q) => $q->where('production_type', $request->string('production_type')))
            ->orderByDesc('placed_on')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Flock::query()->count(),
            'active' => Flock::query()->where('lifecycle_status', 'Active')->count(),
            'birds' => (int) Flock::query()->sum('current_count'),
            'placed' => (int) Flock::query()->sum('placed_count'),
        ];

        return view('modules.flocks.index', $this->moduleViewData('flocks', [
            'flocks' => $flocks,
            'farms' => Farm::query()->where('primary_species', 'poultry')->orderBy('name')->get(),
            'stats' => $stats,
        ]));
    }

    public function show(Flock $flock): View
    {
        $flock->load(['farm', 'livestock', 'events']);

        return view('modules.flocks.show', $this->moduleViewData('flocks', compact('flock')));
    }

    public function create(): View
    {
        return view('modules.flocks.create', $this->formData());
    }

    public function store(FlockRequest $request): RedirectResponse
    {
        $flock = $this->flocks->create($request->flockAttributes());

        return redirect()
            ->route('flocks.show', $flock)
            ->with('success', 'Flock placed successfully.');
    }

    public function edit(Flock $flock): View
    {
        return view('modules.flocks.edit', $this->formData(compact('flock')));
    }

    public function update(FlockRequest $request, Flock $flock): RedirectResponse
    {
        $flock->update($request->flockAttributes());

        return redirect()
            ->route('flocks.show', $flock)
            ->with('success', 'Flock updated successfully.');
    }

    public function destroy(Flock $flock): RedirectResponse
    {
        $flock->delete();

        return redirect()->route('flocks.index')->with('success', 'Flock removed successfully.');
    }

    private function formData(array $extra = []): array
    {
        $farms = Farm::query()->where('primary_species', 'poultry')->orderBy('name')->get();

        return $this->moduleViewData('flocks', array_merge([
            'farms' => $farms,
            'livestockGroups' => Livestock::query()
                ->with('farm')
                ->whereIn('farm_id', $farms->modelKeys())
                ->orderBy('name')
                ->get(),
        ], $extra));
    }
}
