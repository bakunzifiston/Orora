<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\TreatmentRequest;
use App\Models\Animal;
use App\Models\ExpenseVendor;
use App\Models\HealthRecord;
use App\Models\Treatment;
use App\Services\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TreatmentController extends Controller
{
    use ProvidesModuleNavigation;

    public function __construct(private ExpenseService $expenseService) {}

    public function create(): View
    {
        return view('modules.health.treatments.create', $this->healthViewData([
            'animals' => Animal::query()->with('farm')->orderBy('tag_number')->get(),
            'vendors' => ExpenseVendor::query()->where('is_active', true)->orderBy('name')->get(),
        ]));
    }

    public function store(TreatmentRequest $request): RedirectResponse
    {
        $animal = Animal::query()->findOrFail($request->input('animal_id'));

        $treatment = Treatment::create(array_merge($request->treatmentAttributes(), [
            'farm_id' => $animal->farm_id,
        ]));

        $this->storeAttachment($request, $treatment);
        $this->syncHealthRecord($treatment);
        $this->expenseService->syncFromRequest($request, $treatment, 'health.treatment', ExpenseService::treatmentContext($treatment));

        return redirect()
            ->route('health.treatments')
            ->with('success', 'Treatment saved successfully.');
    }

    public function edit(Treatment $treatment): View
    {
        $treatment->load(['animal', 'farm', 'expense.vendor']);

        return view('modules.health.treatments.edit', $this->healthViewData([
            'treatment' => $treatment,
            'animals' => Animal::query()->with('farm')->orderBy('tag_number')->get(),
            'vendors' => ExpenseVendor::query()->where('is_active', true)->orderBy('name')->get(),
        ]));
    }

    public function update(TreatmentRequest $request, Treatment $treatment): RedirectResponse
    {
        $animal = Animal::query()->findOrFail($request->input('animal_id'));

        $treatment->update(array_merge($request->treatmentAttributes(), [
            'farm_id' => $animal->farm_id,
        ]));

        $this->storeAttachment($request, $treatment);
        $treatment = $treatment->fresh();
        $this->syncHealthRecord($treatment);
        $this->expenseService->syncFromRequest($request, $treatment, 'health.treatment', ExpenseService::treatmentContext($treatment));

        return redirect()
            ->route('health.treatments')
            ->with('success', 'Treatment updated successfully.');
    }

    public function destroy(Treatment $treatment): RedirectResponse
    {
        if ($treatment->attachment_path) {
            Storage::disk('public')->delete($treatment->attachment_path);
        }

        $this->expenseService->deleteForSource($treatment);
        $treatment->healthRecord?->delete();
        $treatment->delete();

        return redirect()
            ->route('health.treatments')
            ->with('success', 'Treatment removed successfully.');
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
        $treatment->load('animal');

        $title = collect([$treatment->disease_name, $treatment->medicine_name])
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
            'record_type' => 'Treatment',
            'recorded_on' => $treatment->start_date,
            'health_status' => $this->healthStatusForTreatment($treatment),
            'title' => $title,
            'treatment' => $treatment->treatment_method ?? $treatment->dosage,
            'medication' => $treatment->medicine_name,
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
            default => $treatment->animal->health_status,
        };
    }

    private function healthViewData(array $data = []): array
    {
        return array_merge($this->moduleViewData('health', [
            'activeHealthSection' => 'treatments',
            'healthSections' => config('modules.health_sections'),
        ]), $data);
    }
}
