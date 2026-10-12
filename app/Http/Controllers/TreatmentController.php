<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HealthSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Controllers\Concerns\ProvidesStockOptions;
use App\Http\Requests\TreatmentRequest;
use App\Models\HealthRecord;
use App\Models\Medicine;
use App\Models\Treatment;
use App\Services\ExpenseService;
use App\Services\MedicineInventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TreatmentController extends Controller
{
    use HealthSectionViews;
    use ProvidesModuleNavigation;
    use ProvidesStockOptions;

    public function __construct(
        private ExpenseService $expenseService,
        private MedicineInventoryService $medicineInventoryService,
    ) {}

    public function create(): View
    {
        return view('modules.health.treatments.create', $this->healthSectionData('treatments', array_merge($this->stockOptions(), [
            'medicines' => Medicine::query()->where('is_active', true)->orderBy('name')->get(),
        ])));
    }

    public function store(TreatmentRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request) {
                $attributes = $request->treatmentAttributes();

                if ($request->wantsNewMedicine()) {
                    $medicine = Medicine::create($request->newMedicineAttributes());
                    $attributes['medicine_id'] = $medicine->id;
                    $attributes['medicine_name'] = $medicine->name;
                }

                $treatment = Treatment::create(array_merge(
                    $attributes,
                    $request->resolvedStockAttributes(),
                    ['recorded_by' => auth()->id()],
                ));

                $this->storeAttachment($request, $treatment);
                $this->medicineInventoryService->deductForTreatment($treatment);
                $this->syncHealthRecord($treatment);
            });
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['quantity_used' => $e->getMessage()]);
        }

        return redirect()
            ->route('health.treatments')
            ->with('success', __('Treatment saved successfully.'));
    }

    public function edit(Treatment $treatment): View
    {
        $treatment->load(['animal', 'farm', 'medicine']);

        return view('modules.health.treatments.edit', $this->healthSectionData('treatments', array_merge($this->stockOptions(), [
            'treatment' => $treatment,
            'medicines' => Medicine::query()
                ->where(function ($query) use ($treatment) {
                    $query->where('is_active', true);

                    if ($treatment->medicine_id) {
                        $query->orWhereKey($treatment->medicine_id);
                    }
                })
                ->orderBy('name')
                ->get(),
        ])));
    }

    public function update(TreatmentRequest $request, Treatment $treatment): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $treatment) {
                $previous = $treatment->replicate();
                $previous->id = $treatment->id;
                $previous->exists = true;

                $treatment->update(array_merge(
                    $request->treatmentAttributes(),
                    $request->resolvedStockAttributes(),
                ));

                $this->storeAttachment($request, $treatment);
                $this->medicineInventoryService->syncForTreatment($treatment->fresh(), $previous);
                $this->syncHealthRecord($treatment->fresh());
            });
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['quantity_used' => $e->getMessage()]);
        }

        return redirect()
            ->route('health.treatments')
            ->with('success', __('Treatment updated successfully.'));
    }

    public function destroy(Treatment $treatment): RedirectResponse
    {
        DB::transaction(function () use ($treatment) {
            if ($treatment->attachment_path) {
                Storage::disk('public')->delete($treatment->attachment_path);
            }

            $this->medicineInventoryService->restoreForTreatment($treatment);
            $this->expenseService->deleteForSource($treatment);
            $treatment->healthRecord?->delete();
            $treatment->delete();
        });

        return redirect()
            ->route('health.treatments')
            ->with('success', __('Treatment removed successfully.'));
    }

    private function storeAttachment(TreatmentRequest $request, Treatment $treatment): void
    {
        if (! $request->hasFile('attachment')) {
            return;
        }

        if ($treatment->attachment_path) {
            Storage::disk('public')->delete($treatment->attachment_path);
        }

        $path = $request->file('attachment')->store('treatments/'.$treatment->id, 'public');
        $treatment->update(['attachment_path' => $path]);
    }

    private function syncHealthRecord(Treatment $treatment): void
    {
        $treatment->load(['animal', 'flock', 'medicine']);

        $title = collect([$treatment->disease_name, $treatment->medicine?->name ?? $treatment->medicine_name])
            ->filter()
            ->implode(' · ');

        $notes = collect([
            $treatment->symptoms ? 'Symptoms: '.$treatment->symptoms : null,
            $treatment->diagnosis ? 'Diagnosis: '.$treatment->diagnosis : null,
            $treatment->notes,
        ])->filter()->implode("\n\n");

        $healthData = [
            'farm_id' => $treatment->farm_id,
            'animal_id' => $treatment->animal_id,
            'flock_id' => $treatment->flock_id,
            'record_type' => 'Treatment',
            'recorded_on' => $treatment->start_date,
            'health_status' => $this->healthStatusForTreatment($treatment),
            'title' => $title,
            'treatment' => $treatment->treatment_method ?? $treatment->dosage,
            'medication' => $treatment->medicine?->name ?? $treatment->medicine_name,
            'veterinarian' => $treatment->veterinarian_name,
            'next_follow_up' => $treatment->follow_up_date,
            'notes' => $notes !== '' ? $notes : null,
        ];

        if ($treatment->health_record_id) {
            HealthRecord::query()
                ->whereKey($treatment->health_record_id)
                ->update($healthData);
        } else {
            $record = HealthRecord::create($healthData);
            $treatment->update(['health_record_id' => $record->id]);
        }
    }

    private function healthStatusForTreatment(Treatment $treatment): string
    {
        return match ($treatment->status) {
            'Completed' => 'Recovering',
            'Ongoing' => 'Under treatment',
            default => $treatment->animal?->health_status ?? 'Under treatment',
        };
    }
}
