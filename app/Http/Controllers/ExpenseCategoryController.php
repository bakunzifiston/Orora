<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExpenseSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\ExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    use ExpenseSectionViews;
    use ProvidesModuleNavigation;

    public function index(): View
    {
        $categories = ExpenseCategory::query()
            ->withCount('expenses')
            ->orderBy('expense_group')
            ->orderBy('name')
            ->get()
            ->groupBy('expense_group');

        return view('modules.expenses.categories.index', $this->expenseSectionData('categories', compact('categories')));
    }

    public function create(): View
    {
        return view('modules.expenses.categories.create', $this->expenseSectionData('categories'));
    }

    public function store(ExpenseCategoryRequest $request): RedirectResponse
    {
        ExpenseCategory::create([
            ...$request->validated(),
            'is_system' => false,
        ]);

        return redirect()->route('expenses.categories')->with('success', 'Category created successfully.');
    }

    public function edit(ExpenseCategory $category): View
    {
        return view('modules.expenses.categories.edit', $this->expenseSectionData('categories', compact('category')));
    }

    public function update(ExpenseCategoryRequest $request, ExpenseCategory $category): RedirectResponse
    {
        if ($category->is_system) {
            $category->update($request->only(['description', 'is_active']));
        } else {
            $category->update($request->validated());
        }

        return redirect()->route('expenses.categories')->with('success', 'Category updated successfully.');
    }

    public function destroy(ExpenseCategory $category): RedirectResponse
    {
        if ($category->is_system) {
            return redirect()->route('expenses.categories')->with('error', 'System categories cannot be deleted.');
        }

        if ($category->expenses()->exists()) {
            return redirect()->route('expenses.categories')->with('error', 'Category has expenses and cannot be deleted.');
        }

        $category->delete();

        return redirect()->route('expenses.categories')->with('success', 'Category removed successfully.');
    }
}
