<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\MortalityRequest;
use App\Models\Animal;
use App\Models\HealthRecord;
use App\Models\Mortality;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MortalityController extends Controller
{
    use ProvidesModuleNavigation;

    public function create(): View
    {
        return view('modules.health.mortalities.create', $this->healthViewData([
            'animals' => Animal::query()->with('farm')->orderBy('tag_number')->get(),
        ]));
    }

    public function store(MortalityRequest $request): RedirectResponse
    {
        $animal = Animal::query()->findOrFail($request->input('animal_id'));

        $mortality = Mortality::create(array_merge($request->mortalityAttributes(), [
            'farm_id' => $animal->farm_id,
        ]));

        $this->storeAttachment($request, $mortality);
        $this->syncAnimalStatus($animal);
        $this->syncHealthRecord($mortality);

        return redirect()
            ->route('health.mortality')
            ->with('success', 'Mortality record saved successfully.');
    }

    public function edit(Mortality $mortality): View
    {
        $mortality->load(['animal', 'farm']);

        return view('modules.health.mortalities.edit', $this->healthViewData([
            'mortality' => $mortality,
            'animals' => Animal::query()->with('farm')->orderBy('tag_number')->get(),
        ]));
    }

    public function update(MortalityRequest $request, Mortality $mortality): RedirectResponse
    {
        $animal = Animal::query()->findOrFail($request->input('animal_id'));

        $mortality->update(array_merge($request->mortalityAttributes(), [
            'farm_id' => $animal->farm_id,
        ]));

        $this->storeAttachment($request, $mortality);
        $this->syncAnimalStatus($animal);
        $this->syncHealthRecord($mortality->fresh());

        return redirect()
            ->route('health.mortality')
            ->with('success', 'Mortality record updated successfully.');
    }

    public function destroy(Mortality $mortality): RedirectResponse
    {
        if ($mortality->attachment_path) {
            Storage::disk('public')->delete($mortality->attachment_path);
        }

        $mortality->healthRecord?->delete();
        $mortality->delete();

        return redirect()
            ->route('health.mortality')
            ->with('success', 'Mortality record removed successfully.');
    }

    private function storeAttachment(MortalityRequest $request, Mortality $mortality): void
    {
        if (! $request->hasFile('attachment')) {
            return;
        }

        if ($mortality->attachment_path) {
            Storage::disk('public')->delete($mortality->attachment_path);
        }

        $path = $request->file('attachment')->store('mortalities/'.$mortality->id, 'public');
        $mortality->update(['attachment_path' => $path]);
    }

    private function syncAnimalStatus(Animal $animal): void
    {
        $animal->update([
            'health_status' => 'Deceased',
            'lifecycle_status' => 'Deceased',
        ]);
    }

    private function syncHealthRecord(Mortality $mortality): void
    {
        $mortality->load('animal');

        $notes = collect([
            $mortality->reported_by ? 'Reported by: '.$mortality->reported_by : null,
            $mortality->disposal_method ? 'Disposal: '.$mortality->disposal_method : null,
            $mortality->postmortem_done ? 'Postmortem: Yes' : null,
            $mortality->notes,
        ])->filter()->implode("\n\n");

        $healthData = [
            'farm_id' => $mortality->farm_id,
            'animal_id' => $mortality->animal_id,
            'record_type' => 'Mortality',
            'recorded_on' => $mortality->death_date,
            'health_status' => 'Deceased',
            'title' => $mortality->cause_of_death,
            'veterinarian' => $mortality->veterinarian_name,
            'notes' => $notes !== '' ? $notes : null,
        ];

        if ($mortality->health_record_id) {
            HealthRecord::query()
                ->whereKey($mortality->health_record_id)
                ->update($healthData);
        } else {
            $record = HealthRecord::create($healthData);
            $mortality->update(['health_record_id' => $record->id]);
        }
    }

    private function healthViewData(array $data = []): array
    {
        return array_merge($this->moduleViewData('health', [
            'activeHealthSection' => 'mortality',
            'healthSections' => config('modules.health_sections'),
        ]), $data);
    }
}
