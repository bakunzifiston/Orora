<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EggSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\EggCollectionRequest;
use App\Models\EggCollection;
use App\Models\Farm;
use App\Models\Flock;
use App\Services\EggCollectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EggCollectionController extends Controller
{
    use EggSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(private readonly EggCollectionService $collections) {}

    public function index(): View
    {
        $collections = EggCollection::query()
            ->with(['farm', 'flock'])
            ->orderByDesc('collected_on')
            ->paginate(15);

        return view('modules.eggs.collections.index', $this->eggSectionData('collections', compact('collections')));
    }

    public function create(Request $request): View
    {
        return view('modules.eggs.collections.create', $this->eggSectionData('collections', array_merge(
            $this->formOptions(),
            [
                'selectedFarmId' => $request->filled('farm_id') ? $request->integer('farm_id') : null,
                'selectedFlockId' => $request->filled('flock_id') ? $request->integer('flock_id') : null,
            ],
        )));
    }

    public function store(EggCollectionRequest $request): RedirectResponse
    {
        $this->collections->create($request->collectionAttributes());

        return redirect()->route('eggs.collections')->with('success', 'Egg collection saved.');
    }

    public function edit(EggCollection $eggCollection): View
    {
        return view('modules.eggs.collections.edit', $this->eggSectionData('collections', array_merge(
            $this->formOptions(),
            ['eggCollection' => $eggCollection],
        )));
    }

    public function update(EggCollectionRequest $request, EggCollection $eggCollection): RedirectResponse
    {
        $eggCollection->update($request->collectionAttributes());

        return redirect()->route('eggs.collections')->with('success', 'Egg collection updated.');
    }

    public function destroy(EggCollection $eggCollection): RedirectResponse
    {
        $eggCollection->delete();

        return redirect()->route('eggs.collections')->with('success', 'Egg collection removed.');
    }

    private function formOptions(): array
    {
        $farms = Farm::query()->where('primary_species', 'poultry')->orderBy('name')->get();

        return [
            'farms' => $farms,
            'flocks' => Flock::query()
                ->with('farm')
                ->whereIn('farm_id', $farms->modelKeys() ?: [0])
                ->orderBy('name')
                ->get(),
        ];
    }
}
