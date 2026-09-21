<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\FarmRequest;
use App\Models\Farm;
use App\Models\Livestock;
use App\Services\RwandaLocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FarmController extends Controller
{
    use ProvidesModuleNavigation;

    public function __construct(private readonly RwandaLocationService $locations) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->string('status')->toString();
        $species = $request->string('species')->toString();
        $district = $request->string('district')->toString();

        $statuses = config('modules.farm_statuses', []);
        $speciesOptions = config('modules.species', []);

        if ($status !== '' && ! in_array($status, $statuses, true)) {
            $status = '';
        }

        if ($species !== '' && ! in_array($species, $speciesOptions, true)) {
            $species = '';
        }

        $farms = Farm::query()
            ->withCount(['livestock', 'animals', 'flocks'])
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';

                $query->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('registration_number', 'like', $like)
                        ->orWhere('owner_first_name', 'like', $like)
                        ->orWhere('owner_last_name', 'like', $like)
                        ->orWhere('contact_phone', 'like', $like)
                        ->orWhere('district', 'like', $like)
                        ->orWhere('province', 'like', $like)
                        ->orWhere('sector', 'like', $like)
                        ->orWhere('village', 'like', $like);
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($species !== '', fn ($query) => $query->where('primary_species', $species))
            ->when($district !== '', fn ($query) => $query->where('district', $district))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $districts = Farm::query()
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->distinct()
            ->orderBy('district')
            ->pluck('district');

        $filtersActive = $search !== '' || $status !== '' || $species !== '' || $district !== '';

        $stats = [
            'total' => Farm::query()->count(),
            'active' => Farm::query()->where('status', 'active')->count(),
            'total_hectares' => (float) Farm::query()->sum('farm_size_hectares'),
            'livestock_groups' => Livestock::query()->count(),
        ];

        return view('modules.farms.index', $this->moduleViewData('farms', compact(
            'farms',
            'stats',
            'districts',
            'search',
            'status',
            'species',
            'district',
            'filtersActive',
        )));
    }

    public function show(Farm $farm): View
    {
        $farm->load('members')->loadCount(['livestock', 'animals', 'flocks']);

        if ($farm->isPoultry()) {
            $farm->load('flocks');
        }

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
