<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExpenseSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExpenseModuleController extends Controller
{
    use ExpenseSectionViews;
    use ProvidesModuleNavigation;

    public function overview(): View
    {
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $byGroup = Expense::query()
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->where('expenses.status', 'paid')
            ->select('expense_categories.expense_group', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.expense_group')
            ->pluck('total', 'expense_group');

        $stats = [
            'month_total' => Expense::query()
                ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
                ->where('status', 'paid')
                ->sum('amount'),
            'feed' => $byGroup['feed'] ?? 0,
            'health' => $byGroup['health'] ?? 0,
            'farm_operations' => $byGroup['farm_operations'] ?? 0,
            'general' => $byGroup['general'] ?? 0,
            'record_count' => Expense::query()->whereBetween('expense_date', [$startOfMonth, $endOfMonth])->count(),
        ];

        $recentExpenses = Expense::query()
            ->with(['category', 'farm', 'vendor'])
            ->orderByDesc('expense_date')
            ->limit(10)
            ->get();

        $topCategories = Expense::query()
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->where('expenses.status', 'paid')
            ->select('expense_categories.name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return view('modules.expenses.overview', $this->expenseSectionData('overview', compact('stats', 'recentExpenses', 'topCategories')));
    }
}
