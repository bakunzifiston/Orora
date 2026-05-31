<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FinanceSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Models\Farm;
use App\Models\FinanceTransaction;
use App\Models\Livestock;
use App\Services\Finance\FinanceReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceModuleController extends Controller
{
    use FinanceSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(private readonly FinanceReportService $reports) {}

    public function overview(Request $request): View
    {
        $filters = $this->financeFilters($request);
        $stats = $this->reports->overviewStats(
            $filters['filterFrom'],
            $filters['filterTo'],
            $filters['filterFarmId'] ? (int) $filters['filterFarmId'] : null,
            $filters['filterLivestockId'] ? (int) $filters['filterLivestockId'] : null,
        );

        $recent = FinanceTransaction::query()
            ->with(['farm', 'period'])
            ->when($filters['filterFarmId'], fn ($q) => $q->where('farm_id', $filters['filterFarmId']))
            ->orderByDesc('transaction_date')
            ->limit(10)
            ->get();

        return view('modules.finance.overview', $this->financeSectionData('overview', array_merge($filters, [
            'stats' => $stats,
            'recent' => $recent,
            'farms' => Farm::query()->orderBy('name')->get(),
            'livestock' => Livestock::query()->when($filters['filterFarmId'], fn ($q) => $q->where('farm_id', $filters['filterFarmId']))->orderBy('name')->get(),
        ])));
    }

    public function transactions(Request $request): View
    {
        $filters = $this->financeFilters($request);

        $transactions = FinanceTransaction::query()
            ->with(['farm', 'livestock', 'lines.account'])
            ->whereBetween('transaction_date', [$filters['filterFrom'], $filters['filterTo']])
            ->when($filters['filterFarmId'], fn ($q) => $q->where('farm_id', $filters['filterFarmId']))
            ->when($filters['filterLivestockId'], fn ($q) => $q->where('livestock_id', $filters['filterLivestockId']))
            ->orderByDesc('transaction_date')
            ->paginate(20)
            ->withQueryString();

        return view('modules.finance.transactions.index', $this->financeSectionData('transactions', array_merge($filters, [
            'transactions' => $transactions,
            'farms' => Farm::query()->orderBy('name')->get(),
            'livestock' => Livestock::query()->when($filters['filterFarmId'], fn ($q) => $q->where('farm_id', $filters['filterFarmId']))->orderBy('name')->get(),
        ])));
    }

    public function profitLoss(Request $request): View
    {
        $filters = $this->financeFilters($request);
        $report = $this->reports->profitAndLoss(
            $filters['filterFrom'],
            $filters['filterTo'],
            $filters['filterFarmId'] ? (int) $filters['filterFarmId'] : null,
            $filters['filterLivestockId'] ? (int) $filters['filterLivestockId'] : null,
        );

        return view('modules.finance.reports.profit_loss', $this->financeSectionData('profit_loss', array_merge($filters, [
            'report' => $report,
            'farms' => Farm::query()->orderBy('name')->get(),
            'livestock' => Livestock::query()->when($filters['filterFarmId'], fn ($q) => $q->where('farm_id', $filters['filterFarmId']))->orderBy('name')->get(),
        ])));
    }

    public function cashFlow(Request $request): View
    {
        $filters = $this->financeFilters($request);
        $report = $this->reports->cashFlow(
            $filters['filterFrom'],
            $filters['filterTo'],
            $filters['filterFarmId'] ? (int) $filters['filterFarmId'] : null,
            $filters['filterLivestockId'] ? (int) $filters['filterLivestockId'] : null,
        );

        return view('modules.finance.reports.cash_flow', $this->financeSectionData('cash_flow', array_merge($filters, [
            'report' => $report,
            'farms' => Farm::query()->orderBy('name')->get(),
            'livestock' => Livestock::query()->when($filters['filterFarmId'], fn ($q) => $q->where('farm_id', $filters['filterFarmId']))->orderBy('name')->get(),
        ])));
    }
}
