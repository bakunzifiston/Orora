<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HealthSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\VaccinationRequest;
use App\Models\Animal;
use App\Models\ExpenseVendor;
use App\Models\HealthRecord;
use App\Models\Vaccination;
use App\Services\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VaccinationController extends Controller
{
    use HealthSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(private ExpenseService $expenseService) {}

    public function create(): View
    {
        return view('modules.health.vaccinations.create', $this->healthSectionData('vaccinations', [
            'animals' => Animal::query()->with('farm')->orderBy('tag_number')->get(),
            'vendors' => ExpenseVendor::query()->where('is_active', true)->orderBy('name')->get(),
        ]));
    }

    public function store(VaccinationRequest $request): RedirectResponse
    {
        $animal = Animal::query()->findOrFail($request->input('animal_id'));

        $vaccination = Vaccination::create(array_merge($request->vaccinationAttributes(), [
            'farm_id' => $animal->farm_id,
        ]));

        $this->storeAttachment($request, $vaccination);
        $this->syncHealthRecord($vaccination);
        $this->expenseService->syncFromRequest($request, $vaccination, 'health.vaccination', ExpenseService::vaccinationContext($vaccination));

        return redirect()
            ->route('health.vaccinations')
            ->with('success', 'Vaccination saved successfully.');
    }

    public function edit(Vaccination $vaccination): View
    {
        $vaccination->load(['animal', 'farm', 'expense.vendor']);

        return view('modules.health.vaccinations.edit', $this->healthSectionData('vaccinations', [
            'vaccination' => $vaccination,
            'animals' => Animal::query()->with('farm')->orderBy('tag_number')->get(),
            'vendors' => ExpenseVendor::query()->where('is_active', true)->orderBy('name')->get(),
        ]));
    }

    public function update(VaccinationRequest $request, Vaccination $vaccination): RedirectResponse
    {
        $animal = Animal::query()->findOrFail($request->input('animal_id'));

        $vaccination->update(array_merge($request->vaccinationAttributes(), [
            'farm_id' => $animal->farm_id,
        ]));

        $this->storeAttachment($request, $vaccination);
        $vaccination = $vaccination->fresh();
        $this->syncHealthRecord($vaccination);
        $this->expenseService->syncFromRequest($request, $vaccination, 'health.vaccination', ExpenseService::vaccinationContext($vaccination));

        return redirect()
            ->route('health.vaccinations')
            ->with('success', 'Vaccination updated successfully.');
    }

    public function destroy(Vaccination $vaccination): RedirectResponse
    {
        if ($vaccination->attachment_path) {
            Storage::disk('public')->delete($vaccination->attachment_path);
        }

        $this->expenseService->deleteForSource($vaccination);
        $vaccination->healthRecord?->delete();
        $vaccination->delete();

        return redirect()
            ->route('health.vaccinations')
            ->with('success', 'Vaccination removed successfully.');
    }

    private function storeAttachment(VaccinationRequest $request, Vaccination $vaccination): void
    {
        if (! $request->hasFile('attachment')) {
            return;
        }

        if ($vaccination->attachment_path) {
            Storage::disk('public')->delete($vaccination->attachment_path);
        }

        $path = $request->file('attachment')->store('vaccinations/'.$vaccination->id, 'public');
        $vaccination->update(['attachment_path' => $path]);
    }

    private function syncHealthRecord(Vaccination $vaccination): void
    {
        $vaccination->load('animal');

        $summary = collect([
            $vaccination->vaccine_name,
            $vaccination->vaccine_type,
            $vaccination->batch_number ? 'Batch '.$vaccination->batch_number : null,
        ])->filter()->implode(' · ');

        $healthData = [
            'farm_id' => $vaccination->farm_id,
            'animal_id' => $vaccination->animal_id,
            'record_type' => 'Vaccination',
            'recorded_on' => $vaccination->vaccination_date,
            'health_status' => $vaccination->status === 'Completed' ? 'Healthy' : $vaccination->animal->health_status,
            'title' => $summary,
            'treatment' => $vaccination->dosage,
            'medication' => $vaccination->vaccine_name,
            'veterinarian' => $vaccination->veterinarian_name,
            'next_follow_up' => $vaccination->next_due_date,
            'notes' => $vaccination->notes,
        ];

        if ($vaccination->health_record_id) {
            HealthRecord::query()
                ->whereKey($vaccination->health_record_id)
                ->update($healthData);
        } else {
            $record = HealthRecord::create($healthData);
            $vaccination->update(['health_record_id' => $record->id]);
        }
    }
}
