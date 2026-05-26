<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\LivestockRequest;
use App\Models\Farm;
use App\Models\Livestock;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LivestockController extends Controller
{
    use ProvidesModuleNavigation;

    public function index(): View
    {
        $livestock = Livestock::query()->with('farm')->orderByDesc('created_at')->paginate(15);

        return view('modules.livestock.index', $this->moduleViewData('livestock', compact('livestock')));
    }

    public function create(): View
    {
        $farms = Farm::query()->orderBy('name')->get();

        return view('modules.livestock.create', $this->moduleViewData('livestock', compact('farms')));
    }

    public function store(LivestockRequest $request): RedirectResponse
    {
        Livestock::create($request->livestockAttributes());

        return redirect()->route('livestock.index')->with('success', 'Livestock group created successfully.');
    }

    public function edit(Livestock $livestock): View
    {
        $farms = Farm::query()->orderBy('name')->get();

        return view('modules.livestock.edit', $this->moduleViewData('livestock', compact('livestock', 'farms')));
    }

    public function update(LivestockRequest $request, Livestock $livestock): RedirectResponse
    {
        $livestock->update($request->livestockAttributes());

        return redirect()->route('livestock.index')->with('success', 'Livestock group updated successfully.');
    }

    public function destroy(Livestock $livestock): RedirectResponse
    {
        $livestock->delete();

        return redirect()->route('livestock.index')->with('success', 'Livestock group removed successfully.');
    }
}
