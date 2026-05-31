<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BreedingSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\BreedingRecordRequest;
use App\Models\Animal;
use App\Models\BreedingRecord;
use App\Models\Farm;
use App\Services\BreedingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BreedingRecordController extends Controller
{
    use BreedingSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(
        private readonly BreedingService $breedingService,
    ) {}

    public function index(Request $request): View
    {
        $records = BreedingRecord::query()
            ->with(['farm', 'femaleAnimal', 'maleAnimal'])
            ->when($request->filled('farm_id'), fn ($q) => $q->where('farm_id', $request->integer('farm_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('breeding_status', $request->string('status')))
            ->orderByDesc('breeding_date')
            ->paginate(15)
            ->withQueryString();

        $farms = Farm::query()->orderBy('name')->get();

        return view('modules.breeding.records.index', $this->breedingSectionData('records', compact('records', 'farms')));
    }

    public function create(): View
    {
        return view('modules.breeding.records.create', $this->breedingSectionData('records', $this->formOptions()));
    }

    public function store(BreedingRecordRequest $request): RedirectResponse
    {
        try {
            $record = $this->breedingService->createBreedingRecord($request->recordAttributes());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['breeding' => $e->getMessage()]);
        }

        return redirect()
            ->route('breeding.records.edit', $record)
            ->with('success', 'Breeding record created. Add a pregnancy check when ready.');
    }

    public function edit(BreedingRecord $breedingRecord): View
    {
        $breedingRecord->load([
            'farm',
            'femaleAnimal',
            'maleAnimal',
            'pregnancyChecks',
            'birthRecord.offspring.animal',
            'logs.actor',
        ]);

        return view('modules.breeding.records.edit', $this->breedingSectionData('records', array_merge(
            $this->formOptions($breedingRecord),
            ['breedingRecord' => $breedingRecord],
        )));
    }

    public function update(BreedingRecordRequest $request, BreedingRecord $breedingRecord): RedirectResponse
    {
        try {
            $this->breedingService->updateBreedingRecord($breedingRecord, $request->recordAttributes());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['breeding' => $e->getMessage()]);
        }

        return redirect()
            ->route('breeding.records.edit', $breedingRecord)
            ->with('success', 'Breeding record updated.');
    }

    public function destroy(BreedingRecord $breedingRecord): RedirectResponse
    {
        if ($breedingRecord->birthRecord) {
            return back()->withErrors(['breeding' => 'Cannot delete a breeding record that has a birth.']);
        }

        $breedingRecord->delete();

        return redirect()->route('breeding.records')->with('success', 'Breeding record removed.');
    }

    private function formOptions(?BreedingRecord $record = null): array
    {
        $farmId = $record?->farm_id ?? old('farm_id');

        return [
            'farms' => Farm::query()->orderBy('name')->get(),
            'femaleAnimals' => Animal::query()
                ->where('gender', 'female')
                ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
                ->orderBy('tag_number')
                ->get(),
            'maleAnimals' => Animal::query()
                ->where('gender', 'male')
                ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
                ->orderBy('tag_number')
                ->get(),
            'gestationDefaults' => config('modules.breeding_gestation_days'),
        ];
    }
}
