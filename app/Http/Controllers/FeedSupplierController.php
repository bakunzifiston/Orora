<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FeedingSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\FeedSupplierRequest;
use App\Models\FeedSupplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeedSupplierController extends Controller
{
    use FeedingSectionViews;
    use ProvidesModuleNavigation;

    public function index(): View
    {
        $suppliers = FeedSupplier::query()->withCount('feedTypes')->orderBy('name')->paginate(15);

        return view('modules.feeding.suppliers.index', $this->feedingSectionData('suppliers', compact('suppliers')));
    }

    public function create(): View
    {
        return view('modules.feeding.suppliers.create', $this->feedingSectionData('suppliers'));
    }

    public function store(FeedSupplierRequest $request): RedirectResponse
    {
        FeedSupplier::create($request->validated());

        return redirect()->route('feeding.suppliers')->with('success', 'Supplier saved successfully.');
    }

    public function edit(FeedSupplier $supplier): View
    {
        return view('modules.feeding.suppliers.edit', $this->feedingSectionData('suppliers', compact('supplier')));
    }

    public function update(FeedSupplierRequest $request, FeedSupplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()->route('feeding.suppliers')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(FeedSupplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()->route('feeding.suppliers')->with('success', 'Supplier removed successfully.');
    }
}
