<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FeedingSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\FeedCalculatorRequest;
use App\Models\Animal;
use App\Models\Farm;
use App\Models\Livestock;
use App\Services\Feeding\FeedCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedCalculatorController extends Controller
{
    use FeedingSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(private readonly FeedCalculatorService $calculator) {}

    public function index(): View
    {
        return view('modules.feeding.calculator.index', $this->feedingSectionData('calculator', [
            'farms' => Farm::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ]));
    }

    public function calculate(FeedCalculatorRequest $request): View
    {
        if ($request->input('level') === 'individual') {
            $animal = Animal::query()
                ->with(['livestock.farm', 'farm'])
                ->findOrFail($request->input('animal_id'));
            $result = $this->calculator->calculateForAnimal($animal);
        } else {
            $livestock = Livestock::query()->with('farm')->findOrFail($request->input('livestock_id'));
            $result = $this->calculator->calculateForHerd($livestock);
        }

        return view('modules.feeding.calculator.result', $this->feedingSectionData('calculator', [
            'result' => $result,
            'farms' => Farm::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'input' => $request->only(['level', 'farm_id', 'livestock_id', 'animal_id']),
        ]));
    }

    public function getLivestock(Request $request): JsonResponse
    {
        $request->validate(['farm_id' => ['required', 'exists:farms,id']]);

        $livestock = Livestock::query()
            ->where('farm_id', $request->input('farm_id'))
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (Livestock $group) => [
                'id' => $group->id,
                'label' => collect([
                    $group->livestock_types_label ?: 'Herd',
                    $group->breed,
                    $group->name,
                ])->filter()->implode(' · ').' ('.($group->head_count ?? 0).' head)',
            ]);

        return response()->json($livestock);
    }

    public function getAnimals(Request $request): JsonResponse
    {
        $request->validate(['livestock_id' => ['required', 'exists:livestock,id']]);

        $animals = Animal::query()
            ->where('livestock_id', $request->input('livestock_id'))
            ->where('lifecycle_status', 'Active')
            ->orderBy('tag_number')
            ->get()
            ->map(fn (Animal $animal) => [
                'id' => $animal->id,
                'label' => collect([
                    $animal->tag_number,
                    $animal->name,
                    $animal->weight_kg ? number_format((float) $animal->weight_kg, 1).' kg' : '? kg',
                    $animal->production_status ?: 'No status',
                ])->filter()->implode(' · '),
            ]);

        return response()->json($animals);
    }
}
