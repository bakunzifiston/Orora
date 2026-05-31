<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\MilkSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\MilkStorageRequest;
use App\Models\Farm;
use App\Models\MilkStorage;
use App\Services\MilkStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MilkStorageController extends Controller
{
    use MilkSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(
        private readonly MilkStorageService $storageService,
    ) {}

    public function index(): View
    {
        $storageUnits = MilkStorage::query()
            ->with('farm')
            ->orderBy('farm_id')
            ->orderBy('container_name')
            ->paginate(15);

        return view('modules.milk.storage.index', $this->milkSectionData('storage', compact('storageUnits')));
    }

    public function create(): View
    {
        return view('modules.milk.storage.create', $this->milkSectionData('storage', [
            'farms' => Farm::query()->orderBy('name')->get(),
        ]));
    }

    public function store(MilkStorageRequest $request): RedirectResponse
    {
        $this->storageService->create($request->validated());

        return redirect()->route('milk.storage')->with('success', 'Storage container added.');
    }

    public function edit(MilkStorage $milkStorage): View
    {
        $milkStorage->load(['farm', 'movements' => fn ($q) => $q->limit(20)]);

        return view('modules.milk.storage.edit', $this->milkSectionData('storage', [
            'milkStorage' => $milkStorage,
            'farms' => Farm::query()->orderBy('name')->get(),
            'movementLabels' => config('modules.milk_storage_movement_labels'),
        ]));
    }

    public function update(MilkStorageRequest $request, MilkStorage $milkStorage): RedirectResponse
    {
        $milkStorage->update($request->validated());
        $this->storageService->refreshStatus($milkStorage);

        return redirect()->route('milk.storage')->with('success', 'Storage updated.');
    }

    public function destroy(MilkStorage $milkStorage): RedirectResponse
    {
        if ((float) $milkStorage->current_quantity_liters > 0) {
            return back()->withErrors(['storage' => 'Empty the container before removing it.']);
        }

        $milkStorage->delete();

        return redirect()->route('milk.storage')->with('success', 'Storage container removed.');
    }
}
