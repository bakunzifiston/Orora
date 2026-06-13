<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\DiseaseRecordRequest;
use App\Models\Animal;
use App\Models\DiseaseRecord;
use App\Models\ExpenseVendor;
use App\Models\Farm;
use App\Models\HealthRecord;
use App\Models\Livestock;
use App\Services\DiseaseRecordService;
use App\Services\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DiseaseRecordController extends Controller
{
    use ProvidesModuleNavigation;

    public function __construct(
        private readonly DiseaseRecordService $diseaseRecords,
        private readonly ExpenseService $expenseService,
    ) {}

    public function create(): View
    {
        return view('modules.health.disease.create', $this->healthViewData(array_merge(
            $this->formOptions(),
            [
                'vendors' => ExpenseVendor::query()->where('is_active', true)->orderBy('name')->get(),
            ],
        )));
    }

    public function store(DiseaseRecordRequest $request): RedirectResponse
    {
        $diseaseRecord = DiseaseRecord::create(array_merge($request->diseaseRecordAttributes(), [
            'disease_code' => $this->diseaseRecords->generateDiseaseCode($request->input('diagnosis_date')),
            'created_by' => auth()->id(),
        ]));

        $this->storeAttachment($request, $diseaseRecord);
        $diseaseRecord = $diseaseRecord->fresh();
        $this->syncAnimalHealthStatus($diseaseRecord);
        $this->syncHealthRecord($diseaseRecord);
        $this->expenseService->syncFromRequest(
            $request,
            $diseaseRecord,
            'health.diagnostics',
            ExpenseService::diseaseRecordContext($diseaseRecord),
        );

        return redirect()
            ->route('health.disease')
            ->with('success', 'Disease record saved successfully.');
    }

    public function edit(DiseaseRecord $diseaseRecord): View
    {
        $diseaseRecord->load(['animal.farm', 'farm', 'livestock', 'expense.vendor']);

        return view('modules.health.disease.edit', $this->healthViewData(array_merge(
            $this->formOptions($diseaseRecord),
            [
                'diseaseRecord' => $diseaseRecord,
                'vendors' => ExpenseVendor::query()->where('is_active', true)->orderBy('name')->get(),
            ],
        )));
    }

    public function update(DiseaseRecordRequest $request, DiseaseRecord $diseaseRecord): RedirectResponse
    {
        $diseaseRecord->update($request->diseaseRecordAttributes());

        $this->storeAttachment($request, $diseaseRecord);
        $diseaseRecord = $diseaseRecord->fresh();
        $this->syncAnimalHealthStatus($diseaseRecord);
        $this->syncHealthRecord($diseaseRecord);
        $this->expenseService->syncFromRequest(
            $request,
            $diseaseRecord,
            'health.diagnostics',
            ExpenseService::diseaseRecordContext($diseaseRecord),
        );

        return redirect()
            ->route('health.disease')
            ->with('success', 'Disease record updated successfully.');
    }

    public function destroy(DiseaseRecord $diseaseRecord): RedirectResponse
    {
        if ($diseaseRecord->attachment_path) {
            Storage::disk('public')->delete($diseaseRecord->attachment_path);
        }

        $this->expenseService->deleteForSource($diseaseRecord);
        $diseaseRecord->healthRecord?->delete();
        $diseaseRecord->delete();

        return redirect()
            ->route('health.disease')
            ->with('success', 'Disease record removed successfully.');
    }

    private function formOptions(?DiseaseRecord $diseaseRecord = null): array
    {
        $farmId = $diseaseRecord?->farm_id ?? old('farm_id');
        $livestockId = $diseaseRecord?->livestock_id ?? old('livestock_id');

        $livestockByFarm = Livestock::query()
            ->select(['id', 'farm_id', 'name'])
            ->orderBy('name')
            ->get()
            ->groupBy('farm_id')
            ->map(fn ($groups) => $groups->map(fn (Livestock $group) => [
                'id' => $group->id,
                'name' => $group->name,
            ])->values())
            ->toArray();

        $animals = Animal::query()
            ->with('farm:id,name')
            ->orderBy('tag_number')
            ->get(['id', 'farm_id', 'livestock_id', 'tag_number', 'name']);

        $animalsByLivestock = $animals
            ->groupBy('livestock_id')
            ->map(fn ($group) => $group->map(fn (Animal $animal) => [
                'id' => $animal->id,
                'label' => collect([
                    $animal->tag_number,
                    $animal->name,
                    $animal->farm?->name,
                ])->filter()->implode(' · '),
            ])->values())
            ->toArray();

        return [
            'farms' => Farm::query()->orderBy('name')->get(),
            'livestockGroups' => Livestock::query()
                ->when($farmId, fn ($query) => $query->where('farm_id', $farmId))
                ->orderBy('name')
                ->get(),
            'livestockByFarm' => $livestockByFarm,
            'animalsByLivestock' => $animalsByLivestock,
            'selectedFarmId' => $farmId,
            'selectedLivestockId' => $livestockId,
            'selectedAnimalId' => $diseaseRecord?->animal_id ?? old('animal_id'),
        ];
    }

    private function storeAttachment(DiseaseRecordRequest $request, DiseaseRecord $diseaseRecord): void
    {
        if (! $request->hasFile('attachment')) {
            return;
        }

        if ($diseaseRecord->attachment_path) {
            Storage::disk('public')->delete($diseaseRecord->attachment_path);
        }

        $path = $request->file('attachment')->store('disease-records/'.$diseaseRecord->id, 'public');
        $diseaseRecord->update(['attachment_path' => $path]);
    }

    private function syncHealthRecord(DiseaseRecord $diseaseRecord): void
    {
        $diseaseRecord->load(['animal', 'farm']);

        $notes = collect([
            $diseaseRecord->symptoms ? 'Symptoms: '.$diseaseRecord->symptoms : null,
            'Severity: '.$diseaseRecord->severityLabel(),
            'Recovery: '.$diseaseRecord->recoveryLabel(),
            'Contagious: '.$diseaseRecord->contagiousLabel(),
            $diseaseRecord->quarantine_required ? 'Quarantine required.' : null,
            $diseaseRecord->notes,
        ])->filter()->implode("\n\n");

        $healthData = [
            'farm_id' => $diseaseRecord->farm_id,
            'animal_id' => $diseaseRecord->animal_id,
            'record_type' => 'Illness',
            'recorded_on' => $diseaseRecord->diagnosis_date,
            'health_status' => $this->healthStatusForRecord($diseaseRecord),
            'title' => $diseaseRecord->disease_name,
            'treatment' => null,
            'medication' => null,
            'veterinarian' => $diseaseRecord->veterinarian_name,
            'next_follow_up' => null,
            'notes' => $notes !== '' ? $notes : null,
        ];

        if ($diseaseRecord->health_record_id) {
            HealthRecord::query()
                ->whereKey($diseaseRecord->health_record_id)
                ->update($healthData);
        } else {
            $record = HealthRecord::create($healthData);
            $diseaseRecord->update(['health_record_id' => $record->id]);
        }
    }

    private function syncAnimalHealthStatus(DiseaseRecord $diseaseRecord): void
    {
        $diseaseRecord->animal?->update([
            'health_status' => $this->healthStatusForRecord($diseaseRecord),
        ]);
    }

    private function healthStatusForRecord(DiseaseRecord $diseaseRecord): string
    {
        if ($diseaseRecord->quarantine_required) {
            return 'Quarantined';
        }

        return match ($diseaseRecord->recovery_status) {
            'recovered' => 'Healthy',
            'recovering' => 'Recovering',
            'chronic' => 'Sick',
            'dead' => 'Deceased',
            default => 'Sick',
        };
    }

    private function healthViewData(array $data = []): array
    {
        return array_merge($this->moduleViewData('health', [
            'activeHealthSection' => 'disease',
            'healthSections' => config('modules.health_sections'),
        ]), $data);
    }
}
