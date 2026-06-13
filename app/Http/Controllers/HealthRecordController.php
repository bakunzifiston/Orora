<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HealthSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\HealthRecordRequest;
use App\Models\Animal;
use App\Models\Farm;
use App\Models\HealthRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HealthRecordController extends Controller
{
    use HealthSectionViews;
    use ProvidesModuleNavigation;

    public function create(Request $request): View
    {
        $defaultType = $request->query('type');
        $returnSection = $request->query('section', 'overview');

        if ($defaultType && ! in_array($defaultType, config('modules.health_record_types'), true)) {
            $defaultType = null;
        }

        return view('modules.health.create', $this->healthSectionData($returnSection, array_merge($this->formOptions(), [
            'defaultType' => $defaultType,
            'returnSection' => $returnSection,
        ])));
    }

    public function store(HealthRecordRequest $request): RedirectResponse
    {
        $record = HealthRecord::create($request->healthRecordAttributes());
        $this->syncAnimalHealthStatus($record);

        return redirect()
            ->route($this->sectionRouteFor($request->input('return_section'), $record))
            ->with('success', 'Health record logged successfully.');
    }

    public function edit(HealthRecord $record, Request $request): View
    {
        return view('modules.health.edit', $this->healthSectionData($request->query('section', $this->sectionKeyForRecord($record)), array_merge($this->formOptions(), [
            'healthRecord' => $record,
            'returnSection' => $request->query('section', $this->sectionKeyForRecord($record)),
        ])));
    }

    public function update(HealthRecordRequest $request, HealthRecord $record): RedirectResponse
    {
        $record->update($request->healthRecordAttributes());
        $this->syncAnimalHealthStatus($record);

        return redirect()
            ->route($this->sectionRouteFor($request->input('return_section'), $record))
            ->with('success', 'Health record updated successfully.');
    }

    public function destroy(HealthRecord $record, Request $request): RedirectResponse
    {
        $section = $request->query('section', $this->sectionKeyForRecord($record));
        $record->delete();

        return redirect()
            ->route($this->sectionRouteFor($section))
            ->with('success', 'Health record removed successfully.');
    }

    private function formOptions(): array
    {
        return [
            'farms' => Farm::query()->orderBy('name')->get(),
            'animals' => Animal::query()->with('farm')->orderBy('tag_number')->get(),
        ];
    }

    private function syncAnimalHealthStatus(HealthRecord $record): void
    {
        $record->animal()->update(['health_status' => $record->health_status]);

        if ($record->record_type === 'Mortality' || $record->health_status === 'Deceased') {
            $record->animal()->update([
                'lifecycle_status' => 'Deceased',
                'health_status' => 'Deceased',
            ]);
        }
    }

    private function sectionKeyForRecord(HealthRecord $record): string
    {
        foreach (config('modules.health_section_record_types', []) as $section => $types) {
            if (in_array($record->record_type, $types, true)) {
                return $section;
            }
        }

        if ($record->record_type === 'Mortality') {
            return 'mortality';
        }

        return 'overview';
    }

    private function sectionRouteFor(string $section, ?HealthRecord $record = null): string
    {
        $section = $section ?: ($record ? $this->sectionKeyForRecord($record) : 'overview');

        $route = collect(config('modules.health_sections'))
            ->firstWhere('key', $section)['route'] ?? 'health.overview';

        return $route;
    }
}
