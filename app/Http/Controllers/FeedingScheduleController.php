<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FeedingSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\FeedingScheduleRequest;
use App\Models\Animal;
use App\Models\Farm;
use App\Models\FeedingSchedule;
use App\Models\FeedInventory;
use App\Models\FeedType;
use App\Models\Livestock;
use App\Services\FeedInventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeedingScheduleController extends Controller
{
    use FeedingSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(private FeedInventoryService $inventoryService) {}

    public function index(): View
    {
        $schedules = FeedingSchedule::query()
            ->with(['farm', 'feedType', 'animal', 'livestock'])
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('modules.feeding.schedules.index', $this->feedingSectionData('schedules', compact('schedules')));
    }

    public function create(): View
    {
        return view('modules.feeding.schedules.create', $this->feedingSectionData('schedules', $this->formOptions()));
    }

    public function store(FeedingScheduleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $feedType = FeedType::query()->findOrFail($data['feed_type_id']);

        $data['feed_inventory_id'] = $data['feed_inventory_id']
            ?? $this->inventoryService->ensureInventory($data['farm_id'], $data['feed_type_id'], $feedType->unit)->id;

        if (empty($data['next_due_date'])) {
            $data['next_due_date'] = $data['start_date'];
        }

        FeedingSchedule::create($data);

        return redirect()->route('feeding.schedules')->with('success', 'Feeding schedule saved successfully.');
    }

    public function edit(FeedingSchedule $schedule): View
    {
        return view('modules.feeding.schedules.edit', $this->feedingSectionData('schedules', array_merge(
            $this->formOptions(),
            ['schedule' => $schedule],
        )));
    }

    public function update(FeedingScheduleRequest $request, FeedingSchedule $schedule): RedirectResponse
    {
        $data = $request->validated();
        $feedType = FeedType::query()->findOrFail($data['feed_type_id']);

        $data['feed_inventory_id'] = $data['feed_inventory_id']
            ?? $this->inventoryService->ensureInventory($data['farm_id'], $data['feed_type_id'], $feedType->unit)->id;

        $schedule->update($data);

        return redirect()->route('feeding.schedules')->with('success', 'Feeding schedule updated successfully.');
    }

    public function destroy(FeedingSchedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return redirect()->route('feeding.schedules')->with('success', 'Feeding schedule removed successfully.');
    }

    private function formOptions(): array
    {
        return [
            'farms' => Farm::query()->orderBy('name')->get(),
            'feedTypes' => FeedType::query()->where('is_active', true)->orderBy('name')->get(),
            'inventories' => FeedInventory::query()->with(['farm', 'feedType'])->get(),
            'livestockGroups' => Livestock::query()->orderBy('name')->get(),
            'animals' => Animal::query()->orderBy('tag_number')->get(),
        ];
    }
}
