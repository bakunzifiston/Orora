<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HealthSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\MedicineMovementRequest;
use App\Http\Requests\MedicineRequest;
use App\Models\Medicine;
use App\Services\MedicineInventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MedicineController extends Controller
{
    use HealthSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(private MedicineInventoryService $medicineInventoryService) {}

    public function index(): View
    {
        $medicines = Medicine::query()
            ->withCount('treatments')
            ->orderBy('name')
            ->paginate(15);

        $alerts = [
            'low_stock' => Medicine::query()
                ->whereNotNull('reorder_level')
                ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                ->count(),
            'expired' => Medicine::query()
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<', now()->toDateString())
                ->count(),
        ];

        return view('modules.health.medicines.index', $this->healthSectionData('medicines', compact('medicines', 'alerts')));
    }

    public function create(): View
    {
        return view('modules.health.medicines.create', $this->healthSectionData('medicines'));
    }

    public function store(MedicineRequest $request): RedirectResponse
    {
        Medicine::create($request->medicineAttributes());

        return redirect()
            ->route('health.medicines')
            ->with('success', __('Medicine saved successfully.'));
    }

    public function show(Medicine $medicine): View
    {
        $medicine->loadCount('treatments');
        $medicine->load(['movements' => fn ($q) => $q->limit(20)]);

        $treatments = $medicine->treatments()
            ->with(['farm', 'animal', 'flock', 'recordedBy'])
            ->orderByDesc('start_date')
            ->paginate(15);

        $movementLabels = config('modules.medicine_movement_labels');

        return view('modules.health.medicines.show', $this->healthSectionData('medicines', compact('medicine', 'treatments', 'movementLabels')));
    }

    public function edit(Medicine $medicine): View
    {
        return view('modules.health.medicines.edit', $this->healthSectionData('medicines', compact('medicine')));
    }

    public function update(MedicineRequest $request, Medicine $medicine): RedirectResponse
    {
        $medicine->update($request->medicineAttributes());

        return redirect()
            ->route('health.medicines')
            ->with('success', __('Medicine updated successfully.'));
    }

    public function storeMovement(MedicineMovementRequest $request, Medicine $medicine): RedirectResponse
    {
        try {
            $this->medicineInventoryService->recordMovement(
                $medicine,
                $request->input('movement_type'),
                (float) $request->input('quantity'),
                $request->input('notes'),
                null,
                $request->filled('moved_at') ? $request->date('moved_at') : now(),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['quantity' => $e->getMessage()]);
        }

        return redirect()
            ->route('health.medicines.show', $medicine)
            ->with('success', __('Stock movement recorded successfully.'));
    }

    public function destroy(Medicine $medicine): RedirectResponse
    {
        if ($medicine->treatments()->exists()) {
            return back()->withErrors([
                'medicine' => __('This medicine is linked to treatments and cannot be deleted. Mark it inactive instead.'),
            ]);
        }

        $medicine->delete();

        return redirect()
            ->route('health.medicines')
            ->with('success', __('Medicine removed successfully.'));
    }
}
