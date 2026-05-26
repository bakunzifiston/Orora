<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FeedingSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\FeedTypeRequest;
use App\Models\FeedSupplier;
use App\Models\FeedType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeedTypeController extends Controller
{
    use FeedingSectionViews;
    use ProvidesModuleNavigation;

    public function index(): View
    {
        $feedTypes = FeedType::query()->with('supplier')->withCount('inventories')->orderBy('name')->paginate(15);

        return view('modules.feeding.feed-types.index', $this->feedingSectionData('feed-types', compact('feedTypes')));
    }

    public function create(): View
    {
        return view('modules.feeding.feed-types.create', $this->feedingSectionData('feed-types', [
            'suppliers' => FeedSupplier::query()->where('is_active', true)->orderBy('name')->get(),
        ]));
    }

    public function store(FeedTypeRequest $request): RedirectResponse
    {
        FeedType::create($request->validated());

        return redirect()->route('feeding.feed-types')->with('success', 'Feed type saved successfully.');
    }

    public function edit(FeedType $feedType): View
    {
        return view('modules.feeding.feed-types.edit', $this->feedingSectionData('feed-types', [
            'feedType' => $feedType,
            'suppliers' => FeedSupplier::query()->orderBy('name')->get(),
        ]));
    }

    public function update(FeedTypeRequest $request, FeedType $feedType): RedirectResponse
    {
        $feedType->update($request->validated());

        return redirect()->route('feeding.feed-types')->with('success', 'Feed type updated successfully.');
    }

    public function destroy(FeedType $feedType): RedirectResponse
    {
        $feedType->delete();

        return redirect()->route('feeding.feed-types')->with('success', 'Feed type removed successfully.');
    }
}
