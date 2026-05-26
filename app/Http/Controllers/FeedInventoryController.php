<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FeedingSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\FeedInventoryMovementRequest;
use App\Http\Requests\FeedInventoryRequest;
use App\Models\Farm;
use App\Models\FeedInventory;
use App\Models\FeedType;
use App\Services\FeedInventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeedInventoryController extends Controller
{
    use FeedingSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(private FeedInventoryService $inventoryService) {}

    public function index(): View
    {
        $inventories = FeedInventory::query()
            ->with(['farm', 'feedType.supplier'])
            ->orderBy('farm_id')
            ->orderBy('feed_type_id')
            ->paginate(15);

        return view('modules.feeding.inventory.index', $this->feedingSectionData('inventory', compact('inventories')));
    }

    public function create(): View
    {
        return view('modules.feeding.inventory.create', $this->feedingSectionData('inventory', [
            'farms' => Farm::query()->orderBy('name')->get(),
            'feedTypes' => FeedType::query()->where('is_active', true)->orderBy('name')->get(),
        ]));
    }

    public function store(FeedInventoryRequest $request): RedirectResponse
    {
        $feedType = FeedType::query()->findOrFail($request->input('feed_type_id'));

        FeedInventory::create([
            ...$request->validated(),
            'unit' => $feedType->unit,
            'quantity_on_hand' => 0,
        ]);

        return redirect()->route('feeding.inventory')->with('success', 'Inventory item created successfully.');
    }

    public function edit(FeedInventory $feedInventory): View
    {
        $feedInventory->load(['farm', 'feedType.supplier', 'movements' => fn ($q) => $q->limit(20)]);

        return view('modules.feeding.inventory.edit', $this->feedingSectionData('inventory', [
            'feedInventory' => $feedInventory,
            'farms' => Farm::query()->orderBy('name')->get(),
            'feedTypes' => FeedType::query()->orderBy('name')->get(),
            'movementLabels' => config('modules.feed_movement_labels'),
        ]));
    }

    public function update(FeedInventoryRequest $request, FeedInventory $feedInventory): RedirectResponse
    {
        $feedType = FeedType::query()->findOrFail($request->input('feed_type_id'));

        $feedInventory->update([
            ...$request->validated(),
            'unit' => $feedType->unit,
        ]);

        return redirect()->route('feeding.inventory')->with('success', 'Inventory updated successfully.');
    }

    public function destroy(FeedInventory $feedInventory): RedirectResponse
    {
        $feedInventory->delete();

        return redirect()->route('feeding.inventory')->with('success', 'Inventory item removed successfully.');
    }

    public function storeMovement(FeedInventoryMovementRequest $request, FeedInventory $feedInventory): RedirectResponse
    {
        try {
            $this->inventoryService->recordMovement(
                $feedInventory,
                $request->input('movement_type'),
                (float) $request->input('quantity'),
                $request->input('notes'),
                null,
                $request->date('moved_at') ?? now(),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['quantity' => $e->getMessage()]);
        }

        return redirect()
            ->route('feeding.inventory.edit', $feedInventory)
            ->with('success', 'Stock movement recorded successfully.');
    }
}
