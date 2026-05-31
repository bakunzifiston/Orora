<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExpenseSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\ExpenseRequest;
use App\Models\Animal;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseVendor;
use App\Models\Farm;
use App\Models\Livestock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    use ExpenseSectionViews;
    use ProvidesModuleNavigation;

    public function index(Request $request): View
    {
        $query = Expense::query()
            ->with(['category', 'farm', 'vendor', 'animal'])
            ->orderByDesc('expense_date');

        if ($request->filled('group')) {
            $query->whereHas('category', fn ($q) => $q->where('expense_group', $request->input('group')));
        }

        if ($request->filled('farm_id')) {
            $query->where('farm_id', $request->input('farm_id'));
        }

        $expenses = $query->paginate(15)->withQueryString();

        return view('modules.expenses.records.index', $this->expenseSectionData('records', [
            'expenses' => $expenses,
            'farms' => Farm::query()->orderBy('name')->get(),
            'filterGroup' => $request->input('group'),
            'filterFarmId' => $request->input('farm_id'),
        ]));
    }

    public function create(Request $request): View
    {
        return view('modules.expenses.records.create', $this->expenseSectionData('records', $this->formOptions($request)));
    }

    public function store(ExpenseRequest $request): RedirectResponse
    {
        $expense = Expense::create($this->expenseAttributes($request));

        $this->storeAttachment($request, $expense);

        return redirect()->route('expenses.records')->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense): View
    {
        if ($expense->source_type) {
            return redirect()
                ->route('expenses.records')
                ->with('error', 'This expense is linked to another module. Edit it from the source record.');
        }

        return view('modules.expenses.records.edit', $this->expenseSectionData('records', array_merge(
            $this->formOptions(request()),
            compact('expense'),
        )));
    }

    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        if ($expense->source_type) {
            return redirect()->route('expenses.records')->with('error', 'Linked expenses must be edited at the source.');
        }

        $expense->update($this->expenseAttributes($request));
        $this->storeAttachment($request, $expense);

        return redirect()->route('expenses.records')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        if ($expense->source_type) {
            return redirect()->route('expenses.records')->with('error', 'Delete the source record instead, or unlink first.');
        }

        if ($expense->attachment_path) {
            Storage::disk('public')->delete($expense->attachment_path);
        }

        $expense->delete();

        return redirect()->route('expenses.records')->with('success', 'Expense removed successfully.');
    }

    private function expenseAttributes(ExpenseRequest $request): array
    {
        return [
            'expense_category_id' => $request->input('expense_category_id'),
            'farm_id' => $request->input('farm_id'),
            'animal_id' => $request->input('animal_id'),
            'livestock_id' => $request->input('livestock_id'),
            'expense_vendor_id' => $request->input('expense_vendor_id'),
            'expense_date' => $request->input('expense_date'),
            'amount' => $request->input('amount'),
            'currency' => $request->input('currency'),
            'payment_method' => $request->input('payment_method'),
            'paid_by' => $request->input('paid_by'),
            'title' => $request->input('title'),
            'notes' => $request->input('notes'),
            'status' => $request->input('status'),
        ];
    }

    private function storeAttachment(ExpenseRequest $request, Expense $expense): void
    {
        if (! $request->hasFile('attachment')) {
            return;
        }

        if ($expense->attachment_path) {
            Storage::disk('public')->delete($expense->attachment_path);
        }

        $path = $request->file('attachment')->store('expenses/'.$expense->id, 'public');
        $expense->update(['attachment_path' => $path]);
    }

    private function formOptions(Request $request): array
    {
        $categories = ExpenseCategory::query()->where('is_active', true)->orderBy('expense_group')->orderBy('name')->get();

        return [
            'farms' => Farm::query()->orderBy('name')->get(),
            'categories' => $categories,
            'categoriesByGroup' => $categories->groupBy('expense_group'),
            'vendors' => ExpenseVendor::query()->where('is_active', true)->orderBy('name')->get(),
            'animals' => Animal::query()->orderBy('tag_number')->get(),
            'livestockGroups' => Livestock::query()->orderBy('name')->get(),
            'preselectedGroup' => $request->input('group'),
            'preselectedCategoryId' => $request->input('category'),
        ];
    }
}
