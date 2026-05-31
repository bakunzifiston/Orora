<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\FarmRequest;
use App\Models\Farm;
use App\Models\Livestock;
use App\Services\RwandaLocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FarmController extends Controller
{
    use ProvidesModuleNavigation;

    public function __construct(private readonly RwandaLocationService $locations) {}

    public function index(): View
    {
        $farms = Farm::query()
            ->withCount(['livestock', 'animals'])
            ->orderBy('name')
            ->paginate(12);

        $stats = [
            'total' => Farm::query()->count(),
            'active' => Farm::query()->where('status', 'active')->count(),
            'total_hectares' => (float) Farm::query()->sum('farm_size_hectares'),
            'livestock_groups' => Livestock::query()->count(),
        ];

        return view('modules.farms.index', $this->moduleViewData('farms', compact('farms', 'stats')));
    }

    public function show(Farm $farm): View
    {
        $farm->load('members')->loadCount(['livestock', 'animals']);

        return view('modules.farms.show', $this->moduleViewData('farms', compact('farm')));
    }

    public function create(): View
    {
        return view('modules.farms.create', $this->formData());
    }

    public function store(FarmRequest $request): RedirectResponse
    {
        $farm = Farm::create($request->farmAttributes());
        $this->syncMembers($farm, $request->memberRows());

        return redirect()->route('farms.index')->with('success', 'Farm registered successfully.');
    }

    public function edit(Farm $farm): View
    {
        $farm->load('members');

        return view('modules.farms.edit', $this->formData(compact('farm')));
    }

    public function update(FarmRequest $request, Farm $farm): RedirectResponse
    {
        $farm->update($request->farmAttributes());
        $this->syncMembers($farm, $request->memberRows());

        return redirect()->route('farms.index')->with('success', 'Farm updated successfully.');
    }

    public function destroy(Farm $farm): RedirectResponse
    {
        $farm->delete();

        return redirect()->route('farms.index')->with('success', 'Farm removed successfully.');
    }

    private function formData(array $extra = []): array
    {
        return $this->moduleViewData('farms', array_merge([
            'provinces' => $this->locations->provinces(),
        ], $extra));
    }

    private function syncMembers(Farm $farm, array $members): void
    {
        $farm->members()->delete();

        if ($farm->requiresMembers() && $members !== []) {
            $farm->members()->createMany($members);
        }
    }
}
