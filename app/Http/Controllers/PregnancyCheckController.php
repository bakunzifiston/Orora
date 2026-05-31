<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BreedingSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\PregnancyCheckRequest;
use App\Models\BreedingRecord;
use App\Models\PregnancyCheck;
use App\Services\BreedingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PregnancyCheckController extends Controller
{
    use BreedingSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(
        private readonly BreedingService $breedingService,
    ) {}

    public function index(Request $request): View
    {
        $checks = PregnancyCheck::query()
            ->with(['animal', 'breedingRecord.farm'])
            ->when($request->filled('result'), fn ($q) => $q->where('result', $request->string('result')))
            ->orderByDesc('check_date')
            ->paginate(15)
            ->withQueryString();

        return view('modules.breeding.checks.index', $this->breedingSectionData('checks', compact('checks')));
    }

    public function create(Request $request): View
    {
        $breedingRecord = $request->filled('breeding_record_id')
            ? BreedingRecord::query()->with('femaleAnimal')->find($request->integer('breeding_record_id'))
            : null;

        $eligibleBreedings = BreedingRecord::query()
            ->with('femaleAnimal')
            ->whereIn('breeding_status', ['pending', 'confirmed_pregnant'])
            ->whereDoesntHave('birthRecord')
            ->orderByDesc('breeding_date')
            ->get();

        return view('modules.breeding.checks.create', $this->breedingSectionData('checks', compact('breedingRecord', 'eligibleBreedings')));
    }

    public function store(PregnancyCheckRequest $request): RedirectResponse
    {
        try {
            $check = $this->breedingService->createPregnancyCheck(
                $request->checkAttributes(),
                $request->file('attachment'),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['check' => $e->getMessage()]);
        }

        return redirect()
            ->route('breeding.records.edit', $check->breeding_record_id)
            ->with('success', 'Pregnancy check recorded.');
    }
}
