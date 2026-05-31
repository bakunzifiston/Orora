<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\LivestockRequest;
use App\Models\Animal;
use App\Models\Farm;
use App\Models\Livestock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LivestockController extends Controller
{
    use ProvidesModuleNavigation;

    public function index(Request $request): View
    {
        $livestock = Livestock::query()
            ->with('farm')
            ->withCount('animals')
            ->when($request->filled('farm_id'), fn ($q) => $q->where('farm_id', $request->integer('farm_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $farms = Farm::query()->orderBy('name')->get();

        $stats = [
            'total' => Livestock::query()->count(),
            'active' => Livestock::query()->where('status', 'active')->count(),
            'head_count' => (int) Livestock::query()->sum('head_count'),
            'animals' => Animal::query()->count(),
        ];

        return view('modules.livestock.index', $this->moduleViewData('livestock', compact('livestock', 'farms', 'stats')));
    }

    public function show(Livestock $livestock): View
    {
        $livestock->load('farm')->loadCount('animals');

        return view('modules.livestock.show', $this->moduleViewData('livestock', compact('livestock')));
    }

    public function create(): View
    {
        $farms = Farm::query()->orderBy('name')->get();

        return view('modules.livestock.create', $this->moduleViewData('livestock', compact('farms')));
    }

    public function store(LivestockRequest $request): RedirectResponse
    {
        $group = Livestock::create($request->livestockAttributes());

        return redirect()
            ->route('livestock.show', $group)
            ->with('success', 'Livestock group created successfully.');
    }

    public function edit(Livestock $livestock): View
    {
        $farms = Farm::query()->orderBy('name')->get();

        return view('modules.livestock.edit', $this->moduleViewData('livestock', compact('livestock', 'farms')));
    }

    public function update(LivestockRequest $request, Livestock $livestock): RedirectResponse
    {
        $livestock->update($request->livestockAttributes());

        return redirect()
            ->route('livestock.show', $livestock)
            ->with('success', 'Livestock group updated successfully.');
    }

    public function destroy(Livestock $livestock): RedirectResponse
    {
        $livestock->delete();

        return redirect()->route('livestock.index')->with('success', 'Livestock group removed successfully.');
    }
}
