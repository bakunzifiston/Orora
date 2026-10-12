<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FeedingSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Controllers\Concerns\ProvidesStockOptions;
use App\Http\Requests\FeedingRecordRequest;
use App\Models\Farm;
use App\Models\Feeding;
use App\Models\FeedingSchedule;
use App\Models\FeedInventory;
use App\Models\Livestock;
use App\Services\FeedInventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FeedingController extends Controller
{
    use FeedingSectionViews;
    use ProvidesModuleNavigation;
    use ProvidesStockOptions;

    public function __construct(private FeedInventoryService $inventoryService) {}

    public function index(): View
    {
        $feedings = Feeding::query()
            ->with(['farm', 'livestock', 'animal', 'flock', 'feedType', 'feedInventory', 'feedingSchedule'])
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
        try {
            DB::transaction(function () use ($request) {
                $inventory = FeedInventory::query()
                    ->with('feedType')
                    ->findOrFail($request->input('feed_inventory_id'));

                $feeding = Feeding::create([
                    'farm_id' => $request->input('farm_id'),
                    'feed_type_id' => $inventory->feed_type_id,
                    'feed_inventory_id' => $inventory->id,
                    'feeding_schedule_id' => $request->input('feeding_schedule_id'),
                    'livestock_id' => $request->input('livestock_id'),
                    'animal_id' => $request->input('animal_id'),
                    'flock_id' => $request->input('flock_id'),
                    'quantity' => $request->input('quantity'),
                    'unit' => $inventory->unit,
                    'fed_on' => $request->input('fed_on'),
                    'notes' => $request->input('notes'),
                ]);

                $this->inventoryService->deductForFeeding($feeding);
            });
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['quantity' => $e->getMessage()]);
        }

        return redirect()->route('feeding.records')->with('success', __('Feeding record created successfully.'));
    }

    public function edit(Feeding $feeding): View
    {
        return view('modules.feedings.edit', $this->feedingSectionData('records', array_merge(
            $this->formOptions($feeding),
            compact('feeding'),
        )));
    }

    public function update(FeedingRecordRequest $request, Feeding $feeding): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $feeding) {
                $inventory = FeedInventory::query()
                    ->with('feedType')
                    ->findOrFail($request->input('feed_inventory_id'));

                // Put previous consumption back before applying the new values.
                $this->inventoryService->restoreForFeeding($feeding);

                $feeding->update([
                    'farm_id' => $request->input('farm_id'),
                    'feed_type_id' => $inventory->feed_type_id,
                    'feed_inventory_id' => $inventory->id,
                    'feeding_schedule_id' => $request->input('feeding_schedule_id'),
                    'livestock_id' => $request->input('livestock_id'),
                    'animal_id' => $request->input('animal_id'),
                    'flock_id' => $request->input('flock_id'),
                    'quantity' => $request->input('quantity'),
                    'unit' => $inventory->unit,
                    'fed_on' => $request->input('fed_on'),
                    'notes' => $request->input('notes'),
                ]);

                $this->inventoryService->deductForFeeding($feeding->fresh(['feedInventory.feedType']));
            });
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['quantity' => $e->getMessage()]);
        }

        return redirect()->route('feeding.records')->with('success', __('Feeding record updated successfully.'));
    }

    public function destroy(Feeding $feeding): RedirectResponse
    {
        DB::transaction(function () use ($feeding) {
            $this->inventoryService->restoreForFeeding($feeding);
            $feeding->delete();
        });

        return redirect()->route('feeding.records')->with('success', __('Feeding record removed successfully.'));
    }

    private function formOptions(?Feeding $feeding = null): array
    {
        $inventories = FeedInventory::query()
            ->with(['farm', 'feedType'])
            ->where(function ($q) use ($feeding) {
                $q->whereHas('feedType', fn ($type) => $type->where('is_active', true));

                if ($feeding?->feed_inventory_id) {
                    $q->orWhere('id', $feeding->feed_inventory_id);
                }
            })
            ->orderBy('farm_id')
            ->get();

        return [
            'farms' => Farm::query()->orderBy('name')->get(),
            'inventories' => $inventories,
            'schedules' => $this->scheduleOptions(),
            'livestockGroups' => Livestock::query()->with('farm')->orderBy('name')->get(),
            ...$this->stockOptions(),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, FeedingSchedule>
     */
    private function scheduleOptions()
    {
        $schedules = FeedingSchedule::query()
            ->with(['farm', 'feedType', 'livestock', 'animal', 'flock'])
            ->where('status', 'active')
            ->orderBy('farm_id')
            ->orderBy('scheduled_time')
            ->get();

        $labelCounts = $schedules->countBy(fn (FeedingSchedule $schedule) => $schedule->optionLabel());

        return $schedules->each(function (FeedingSchedule $schedule) use ($labelCounts) {
            $label = $schedule->optionLabel();

            $schedule->setAttribute(
                'select_label',
                ($labelCounts[$label] ?? 0) > 1 ? $label.' · #'.$schedule->id : $label
            );
        });
    }
}
