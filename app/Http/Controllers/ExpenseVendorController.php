<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExpenseSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\ExpenseVendorRequest;
use App\Models\ExpenseVendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExpenseVendorController extends Controller
{
    use ExpenseSectionViews;
    use ProvidesModuleNavigation;

    public function index(): View
    {
        $vendors = ExpenseVendor::query()->withCount('expenses')->orderBy('name')->paginate(15);

        return view('modules.expenses.vendors.index', $this->expenseSectionData('vendors', compact('vendors')));
    }

    public function create(): View
    {
        return view('modules.expenses.vendors.create', $this->expenseSectionData('vendors'));
    }

    public function store(ExpenseVendorRequest $request): RedirectResponse
    {
        ExpenseVendor::create($request->validated());

        return redirect()->route('expenses.vendors')->with('success', 'Vendor saved successfully.');
    }

    public function edit(ExpenseVendor $vendor): View
    {
        return view('modules.expenses.vendors.edit', $this->expenseSectionData('vendors', compact('vendor')));
    }

    public function update(ExpenseVendorRequest $request, ExpenseVendor $vendor): RedirectResponse
    {
        $vendor->update($request->validated());

        return redirect()->route('expenses.vendors')->with('success', 'Vendor updated successfully.');
    }

    public function destroy(ExpenseVendor $vendor): RedirectResponse
    {
        $vendor->delete();

        return redirect()->route('expenses.vendors')->with('success', 'Vendor removed successfully.');
    }
}
