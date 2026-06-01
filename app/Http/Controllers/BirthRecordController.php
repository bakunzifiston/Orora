<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BreedingSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\BirthRecordRequest;
use App\Http\Requests\OffspringRegisterRequest;
use App\Http\Requests\OffspringUpdateRequest;
use App\Models\BirthRecord;
use App\Models\BreedingRecord;
use App\Models\ExpenseVendor;
use App\Models\Offspring;
use App\Services\BreedingService;
use App\Services\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BirthRecordController extends Controller
{
    use BreedingSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(
        private readonly BreedingService $breedingService,
        private readonly ExpenseService $expenseService,
    ) {}

    public function index(): View
    {
        $births = BirthRecord::query()
            ->with(['motherAnimal', 'breedingRecord.farm'])
            ->orderByDesc('birth_date')
            ->paginate(15);

        return view('modules.breeding.births.index', $this->breedingSectionData('births', compact('births')));
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

        $vendors = ExpenseVendor::query()->where('is_active', true)->orderBy('name')->get();

        return view('modules.breeding.births.create', $this->breedingSectionData('births', compact('breedingRecord', 'eligibleBreedings', 'vendors')));
    }

    public function store(BirthRecordRequest $request): RedirectResponse
    {
        try {
            $birth = $this->breedingService->createBirthRecord(
                $request->birthAttributes(),
                $request->file('attachment'),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['birth' => $e->getMessage()]);
        }

        $this->expenseService->syncFromRequest(
            $request,
            $birth,
            'breeding.birth',
            ExpenseService::birthRecordContext($birth->fresh()),
        );

        return redirect()
            ->route('breeding.births.edit', $birth)
            ->with('success', 'Birth recorded. Register offspring below.');
    }

    public function edit(BirthRecord $birthRecord): View
    {
        $birthRecord->load(['motherAnimal', 'breedingRecord', 'offspring.animal']);

        return view('modules.breeding.births.edit', $this->breedingSectionData('births', ['birthRecord' => $birthRecord]));
    }

    public function updateOffspring(OffspringUpdateRequest $request, BirthRecord $birthRecord, Offspring $offspring): RedirectResponse
    {
        if ((int) $offspring->birth_record_id !== (int) $birthRecord->id) {
            abort(404);
        }

        try {
            $this->breedingService->updateOffspring($offspring, $request->validated());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['offspring' => $e->getMessage()]);
        }

        return back()->with('success', 'Offspring updated.');
    }

    public function registerOffspring(OffspringRegisterRequest $request, BirthRecord $birthRecord, Offspring $offspring): RedirectResponse
    {
        if ((int) $offspring->birth_record_id !== (int) $birthRecord->id) {
            abort(404);
        }

        try {
            $this->breedingService->registerOffspring($offspring, $request->validated());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['register' => $e->getMessage()]);
        }

        return back()->with('success', 'Offspring registered as a new animal.');
    }
}
