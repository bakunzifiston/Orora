<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\BirthRecord;
use App\Models\BreedingRecord;
use App\Models\Certificate;
use App\Models\Customer;
use App\Models\EmployeeDocument;
use App\Models\Expense;
use App\Models\Farm;
use App\Models\FeedInventory;
use App\Models\FinanceTransaction;
use App\Models\HealthRecord;
use App\Models\MilkRecord;
use App\Models\MilkSession;
use App\Models\Movement;
use App\Models\SaleItem;
use App\Models\SaleTransaction;
use App\Models\Vaccination;
use App\Services\Finance\FinanceReportService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class DashboardAnalyticsService
{
    private const ACTIVITY_LIMIT = 15;

    private const TREND_MONTHS = 6;

    private const CHART_COLORS = ['#A4D400', '#002B2B', '#4ade80', '#60a5fa', '#fb923c', '#fbbf24'];

    public function __construct(
        private readonly FinanceReportService $financeReports,
        private readonly BreedingReminderService $breedingReminders,
    ) {}

    /**
     * @param  array{period: string, farm_id: ?int, from: string, to: string}  $filters
     * @return array<string, mixed>
     */
    public function build(array $filters): array
    {
        $from = $filters['from'];
        $to = $filters['to'];
        $farmId = $filters['farm_id'];
        $alerts = $this->alerts($farmId);

        $critical = collect($alerts)->where('severity', 'critical')->count();
        $warning = collect($alerts)->where('severity', 'warning')->count();
        $info = collect($alerts)->where('severity', 'info')->count();

        $financeStats = $this->financeReports->overviewStats($from, $to, $farmId);

        return [
            'filters' => $filters,
            'alertStrip' => [
                'total' => count($alerts),
                'critical' => $critical,
                'warning' => $warning,
                'info' => $info,
            ],
            'financial' => $this->financialSummary($from, $to, $farmId, $financeStats),
            'livestock' => $this->livestockSummary($farmId),
            'charts' => [
                'revenueExpenses' => $this->revenueExpenseTrends($farmId),
                'milkTrend' => $this->milkProductionTrend($farmId),
                'salesByType' => $this->salesByType($from, $to, $farmId),
                'expenseBreakdown' => $this->expenseBreakdown($from, $to, $farmId),
                'animalHealth' => $this->animalHealthChart($farmId),
            ],
            'moduleStrips' => $this->moduleStrips($from, $to, $farmId),
            'recentSales' => $this->recentSales($from, $to, $farmId, 5),
            'pendingAlerts' => $this->pendingAlertsByModule($alerts),
            'topAnimals' => $this->topMilkProducers($from, $to, $farmId, 5),
            'topCustomers' => $this->topCustomers($from, $to, $farmId, 5),
            'activity' => $this->recentActivity($farmId),
        ];
    }

    /**
     * @return array{period: string, farm_id: ?int, from: string, to: string, label: string}
     */
    public function resolveFilters(Request $request): array
    {
        $period = $request->input('period', 'this_month');
        $farmId = $request->filled('farm_id') ? (int) $request->input('farm_id') : null;

        [$from, $to, $label] = match ($period) {
            'last_month' => [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
                now()->subMonth()->format('F Y'),
            ],
            'this_quarter' => [
                now()->startOfQuarter(),
                now()->endOfQuarter(),
                'Q'.now()->quarter.' '.now()->year,
            ],
            'this_year' => [
                now()->startOfYear(),
                now()->endOfYear(),
                (string) now()->year,
            ],
            'custom' => [
                Carbon::parse($request->input('from', now()->startOfMonth()->toDateString())),
                Carbon::parse($request->input('to', now()->endOfMonth()->toDateString())),
                'Custom range',
            ],
            default => [
                now()->startOfMonth(),
                now()->endOfMonth(),
                now()->format('F Y'),
            ],
        };

        if ($period === 'custom') {
            $label = $from->format('M j').' – '.$to->format('M j, Y');
        }

        return [
            'period' => $period,
            'farm_id' => $farmId,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'label' => $label,
        ];
    }

    /**
     * @param  array<string, mixed>  $financeStats
     * @return array<string, mixed>
     */
    private function financialSummary(string $from, string $to, ?int $farmId, array $financeStats): array
    {
        $revenue = (float) ($financeStats['revenue'] ?? 0);
        $expenses = (float) ($financeStats['expenses'] ?? 0);

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'net_profit' => (float) ($financeStats['net_income'] ?? 0),
            'accounts_receivable' => (float) ($financeStats['accounts_receivable'] ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function livestockSummary(?int $farmId): array
    {
        $animalQuery = $this->farmScope(Animal::query(), $farmId);

        return [
            'total_animals' => (clone $animalQuery)->count(),
            'active_farms' => $farmId
                ? (Farm::query()->where('id', $farmId)->where('status', 'active')->exists() ? 1 : 0)
                : Farm::query()->where('status', 'active')->count(),
            'lactating' => (clone $animalQuery)->milkingEligible()
                ->where('lifecycle_status', 'Active')
                ->count(),
            'for_sale' => $this->animalsForSaleCount($farmId),
        ];
    }

    private function animalsForSaleCount(?int $farmId): int
    {
        $query = SaleItem::query()
            ->whereNotNull('animal_id')
            ->whereHas('transaction', function (Builder $q) use ($farmId) {
                $q->where('sale_type', 'animal_sale')
                    ->whereNotIn('sale_status', ['cancelled', 'completed']);
                $this->farmScope($q, $farmId);
            });

        return $query->distinct('animal_id')->count('animal_id');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function moduleStrips(string $from, string $to, ?int $farmId): array
    {
        $vaccDue = Vaccination::query()
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '<=', now()->addDays(30)->toDateString())
            ->count();

        $lowStock = $this->lowFeedStockCount($farmId);

        $pregnant = BreedingRecord::query()
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->where('breeding_status', 'confirmed_pregnant')
            ->count();

        $upcomingCalvings = BreedingRecord::query()
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->where('breeding_status', 'confirmed_pregnant')
            ->whereBetween('expected_calving_date', [now()->toDateString(), now()->addDays(60)->toDateString()])
            ->count();

        return [
            [
                'key' => 'health',
                'label' => 'Health',
                'route' => 'health.overview',
                'icon' => 'health',
                'metrics' => [
                    ['label' => 'Vaccinations due', 'value' => number_format($vaccDue)],
                    ['label' => 'Follow-ups (30d)', 'value' => number_format($this->healthFollowupsCount($farmId))],
                ],
            ],
            [
                'key' => 'feeding',
                'label' => 'Feeding',
                'route' => 'feeding.overview',
                'icon' => 'feeding',
                'metrics' => [
                    ['label' => 'Low stock', 'value' => number_format($lowStock)],
                    ['label' => 'Feed types active', 'value' => number_format(
                        FeedInventory::query()
                            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
                            ->count()
                    )],
                ],
            ],
            [
                'key' => 'breeding',
                'label' => 'Breeding',
                'route' => 'breeding.overview',
                'icon' => 'breeding',
                'metrics' => [
                    ['label' => 'Pregnant', 'value' => number_format($pregnant)],
                    ['label' => 'Calvings (60d)', 'value' => number_format($upcomingCalvings)],
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentSales(string $from, string $to, ?int $farmId, int $limit): array
    {
        return $this->farmScope(SaleTransaction::query(), $farmId)
            ->with(['customer', 'farm'])
            ->whereBetween('sale_date', [$from, $to])
            ->whereNotIn('sale_status', ['cancelled'])
            ->orderByDesc('sale_date')
            ->limit($limit)
            ->get()
            ->map(fn (SaleTransaction $sale) => [
                'number' => $sale->sale_number,
                'type' => $sale->typeLabel(),
                'customer' => $sale->customer?->display_name ?? 'Walk-in',
                'farm' => $sale->farm?->name ?? '—',
                'amount' => (float) $sale->total_amount,
                'currency' => $sale->currency,
                'status' => $sale->statusLabel(),
                'sale_status' => $sale->sale_status,
                'date' => $sale->sale_date->format('M j, Y'),
                'route' => 'sales.transactions.show',
                'params' => [$sale],
            ])
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $alerts
     * @return array<string, list<array<string, mixed>>>
     */
    private function pendingAlertsByModule(array $alerts): array
    {
        $grouped = [];
        foreach ($alerts as $alert) {
            $module = ucfirst($alert['icon'] ?? 'general');
            $grouped[$module][] = $alert;
        }

        return $grouped;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function topMilkProducers(string $from, string $to, ?int $farmId, int $limit): array
    {
        return MilkRecord::query()
            ->join('milk_sessions', 'milk_records.milk_session_id', '=', 'milk_sessions.id')
            ->join('animals', 'milk_records.animal_id', '=', 'animals.id')
            ->where('milk_sessions.status', 'completed')
            ->whereBetween('milk_sessions.session_date', [$from, $to])
            ->when($farmId, fn ($q) => $q->where('milk_sessions.farm_id', $farmId))
            ->select(
                'animals.id',
                'animals.tag_number',
                'animals.name',
                DB::raw('SUM(milk_records.yield_liters) as total_liters')
            )
            ->groupBy('animals.id', 'animals.tag_number', 'animals.name')
            ->orderByDesc('total_liters')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->name ?: $row->tag_number,
                'tag' => $row->tag_number,
                'value' => (float) $row->total_liters,
                'display' => number_format((float) $row->total_liters, 1).' L',
            ])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function topCustomers(string $from, string $to, ?int $farmId, int $limit): array
    {
        $rows = $this->farmScope(SaleTransaction::query(), $farmId)
            ->whereBetween('sale_date', [$from, $to])
            ->where('sale_status', 'completed')
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('customer_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $customers = Customer::query()
            ->whereIn('id', $rows->pluck('customer_id'))
            ->get()
            ->keyBy('id');

        return $rows->map(function ($row) use ($customers) {
            $customer = $customers->get($row->customer_id);

            return [
                'label' => $customer?->display_name ?? 'Customer',
                'value' => (float) $row->revenue,
                'display' => number_format((float) $row->revenue, 0).' RWF',
                'route' => $customer ? 'customers.show' : null,
                'params' => $customer ? [$customer] : [],
            ];
        })->all();
    }

    /**
     * @return array{labels: list<string>, revenue: list<float>, expenses: list<float>}
     */
    private function revenueExpenseTrends(?int $farmId): array
    {
        $labels = [];
        $revenue = [];
        $expenses = [];
        $start = now()->subMonths(self::TREND_MONTHS - 1)->startOfMonth();

        for ($i = 0; $i < self::TREND_MONTHS; $i++) {
            $month = $start->copy()->addMonths($i);
            $from = $month->copy()->startOfMonth()->toDateString();
            $to = $month->copy()->endOfMonth()->toDateString();
            $labels[] = $month->format('M');

            $revenue[] = (float) $this->farmScope(SaleTransaction::query(), $farmId)
                ->whereBetween('sale_date', [$from, $to])
                ->where('sale_status', 'completed')
                ->sum('total_amount');

            $expenses[] = (float) $this->farmScope(Expense::query(), $farmId)
                ->whereBetween('expense_date', [$from, $to])
                ->where('status', 'paid')
                ->sum('amount');
        }

        return compact('labels', 'revenue', 'expenses');
    }

    /**
     * @return array{labels: list<string>, datasets: list<array{label: string, data: list<float>}>}
     */
    private function milkProductionTrend(?int $farmId): array
    {
        $labels = [];
        $start = now()->subMonths(self::TREND_MONTHS - 1)->startOfMonth();

        for ($i = 0; $i < self::TREND_MONTHS; $i++) {
            $labels[] = $start->copy()->addMonths($i)->format('M');
        }

        $farmsQuery = Farm::query()->orderBy('name');
        if ($farmId) {
            $farmsQuery->where('id', $farmId);
        }

        $farms = $farmsQuery->get();
        if ($farms->isEmpty()) {
            return ['labels' => $labels, 'datasets' => []];
        }

        $monthRanges = [];
        for ($i = 0; $i < self::TREND_MONTHS; $i++) {
            $month = $start->copy()->addMonths($i);
            $monthRanges[] = [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ];
        }

        $datasets = [];
        foreach ($farms->take(5) as $index => $farm) {
            $data = [];
            foreach ($monthRanges as [$from, $to]) {
                $data[] = (float) MilkSession::query()
                    ->where('farm_id', $farm->id)
                    ->where('status', 'completed')
                    ->whereBetween('session_date', [$from, $to])
                    ->sum('total_yield_liters');
            }
            $datasets[] = [
                'label' => $farm->name,
                'data' => $data,
                'color' => self::CHART_COLORS[$index % count(self::CHART_COLORS)],
            ];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    private function salesByType(string $from, string $to, ?int $farmId): array
    {
        $rows = $this->farmScope(SaleTransaction::query(), $farmId)
            ->whereBetween('sale_date', [$from, $to])
            ->where('sale_status', 'completed')
            ->select('sale_type', DB::raw('SUM(total_amount) as total'))
            ->groupBy('sale_type')
            ->get();

        return [
            'labels' => $rows->map(function ($r) {
                return config('modules.sale_type_labels.'.$r->sale_type)
                    ?? ucfirst(str_replace('_', ' ', (string) $r->sale_type));
            })->values()->all(),
            'values' => $rows->pluck('total')->map(fn ($v) => (float) $v)->values()->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    private function expenseBreakdown(string $from, string $to, ?int $farmId): array
    {
        $query = Expense::query()
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->whereBetween('expense_date', [$from, $to])
            ->where('expenses.status', 'paid');

        if ($farmId) {
            $query->where('expenses.farm_id', $farmId);
        }

        $rows = $query
            ->select('expense_categories.expense_group', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.expense_group')
            ->orderByDesc('total')
            ->get();

        $labels = config('modules.expense_groups', []);

        return [
            'labels' => $rows->map(fn ($r) => $labels[$r->expense_group] ?? ucfirst(str_replace('_', ' ', $r->expense_group)))->values()->all(),
            'values' => $rows->pluck('total')->map(fn ($v) => (float) $v)->values()->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>, colors: list<string>}
     */
    private function animalHealthChart(?int $farmId): array
    {
        $counts = $this->farmScope(Animal::query(), $farmId)
            ->selectRaw('health_status, COUNT(*) as total')
            ->groupBy('health_status')
            ->pluck('total', 'health_status');

        $statusColors = [
            'Healthy' => '#A4D400',
            'Pregnant' => '#4ade80',
            'Sick' => '#f87171',
            'Under treatment' => '#fb923c',
            'Quarantined' => '#fbbf24',
            'Recovering' => '#60a5fa',
            'Deceased' => '#6b7280',
        ];

        $labels = [];
        $values = [];
        $colors = [];

        foreach ($counts as $status => $count) {
            if ((int) $count === 0) {
                continue;
            }
            $labels[] = $status ?: 'Unset';
            $values[] = (int) $count;
            $colors[] = $statusColors[$status] ?? '#94a3b8';
        }

        return compact('labels', 'values', 'colors');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function alerts(?int $farmId = null): array
    {
        $alerts = collect();

        $attention = $this->animalsNeedingAttention($farmId);
        if ($attention > 0) {
            $alerts->push($this->alert('critical', 'Animals need attention', "{$attention} animal(s) require care.", 'health.overview', 'health', 'Health'));
        }

        $lowStock = $this->lowFeedStockCount($farmId);
        if ($lowStock > 0) {
            $alerts->push($this->alert('warning', 'Feed inventory low', "{$lowStock} item(s) at or below reorder level.", 'feeding.overview', 'feeding', 'Feeding'));
        }

        $vaccDue = Vaccination::query()
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '<=', now()->addDays(30)->toDateString())
            ->count();

        if ($vaccDue > 0) {
            $alerts->push($this->alert('warning', 'Vaccinations due', "{$vaccDue} due within 30 days.", 'health.vaccinations', 'health', 'Health'));
        }

        $certExpiring = Certificate::query()
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->whereNotNull('expires_on')
            ->whereDate('expires_on', '>=', now()->toDateString())
            ->whereDate('expires_on', '<=', now()->addDays(30)->toDateString())
            ->count();

        if ($certExpiring > 0) {
            $alerts->push($this->alert('warning', 'Certificates expiring', "{$certExpiring} expire within 30 days.", 'certificates.index', 'certificate', 'Certificates'));
        }

        $overLimit = Customer::query()
            ->join('customer_credit', 'customers.id', '=', 'customer_credit.customer_id')
            ->whereColumn('customer_credit.outstanding_balance', '>', 'customer_credit.credit_limit')
            ->where('customer_credit.credit_limit', '>', 0)
            ->count();

        if ($overLimit > 0) {
            $alerts->push($this->alert('critical', 'Credit limit exceeded', "{$overLimit} customer(s) over limit.", 'customers.directory', 'customer', 'Customers'));
        }

        $calvingSoon = BreedingRecord::query()
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->where('breeding_status', 'confirmed_pregnant')
            ->whereBetween('expected_calving_date', [now()->toDateString(), now()->addDays(14)->toDateString()])
            ->count();

        if ($calvingSoon > 0) {
            $alerts->push($this->alert('info', 'Calvings approaching', "{$calvingSoon} expected in 14 days.", 'breeding.overview', 'breeding', 'Breeding'));
        }

        $pregnancyChecksDue = $this->breedingReminders->dueCount($farmId);
        if ($pregnancyChecksDue > 0) {
            $days = $this->breedingReminders->dueAfterDays();
            $alerts->push($this->alert(
                'warning',
                'Pregnancy checks due',
                "{$pregnancyChecksDue} breeding(s) need a pregnancy check ({$days} days after breeding).",
                'breeding.overview',
                'breeding',
                'Breeding',
                'pregnancy-check-due',
            ));
        }

        $unpaidSales = $this->farmScope(SaleTransaction::query(), $farmId)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotIn('sale_status', ['cancelled', 'draft'])
            ->count();

        if ($unpaidSales > 0) {
            $alerts->push($this->alert('warning', 'Outstanding payments', "{$unpaidSales} sale(s) unpaid.", 'sales.overview', 'sale', 'Sales'));
        }

        return $alerts->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function alert(
        string $severity,
        string $title,
        string $message,
        ?string $route,
        string $icon,
        string $module,
        ?string $routeFragment = null,
    ): array {
        return [
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'route' => $route && Route::has($route) ? $route : null,
            'route_fragment' => $routeFragment,
            'icon' => $icon,
            'module' => $module,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentActivity(?int $farmId): array
    {
        $items = collect();

        $this->farmScope(SaleTransaction::query(), $farmId)
            ->with(['farm', 'customer'])
            ->orderByDesc('sale_date')->limit(6)->get()
            ->each(function (SaleTransaction $sale) use ($items) {
                $items->push([
                    'at' => $sale->sale_date->startOfDay(),
                    'icon' => 'sale',
                    'module' => 'Sales',
                    'title' => $sale->sale_number,
                    'meta' => number_format($sale->total_amount, 0).' '.$sale->currency,
                    'route' => 'sales.transactions.show',
                    'params' => [$sale],
                ]);
            });

        $this->farmScope(Expense::query(), $farmId)
            ->with('category')->orderByDesc('expense_date')->limit(5)->get()
            ->each(function (Expense $expense) use ($items) {
                $items->push([
                    'at' => $expense->expense_date->startOfDay(),
                    'icon' => 'expense',
                    'module' => 'Expenses',
                    'title' => $expense->category?->name ?? 'Expense',
                    'meta' => number_format($expense->amount, 0).' RWF',
                    'route' => 'expenses.records.edit',
                    'params' => [$expense],
                ]);
            });

        $this->farmScope(HealthRecord::query(), $farmId)
            ->orderByDesc('recorded_on')->limit(4)->get()
            ->each(function (HealthRecord $record) use ($items) {
                $items->push([
                    'at' => $record->recorded_on->startOfDay(),
                    'icon' => 'health',
                    'module' => 'Health',
                    'title' => $record->record_type,
                    'meta' => $record->recorded_on->format('M j'),
                    'route' => 'health.records.edit',
                    'params' => [$record],
                ]);
            });

        return $items
            ->sortByDesc(fn ($item) => $item['at']->timestamp)
            ->take(self::ACTIVITY_LIMIT)
            ->values()
            ->all();
    }

    /**
     * @template T of Builder
     * @param  T  $query
     * @return T
     */
    private function farmScope(Builder $query, ?int $farmId, string $column = 'farm_id'): Builder
    {
        if ($farmId) {
            $query->where($column, $farmId);
        }

        return $query;
    }

    private function animalsNeedingAttention(?int $farmId = null): int
    {
        return $this->farmScope(Animal::query(), $farmId)
            ->whereIn('health_status', ['Sick', 'Under treatment', 'Quarantined', 'Recovering'])
            ->count();
    }

    private function healthFollowupsCount(?int $farmId = null): int
    {
        return $this->farmScope(HealthRecord::query(), $farmId)
            ->whereNotNull('next_follow_up')
            ->whereDate('next_follow_up', '>=', now()->toDateString())
            ->whereDate('next_follow_up', '<=', now()->addDays(30)->toDateString())
            ->count();
    }

    private function lowFeedStockCount(?int $farmId = null): int
    {
        return FeedInventory::query()
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->whereNotNull('reorder_level')
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->count();
    }
}
