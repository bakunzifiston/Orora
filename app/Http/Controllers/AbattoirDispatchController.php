<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Controllers\Concerns\SalesSectionViews;
use App\Models\AbattoirDispatch;
use App\Models\Animal;
use App\Models\Farm;
use App\Services\SaleTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AbattoirDispatchController extends Controller
{
    use ProvidesModuleNavigation;
    use SalesSectionViews;

    public function __construct(private readonly SaleTransactionService $saleService) {}

    public function index(): View
    {
        $dispatches = AbattoirDispatch::query()
            ->with('farm')
            ->orderByDesc('dispatch_date')
            ->paginate(15);

        return view('modules.sales.abattoir.index', $this->salesSectionData('abattoir', compact('dispatches')));
    }

    public function create(): View
    {
        return view('modules.sales.abattoir.create', $this->salesSectionData('abattoir', [
            'farms' => Farm::query()->orderBy('name')->get(),
            'animals' => Animal::query()
                ->where('lifecycle_status', 'Active')
                ->orderBy('tag_number')
                ->get(['id', 'farm_id', 'tag_number', 'weight_kg']),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'farm_id' => ['required', 'exists:farms,id'],
            'dispatch_date' => ['required', 'date'],
            'abattoir_name' => ['required', 'string', 'max:255'],
            'abattoir_location' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'transport_method' => ['nullable', 'string', 'max:50'],
            'vehicle_plate' => ['nullable', 'string', 'max:50'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'expected_return_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'animal_ids' => ['required', 'array', 'min:1'],
            'animal_ids.*' => ['exists:animals,id'],
            'live_weights' => ['nullable', 'array'],
        ]);

        $dispatch = DB::transaction(function () use ($validated) {
            $dispatch = AbattoirDispatch::create([
                'farm_id' => $validated['farm_id'],
                'dispatch_code' => $this->saleService->generateDispatchCode($validated['dispatch_date']),
                'dispatch_date' => $validated['dispatch_date'],
                'abattoir_name' => $validated['abattoir_name'],
                'abattoir_location' => $validated['abattoir_location'] ?? null,
                'contact_person' => $validated['contact_person'] ?? null,
                'transport_method' => $validated['transport_method'] ?? null,
                'vehicle_plate' => $validated['vehicle_plate'] ?? null,
                'driver_name' => $validated['driver_name'] ?? null,
                'expected_return_date' => $validated['expected_return_date'] ?? null,
                'total_animals_dispatched' => count($validated['animal_ids']),
                'dispatch_status' => 'dispatched',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['animal_ids'] as $animalId) {
                $animal = Animal::query()->findOrFail($animalId);
                $this->saleService->assertAnimalSellable($animal);

                $dispatch->animals()->create([
                    'animal_id' => $animalId,
                    'live_weight_kg' => $validated['live_weights'][$animalId] ?? $animal->weight_kg,
                    'animal_condition' => 'healthy',
                ]);

                $animal->update(['lifecycle_status' => 'Transferred']);
            }

            return $dispatch;
        });

        return redirect()->route('sales.abattoir.show', $dispatch)->with('success', 'Animals dispatched to abattoir.');
    }

    public function show(AbattoirDispatch $abattoirDispatch): View
    {
        $abattoirDispatch->load(['farm', 'animals.animal', 'returns.animal', 'saleTransaction']);

        return view('modules.sales.abattoir.show', $this->salesSectionData('abattoir', [
            'dispatch' => $abattoirDispatch,
            'cutTypes' => config('modules.abattoir_cut_types'),
        ]));
    }

    public function storeReturn(Request $request, AbattoirDispatch $abattoirDispatch): RedirectResponse
    {
        $validated = $request->validate([
            'animal_id' => ['required', 'exists:animals,id'],
            'return_date' => ['required', 'date'],
            'cut_type' => ['required', 'in:'.implode(',', config('modules.abattoir_cut_types'))],
            'cut_weight_kg' => ['required', 'numeric', 'min:0.01'],
            'carcass_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'dressing_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grade' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string'],
        ]);

        $abattoirDispatch->returns()->create($validated);
        $abattoirDispatch->update(['dispatch_status' => 'returned']);

        return redirect()->route('sales.abattoir.show', $abattoirDispatch)->with('success', 'Abattoir return recorded.');
    }
}
