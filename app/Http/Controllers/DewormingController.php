<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HealthSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Controllers\Concerns\ProvidesStockOptions;
use App\Http\Requests\DewormingRequest;
use App\Models\Deworming;
use App\Models\ExpenseVendor;
use App\Models\HealthRecord;
use App\Models\Livestock;
use App\Services\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DewormingController extends Controller
{
    use HealthSectionViews;
    use ProvidesModuleNavigation;
    use ProvidesStockOptions;

    public function __construct(private ExpenseService $expenseService) {}

    public function create(): View
    {
        return view('modules.health.dewormings.create', $this->healthSectionData('dewormings', array_merge($this->stockOptions(), [
            'livestockGroups' => Livestock::query()->with('farm')->orderBy('name')->get(),
            'vendors' => ExpenseVendor::query()->where('is_active', true)->orderBy('name')->get(),
        ])));
    }

    public function store(DewormingRequest $request): RedirectResponse
    {
        $deworming = Deworming::create(array_merge($request->dewormingAttributes(), $request->resolvedStockAttributes()));

        $this->storeAttachment($request, $deworming);
        $this->syncHealthRecord($deworming);
        $this->expenseService->syncFromRequest($request, $deworming, 'health.deworming', ExpenseService::dewormingContext($deworming));

        return redirect()
            ->route('health.dewormings')
            ->with('success', __('Deworming saved successfully.'));
    }

    public function edit(Deworming $deworming): View
    {
        $deworming->load(['animal', 'farm', 'livestock', 'expense.vendor']);

        return view('modules.health.dewormings.edit', $this->healthSectionData('dewormings', array_merge($this->stockOptions(), [
            'deworming' => $deworming,
            'livestockGroups' => Livestock::query()->with('farm')->orderBy('name')->get(),
            'vendors' => ExpenseVendor::query()->where('is_active', true)->orderBy('name')->get(),
        ])));
    }

    public function update(DewormingRequest $request, Deworming $deworming): RedirectResponse
    {
        $deworming->update(array_merge($request->dewormingAttributes(), $request->resolvedStockAttributes()));

        $this->storeAttachment($request, $deworming);
        $deworming = $deworming->fresh();
        $this->syncHealthRecord($deworming);
        $this->expenseService->syncFromRequest($request, $deworming, 'health.deworming', ExpenseService::dewormingContext($deworming));

        return redirect()
            ->route('health.dewormings')
            ->with('success', __('Deworming updated successfully.'));
    }

    public function destroy(Deworming $deworming): RedirectResponse
    {
        if ($deworming->attachment_path) {
            Storage::disk('public')->delete($deworming->attachment_path);
        }

        $this->expenseService->deleteForSource($deworming);
        $deworming->healthRecord?->delete();
        $deworming->delete();

        return redirect()
            ->route('health.dewormings')
            ->with('success', __('Deworming removed successfully.'));
    }

    private function storeAttachment(DewormingRequest $request, Deworming $deworming): void
    {
        if (! $request->hasFile('attachment')) {
            return;
        }

        if ($deworming->attachment_path) {
            Storage::disk('public')->delete($deworming->attachment_path);
        }

        $path = $request->file('attachment')->store('dewormings/'.$deworming->id, 'public');
        $deworming->update(['attachment_path' => $path]);
    }

    private function syncHealthRecord(Deworming $deworming): void
    {
        $deworming->load(['animal', 'flock', 'livestock']);

        $summary = collect([
            $deworming->product_name,
            $deworming->product_type,
            $deworming->batch_number ? 'Batch '.$deworming->batch_number : null,
        ])->filter()->implode(' · ');

        $notes = collect([
            $deworming->livestock && ! $deworming->animal_id && ! $deworming->flock_id
                ? 'Livestock group: '.$deworming->livestock->name
                : null,
            $deworming->active_ingredient ? 'Active ingredient: '.$deworming->active_ingredient : null,
            $deworming->notes,
        ])->filter()->implode("\n\n");

        $healthData = [
            'farm_id' => $deworming->farm_id,
            'animal_id' => $deworming->animal_id,
            'flock_id' => $deworming->flock_id,
            'record_type' => 'Deworming',
            'recorded_on' => $deworming->deworming_date,
            'health_status' => $deworming->status === 'Completed' ? 'Healthy' : ($deworming->animal?->health_status ?? 'Healthy'),
            'title' => $summary,
            'treatment' => $deworming->dosage,
            'medication' => $deworming->product_name,
            'veterinarian' => $deworming->veterinarian_name,
            'next_follow_up' => $deworming->next_due_date,
            'notes' => $notes !== '' ? $notes : null,
        ];

        if ($deworming->health_record_id) {
            HealthRecord::query()
                ->whereKey($deworming->health_record_id)
                ->update($healthData);
        } else {
            $record = HealthRecord::create($healthData);
            $deworming->update(['health_record_id' => $record->id]);
        }
    }
}
