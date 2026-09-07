<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HealthSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Controllers\Concerns\ProvidesStockOptions;
use App\Http\Requests\MortalityRequest;
use App\Models\Animal;
use App\Models\HealthRecord;
use App\Models\Mortality;
use App\Services\FlockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MortalityController extends Controller
{
    use HealthSectionViews;
    use ProvidesModuleNavigation;
    use ProvidesStockOptions;

    public function __construct(private readonly FlockService $flocks) {}

    public function create(): View
    {
        return view('modules.health.mortalities.create', $this->healthSectionData('mortality', $this->stockOptions()));
    }

    public function store(MortalityRequest $request): RedirectResponse
    {
        $stock = $request->resolvedStockAttributes();
        $mortality = Mortality::create(array_merge($request->mortalityAttributes(), $stock));

        $this->storeAttachment($request, $mortality);

        if ($mortality->animal_id) {
            $this->syncAnimalStatus($mortality->animal);
        }

        if ($mortality->flock_id) {
            $this->flocks->recordMortality(
                $mortality->flock,
                (int) $mortality->deaths_count,
                $mortality->death_date,
                $mortality->cause_of_death,
                $mortality,
            );
        }

        $this->syncHealthRecord($mortality);

        return redirect()
            ->route('health.mortality')
            ->with('success', 'Mortality record saved successfully.');
    }

    public function edit(Mortality $mortality): View
    {
        $mortality->load(['animal', 'farm', 'flock']);

        return view('modules.health.mortalities.edit', $this->healthSectionData('mortality', array_merge(
            $this->stockOptions(),
            ['mortality' => $mortality],
        )));
    }

    public function update(MortalityRequest $request, Mortality $mortality): RedirectResponse
    {
        $previousFlockId = $mortality->flock_id;
        $previousDeaths = (int) $mortality->deaths_count;

        $stock = $request->resolvedStockAttributes();
        $mortality->update(array_merge($request->mortalityAttributes(), $stock));
        $mortality = $mortality->fresh();

        $this->storeAttachment($request, $mortality);

        if ($mortality->animal_id) {
            $this->syncAnimalStatus($mortality->animal);
        }

        $this->syncFlockCountOnUpdate($mortality, $previousFlockId, $previousDeaths);
        $this->syncHealthRecord($mortality);

        return redirect()
            ->route('health.mortality')
            ->with('success', 'Mortality record updated successfully.');
    }

    public function destroy(Mortality $mortality): RedirectResponse
    {
        if ($mortality->attachment_path) {
            Storage::disk('public')->delete($mortality->attachment_path);
        }

        if ($mortality->flock_id && $mortality->flock) {
            $this->flocks->adjust(
                $mortality->flock,
                'adjustment',
                (int) $mortality->deaths_count,
                now(),
                'Mortality record removed',
                $mortality,
            );
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

    private function syncFlockCountOnUpdate(Mortality $mortality, mixed $previousFlockId, int $previousDeaths): void
    {
        if (! $mortality->flock_id) {
            if ($previousFlockId) {
                $previous = \App\Models\Flock::query()->find($previousFlockId);
                if ($previous) {
                    $this->flocks->adjust($previous, 'adjustment', $previousDeaths, now(), 'Mortality reassigned', $mortality);
                }
            }

            return;
        }

        $delta = $previousFlockId == $mortality->flock_id
            ? ((int) $mortality->deaths_count - $previousDeaths)
            : (int) $mortality->deaths_count;

        if ($previousFlockId && $previousFlockId != $mortality->flock_id) {
            $previous = \App\Models\Flock::query()->find($previousFlockId);
            if ($previous) {
                $this->flocks->adjust($previous, 'adjustment', $previousDeaths, now(), 'Mortality reassigned', $mortality);
            }
        }

        if ($delta !== 0) {
            $this->flocks->adjust(
                $mortality->flock,
                $delta > 0 ? 'mortality' : 'adjustment',
                -$delta,
                $mortality->death_date,
                $mortality->cause_of_death,
                $mortality,
            );
        }
    }

    private function syncHealthRecord(Mortality $mortality): void
    {
        $mortality->load(['animal', 'flock']);

        $notes = collect([
            $mortality->flock_id && $mortality->deaths_count
                ? 'Deaths: '.$mortality->deaths_count
                : null,
            $mortality->reported_by ? 'Reported by: '.$mortality->reported_by : null,
            $mortality->disposal_method ? 'Disposal: '.$mortality->disposal_method : null,
            $mortality->postmortem_done ? 'Postmortem: Yes' : null,
            $mortality->notes,
        ])->filter()->implode("\n\n");

        $healthData = [
            'farm_id' => $mortality->farm_id,
            'animal_id' => $mortality->animal_id,
            'flock_id' => $mortality->flock_id,
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
}
