<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\VetVisitRequest;
use App\Models\Animal;
use App\Models\HealthRecord;
use App\Models\VetVisit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VetVisitController extends Controller
{
    use ProvidesModuleNavigation;

    public function create(): View
    {
        return view('modules.health.vet-visits.create', $this->healthViewData([
            'animals' => Animal::query()->with('farm')->orderBy('tag_number')->get(),
        ]));
    }

    public function store(VetVisitRequest $request): RedirectResponse
    {
        $animal = Animal::query()->findOrFail($request->input('animal_id'));

        $vetVisit = VetVisit::create(array_merge($request->vetVisitAttributes(), [
            'farm_id' => $animal->farm_id,
        ]));

        $this->storeAttachment($request, $vetVisit);
        $this->syncHealthRecord($vetVisit);

        return redirect()
            ->route('health.vet-visits')
            ->with('success', 'Vet visit saved successfully.');
    }

    public function edit(VetVisit $vetVisit): View
    {
        $vetVisit->load(['animal', 'farm']);

        return view('modules.health.vet-visits.edit', $this->healthViewData([
            'vetVisit' => $vetVisit,
            'animals' => Animal::query()->with('farm')->orderBy('tag_number')->get(),
        ]));
    }

    public function update(VetVisitRequest $request, VetVisit $vetVisit): RedirectResponse
    {
        $animal = Animal::query()->findOrFail($request->input('animal_id'));

        $vetVisit->update(array_merge($request->vetVisitAttributes(), [
            'farm_id' => $animal->farm_id,
        ]));

        $this->storeAttachment($request, $vetVisit);
        $this->syncHealthRecord($vetVisit->fresh());

        return redirect()
            ->route('health.vet-visits')
            ->with('success', 'Vet visit updated successfully.');
    }

    public function destroy(VetVisit $vetVisit): RedirectResponse
    {
        if ($vetVisit->attachment_path) {
            Storage::disk('public')->delete($vetVisit->attachment_path);
        }

        $vetVisit->healthRecord?->delete();
        $vetVisit->delete();

        return redirect()
            ->route('health.vet-visits')
            ->with('success', 'Vet visit removed successfully.');
    }

    private function storeAttachment(VetVisitRequest $request, VetVisit $vetVisit): void
    {
        if (! $request->hasFile('attachment')) {
            return;
        }

        if ($vetVisit->attachment_path) {
            Storage::disk('public')->delete($vetVisit->attachment_path);
        }

        $path = $request->file('attachment')->store('vet-visits/'.$vetVisit->id, 'public');
        $vetVisit->update(['attachment_path' => $path]);
    }

    private function syncHealthRecord(VetVisit $vetVisit): void
    {
        $vetVisit->load('animal');

        $title = collect([$vetVisit->disease_name, $vetVisit->medicine_name])
            ->filter()
            ->implode(' · ');

        $notes = collect([
            $vetVisit->symptoms ? 'Symptoms: '.$vetVisit->symptoms : null,
            $vetVisit->diagnosis ? 'Diagnosis: '.$vetVisit->diagnosis : null,
            $vetVisit->notes,
        ])->filter()->implode("\n\n");

        $healthData = [
            'farm_id' => $vetVisit->farm_id,
            'animal_id' => $vetVisit->animal_id,
            'record_type' => 'Vet visit',
            'recorded_on' => $vetVisit->start_date,
            'health_status' => $this->healthStatusForVisit($vetVisit),
            'title' => $title,
            'treatment' => $vetVisit->treatment_method ?? $vetVisit->dosage,
            'medication' => $vetVisit->medicine_name,
            'veterinarian' => $vetVisit->veterinarian_name,
            'next_follow_up' => $vetVisit->follow_up_date,
            'notes' => $notes !== '' ? $notes : null,
        ];

        if ($vetVisit->health_record_id) {
            HealthRecord::query()
                ->whereKey($vetVisit->health_record_id)
                ->update($healthData);
        } else {
            $record = HealthRecord::create($healthData);
            $vetVisit->update(['health_record_id' => $record->id]);
        }
    }

    private function healthStatusForVisit(VetVisit $vetVisit): string
    {
        return match ($vetVisit->status) {
            'Completed' => 'Recovering',
            'Ongoing' => 'Under treatment',
            default => $vetVisit->animal->health_status,
        };
    }

    private function healthViewData(array $data = []): array
    {
        return array_merge($this->moduleViewData('health', [
            'activeHealthSection' => 'vet-visits',
            'healthSections' => config('modules.health_sections'),
        ]), $data);
    }
}
