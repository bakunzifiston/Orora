<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FeedingSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\FeedingRecordRequest;
use App\Models\Animal;
use App\Models\Farm;
use App\Models\Feeding;
use App\Models\FeedingSchedule;
use App\Models\FeedInventory;
use App\Models\Livestock;
use App\Services\FeedInventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeedingController extends Controller
{
    use FeedingSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(private FeedInventoryService $inventoryService) {}

    public function index(): View
    {
        $feedings = Feeding::query()
            ->with(['farm', 'livestock', 'animal', 'feedType', 'feedInventory', 'feedingSchedule'])
            ->orderByDesc('fed_on')
            ->paginate(15);

        return view('modules.feedings.index', $this->feedingSectionData('records', compact('feedings')));
    }

    public function create(): View
    {
        return view('modules.feedings.create', $this->feedingSectionData('records', $this->formOptions()));
    }

    public function store(FeedingRecordRequest $request): RedirectResponse
    {
        $inventory = FeedInventory::query()->with('feedType')->findOrFail($request->input('feed_inventory_id'));

        $feeding = Feeding::create([
            'farm_id' => $request->input('farm_id'),
            'feed_type_id' => $inventory->feed_type_id,
            'feed_inventory_id' => $inventory->id,
            'feeding_schedule_id' => $request->input('feeding_schedule_id'),
            'livestock_id' => $request->input('livestock_id'),
            'animal_id' => $request->input('animal_id'),
            'quantity' => $request->input('quantity'),
            'unit' => $inventory->unit,
            'fed_on' => $request->input('fed_on'),
            'notes' => $request->input('notes'),
        ]);

        try {
            $this->inventoryService->deductForFeeding($feeding);
        } catch (\InvalidArgumentException $e) {
            $feeding->delete();

            return back()->withInput()->withErrors(['quantity' => $e->getMessage()]);
        }

        return redirect()->route('feeding.records')->with('success', 'Feeding record created successfully.');
    }

    public function edit(Feeding $feeding): View
    {
        return view('modules.feedings.edit', $this->feedingSectionData('records', array_merge(
            $this->formOptions(),
            compact('feeding'),
        )));
    }

    public function update(FeedingRecordRequest $request, Feeding $feeding): RedirectResponse
    {
        $inventory = FeedInventory::query()->findOrFail($request->input('feed_inventory_id'));

        $feeding->update([
            'farm_id' => $request->input('farm_id'),
            'feed_type_id' => $inventory->feed_type_id,
            'feed_inventory_id' => $inventory->id,
            'feeding_schedule_id' => $request->input('feeding_schedule_id'),
            'livestock_id' => $request->input('livestock_id'),
            'animal_id' => $request->input('animal_id'),
            'quantity' => $request->input('quantity'),
            'unit' => $inventory->unit,
            'fed_on' => $request->input('fed_on'),
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('feeding.records')->with('success', 'Feeding record updated successfully.');
    }

    public function destroy(Feeding $feeding): RedirectResponse
    {
        $feeding->delete();

        return redirect()->route('feeding.records')->with('success', 'Feeding record removed successfully.');
    }

    private function formOptions(): array
    {
        return [
            'farms' => Farm::query()->orderBy('name')->get(),
            'inventories' => FeedInventory::query()->with(['farm', 'feedType'])->get(),
            'schedules' => FeedingSchedule::query()->where('status', 'active')->orderBy('farm_id')->get(),
            'livestockGroups' => Livestock::query()->orderBy('name')->get(),
            'animals' => Animal::query()->orderBy('tag_number')->get(),
        ];
    }
}
