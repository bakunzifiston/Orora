<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Controllers\Concerns\SalesSectionViews;
use App\Models\SaleTransaction;
use App\Services\DashboardAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesModuleController extends Controller
{
    use ProvidesModuleNavigation;
    use SalesSectionViews;

    public function __construct(
        private readonly DashboardAnalyticsService $analytics,
    ) {}

    public function overview(Request $request): View
    {
        $filters = $this->analytics->resolveFilters($request);
        $from = $filters['from'];
        $to = $filters['to'];

        $periodQuery = SaleTransaction::query()->whereBetween('sale_date', [$from, $to]);

        $byType = (clone $periodQuery)
            ->where('sale_status', 'completed')
            ->select('sale_type', DB::raw('SUM(total_amount) as total'))
            ->groupBy('sale_type')
            ->pluck('total', 'sale_type');

        $stats = [
            'period_total' => (clone $periodQuery)
                ->where('sale_status', 'completed')
                ->sum('total_amount'),
            'transaction_count' => (clone $periodQuery)->count(),
            'unpaid_balance' => SaleTransaction::query()
                ->whereIn('payment_status', ['unpaid', 'partial'])
                ->whereNotIn('sale_status', ['cancelled'])
                ->withSum('payments as paid_total', 'amount_paid')
                ->get()
                ->sum(fn (SaleTransaction $t) => max(0, (float) $t->total_amount - (float) ($t->paid_total ?? 0))),
            'animal' => $byType['animal_sale'] ?? 0,
            'meat' => $byType['meat_sale'] ?? 0,
            'milk' => $byType['milk_sale'] ?? 0,
        ];

        $recent = SaleTransaction::query()
            ->with(['farm', 'customer'])
            ->orderByDesc('sale_date')
            ->limit(10)
            ->get();

        return view('modules.sales.overview', $this->salesSectionData('overview', compact('stats', 'recent', 'filters')));
    }
}
