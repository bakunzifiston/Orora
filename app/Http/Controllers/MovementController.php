<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Models\Animal;
use App\Models\Farm;
use App\Models\Movement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovementController extends Controller
{
    use ProvidesModuleNavigation;

    public function index(): View
    {
        $movements = Movement::query()
            ->with(['animal', 'fromFarm', 'toFarm'])
            ->orderByDesc('moved_on')
            ->paginate(15);

        return view('modules.movements.index', $this->moduleViewData('movement', compact('movements')));
    }

    public function create(): View
    {
        return view('modules.movements.create', $this->moduleViewData('movement', $this->formOptions()));
    }

    public function store(Request $request): RedirectResponse
    {
        Movement::create($request->validate($this->rules()));

        return redirect()->route('movements.index')->with('success', 'Movement recorded successfully.');
    }

    public function edit(Movement $movement): View
    {
        return view('modules.movements.edit', $this->moduleViewData('movement', array_merge($this->formOptions(), compact('movement'))));
    }

    public function update(Request $request, Movement $movement): RedirectResponse
    {
        $movement->update($request->validate($this->rules()));

        return redirect()->route('movements.index')->with('success', 'Movement updated successfully.');
    }

    public function destroy(Movement $movement): RedirectResponse
    {
        $movement->delete();

        return redirect()->route('movements.index')->with('success', 'Movement removed successfully.');
    }

    private function formOptions(): array
    {
        return [
            'animals' => Animal::query()->with('farm')->orderBy('tag_number')->get(),
            'farms' => Farm::query()->orderBy('name')->get(),
        ];
    }

    private function rules(): array
    {
        return [
            'animal_id' => ['required', 'exists:animals,id'],
            'from_farm_id' => ['required', 'exists:farms,id'],
            'to_farm_id' => ['nullable', 'exists:farms,id', 'different:from_farm_id'],
            'movement_type' => ['required', 'in:'.implode(',', config('modules.movement_types'))],
            'moved_on' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
