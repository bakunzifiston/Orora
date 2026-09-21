<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\LivestockRequest;
use App\Models\Animal;
use App\Models\Farm;
use App\Models\Livestock;
use App\Services\Species\SpeciesProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LivestockController extends Controller
{
    use ProvidesModuleNavigation;

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $farmId = $request->filled('farm_id') ? $request->integer('farm_id') : null;
        $status = $request->string('status')->toString();

        $statuses = config('modules.record_statuses', []);

        if ($status !== '' && ! in_array($status, $statuses, true)) {
            $status = '';
        }

        $livestock = Livestock::query()
            ->with('farm')
            ->withCount('animals')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';

                $query->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('breed', 'like', $like)
                        ->orWhere('notes', 'like', $like)
                        ->orWhereHas('farm', fn ($farm) => $farm->where('name', 'like', $like));
                });
            })
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $farms = Farm::query()->orderBy('name')->get();
        $filtersActive = $search !== '' || $farmId !== null || $status !== '';

        $stats = [
            'total' => Livestock::query()->count(),
            'active' => Livestock::query()->where('status', 'active')->count(),
            'head_count' => (int) Livestock::query()->sum('head_count'),
            'animals' => Animal::query()->count(),
        ];

        return view('modules.livestock.index', $this->moduleViewData('livestock', compact(
            'livestock',
            'farms',
            'stats',
            'search',
            'farmId',
            'status',
            'filtersActive',
        )));
    }

    public function show(Livestock $livestock): View
    {
        $livestock->load('farm')->loadCount('animals');

        return view('modules.livestock.show', $this->moduleViewData('livestock', compact('livestock')));
    }

    public function create(): View
    {
        return view('modules.livestock.create', $this->livestockFormData());
    }

    public function store(LivestockRequest $request): RedirectResponse
    {
        $group = Livestock::create($request->livestockAttributes());

        return redirect()
            ->route('livestock.show', $group)
            ->with('success', 'Livestock group created successfully.');
    }

    public function edit(Livestock $livestock): View
    {
        return view('modules.livestock.edit', $this->livestockFormData(compact('livestock')));
    }

    public function update(LivestockRequest $request, Livestock $livestock): RedirectResponse
    {
        $livestock->update($request->livestockAttributes());

        return redirect()
            ->route('livestock.show', $livestock)
            ->with('success', 'Livestock group updated successfully.');
    }

    public function destroy(Livestock $livestock): RedirectResponse
    {
        $livestock->delete();

        return redirect()->route('livestock.index')->with('success', 'Livestock group removed successfully.');
    }

    private function livestockFormData(array $extra = []): array
    {
        $farms = Farm::query()->orderBy('name')->get();
        $species = app(SpeciesProfile::class);
        $selectedFarm = $extra['livestock']->farm ?? $farms->first();

        return $this->moduleViewData('livestock', array_merge([
            'farms' => $farms,
            'catalogsByFarm' => $species->catalogsByFarm($farms),
            'herdGroups' => $species->herdGroups($selectedFarm),
            'livestockTypes' => $species->livestockTypes($selectedFarm),
            'productionPurposes' => $species->productionPurposes($selectedFarm),
            'farmingMethods' => $species->farmingMethods($selectedFarm),
            'feedingMethods' => $species->feedingMethods($selectedFarm),
            'groupCopy' => $species->copy($selectedFarm),
        ], $extra));
    }
}
