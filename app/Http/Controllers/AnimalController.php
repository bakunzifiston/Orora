<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\AnimalRequest;
use App\Models\Animal;
use App\Models\Farm;
use App\Models\Livestock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnimalController extends Controller
{
    use ProvidesModuleNavigation;

    public function index(Request $request): View
    {
        $animals = Animal::query()
            ->with(['farm', 'livestock'])
            ->when($request->filled('farm_id'), fn ($q) => $q->where('farm_id', $request->integer('farm_id')))
            ->when($request->filled('livestock_id'), fn ($q) => $q->where('livestock_id', $request->integer('livestock_id')))
            ->when($request->filled('gender'), fn ($q) => $q->where('gender', $request->string('gender')))
            ->when($request->filled('lifecycle_status'), fn ($q) => $q->where('lifecycle_status', $request->string('lifecycle_status')))
            ->when($request->filled('health_status'), fn ($q) => $q->where('health_status', $request->string('health_status')))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $farms = Farm::query()->orderBy('name')->get();
        $livestockGroups = Livestock::query()->with('farm')->orderBy('name')->get();

        $stats = [
            'total' => Animal::query()->count(),
            'active' => Animal::query()->where('lifecycle_status', 'Active')->count(),
            'female' => Animal::query()->where('gender', 'female')->count(),
            'lactating' => Animal::query()->where('production_status', 'Lactating')->count(),
        ];

        return view('modules.animals.index', $this->moduleViewData('animals', compact(
            'animals',
            'farms',
            'livestockGroups',
            'stats',
        )));
    }

    public function show(Animal $animal): View
    {
        $animal->load(['farm', 'livestock']);

        return view('modules.animals.show', $this->moduleViewData('animals', compact('animal')));
    }

    public function create(): View
    {
        return view('modules.animals.create', $this->moduleViewData('animals', $this->formOptions()));
    }

    public function store(AnimalRequest $request): RedirectResponse
    {
        $animal = Animal::create($request->animalAttributes());
        $this->storePhoto($request, $animal);

        return redirect()
            ->route('animals.show', $animal)
            ->with('success', 'Animal registered successfully.');
    }

    public function edit(Animal $animal): View
    {
        return view('modules.animals.edit', $this->moduleViewData('animals', array_merge($this->formOptions(), compact('animal'))));
    }

    public function update(AnimalRequest $request, Animal $animal): RedirectResponse
    {
        $animal->update($request->animalAttributes());
        $this->storePhoto($request, $animal);

        return redirect()
            ->route('animals.show', $animal)
            ->with('success', 'Animal updated successfully.');
    }

    public function destroy(Animal $animal): RedirectResponse
    {
        if ($animal->photo_path) {
            Storage::disk('public')->delete($animal->photo_path);
        }

        $animal->delete();

        return redirect()->route('animals.index')->with('success', 'Animal removed successfully.');
    }

    private function formOptions(): array
    {
        return [
            'farms' => Farm::query()->orderBy('name')->get(),
            'livestockGroups' => Livestock::query()->with('farm')->orderBy('name')->get(),
        ];
    }

    private function storePhoto(AnimalRequest $request, Animal $animal): void
    {
        if (! $request->hasFile('photo')) {
            return;
        }

        if ($animal->photo_path) {
            Storage::disk('public')->delete($animal->photo_path);
        }

        $path = $request->file('photo')->store('animals/'.$animal->id, 'public');
        $animal->update(['photo_path' => $path]);
    }
}
