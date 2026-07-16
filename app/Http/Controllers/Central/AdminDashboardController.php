<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Central\ContactMessage;
use App\Models\Farm;
use App\Models\Livestock;
use App\Models\MilkSession;
use App\Models\SaleItem;
use App\Models\SaleTransaction;
use App\Models\TenantAccount;
use App\Services\AdminDashboardFilterService;
use App\Services\AdminFarmMapService;
use App\Services\AdminPlatformStatsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardFilterService $filters,
        private readonly AdminPlatformStatsService $stats,
        private readonly AdminFarmMapService $farmMap,
    ) {}

    public function index(Request $request): View
    {
        $filters = $this->filters->resolve($request);
        $rangeStart = $this->filters->rangeStart($filters);
        $rangeEnd = $this->filters->rangeEnd($filters);

        $livestockGroups = $this->safeQuery(
            Livestock::class,
            fn ($query) => $query
                ->with('farm:id,name,tenant_id')
                ->withCount(['animals as animals_count' => fn ($animalQuery) => $animalQuery
                    ->whereBetween('created_at', [$rangeStart, $rangeEnd])])
                ->whereBetween('created_at', [$rangeStart, $rangeEnd])
                ->orderBy('name')
                ->get(),
            collect(),
        );

        $stats = array_merge($this->stats->platformStats($filters, $rangeStart, $rangeEnd), [
            'contact_new' => $this->safeCount(
                ContactMessage::class,
                fn ($q) => $q->where('status', 'new')
                    ->whereBetween('created_at', [$rangeStart, $rangeEnd]),
            ),
        ]);

        $recentContacts = $this->safeQuery(
            ContactMessage::class,
            fn ($q) => $q
                ->whereBetween('created_at', [$rangeStart, $rangeEnd])
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
            collect(),
        );

        $recentFarms = $this->safeQuery(
            Farm::class,
            fn ($query) => $query
                ->whereBetween('created_at', [$rangeStart, $rangeEnd])
                ->orderByDesc('created_at')
                ->limit(8)
                ->get(),
            collect(),
        );

        $recentActivity = $this->buildRecentActivity($rangeStart, $rangeEnd);

        return view('central.dashboard.index', [
            'activeNav' => 'dashboard',
            'filters' => $filters,
            'stats' => $stats,
            'charts' => $this->buildCharts($filters),
            'recentFarms' => $recentFarms,
            'recentActivity' => $recentActivity,
            'recentContacts' => $recentContacts,
            'livestockGroups' => $livestockGroups,
            'farmMapMarkers' => $this->farmMap->markers(),
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Livestock>  $livestockGroups
     * @param  array{period: string, from: string, to: string, label: string}  $filters
     * @return array{
     *     milkYield: array{labels: list<string>, values: list<float>, interval: string},
     *     animalsSold: array{labels: list<string>, values: list<int>, interval: string},
     *     groups: array{labels: list<string>, values: list<int>}
     * }
     */
    private function buildCharts(array $filters): array
    {
        $milkYield = $this->buildMilkYieldChart($filters);
        $animalsSold = $this->buildAnimalsSoldChart($filters);
        $groups = $this->buildLivestockGroupsDonut();

        return [
            'milkYield' => $milkYield,
            'animalsSold' => $animalsSold,
            'groups' => $groups,
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function buildLivestockGroupsDonut(): array
    {
        $allGroups = $this->safeQuery(
            Livestock::class,
            fn ($query) => $query
                ->withCount('animals')
                ->orderBy('name')
                ->get(['id', 'name']),
            collect(),
        );

        $aggregated = $allGroups
            ->groupBy('name')
            ->map(fn ($items, $name) => [
                'name' => (string) $name,
                'animals' => (int) $items->sum('animals_count'),
            ])
            ->filter(fn (array $item) => $item['animals'] > 0)
            ->sortByDesc('animals')
            ->values();

        $top = $aggregated->take(7);
        $otherAnimals = (int) $aggregated->slice(7)->sum('animals');

        $labels = $top->pluck('name')->all();
        $values = $top->pluck('animals')->all();

        if ($otherAnimals > 0) {
            $labels[] = 'Other';
            $values[] = $otherAnimals;
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * @param  array{period: string, from: string, to: string}  $filters
     * @return array{labels: list<string>, values: list<float>, interval: string}
     */
    private function buildMilkYieldChart(array $filters): array
    {
        $start = Carbon::parse($filters['from'])->startOfDay();
        $end = Carbon::parse($filters['to'])->endOfDay();

        $bucket = match ($filters['period']) {
            'all' => 'year',
            default => $this->resolveCustomBucket($start, $end),
        };

        $chart = match ($bucket) {
            'year' => $this->bucketMilkYieldByYear($start, $end),
            default => $this->bucketMilkYieldByMonth($start, $end),
        };

        $chart['interval'] = $bucket === 'year' ? 'year' : 'month';

        return $chart;
    }

    /**
     * @param  array{period: string, from: string, to: string}  $filters
     * @return array{labels: list<string>, values: list<int>, interval: string}
     */
    private function buildAnimalsSoldChart(array $filters): array
    {
        $start = Carbon::parse($filters['from'])->startOfDay();
        $end = Carbon::parse($filters['to'])->endOfDay();

        $bucket = match ($filters['period']) {
            'all' => 'year',
            default => $this->resolveCustomBucket($start, $end),
        };

        $chart = match ($bucket) {
            'year' => $this->bucketAnimalsSoldByYear($start, $end),
            default => $this->bucketAnimalsSoldByMonth($start, $end),
        };

        $chart['interval'] = $bucket === 'year' ? 'year' : 'month';

        return $chart;
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function bucketAnimalsSoldByMonth(Carbon $start, Carbon $end): array
    {
        $labels = [];
        $values = [];
        $cursor = $start->copy()->startOfMonth();
        $limit = $end->copy()->startOfMonth();

        while ($cursor->lte($limit)) {
            $from = $cursor->copy()->startOfMonth()->max($start)->toDateString();
            $to = $cursor->copy()->endOfMonth()->min($end)->toDateString();
            $labels[] = $cursor->format('M Y');
            $values[] = $this->animalsSoldBetween($from, $to);
            $cursor->addMonth();
        }

        return compact('labels', 'values');
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function bucketAnimalsSoldByYear(Carbon $start, Carbon $end): array
    {
        $labels = [];
        $values = [];
        $cursor = $start->copy()->startOfYear();
        $limit = $end->copy()->startOfYear();

        while ($cursor->lte($limit)) {
            $from = $cursor->copy()->startOfYear()->max($start)->toDateString();
            $to = $cursor->copy()->endOfYear()->min($end)->toDateString();
            $labels[] = (string) $cursor->year;
            $values[] = $this->animalsSoldBetween($from, $to);
            $cursor->addYear();
        }

        return compact('labels', 'values');
    }

    private function animalsSoldBetween(string $from, string $to): int
    {
        return (int) $this->safeQuery(
            SaleTransaction::class,
            fn ($query) => $query
                ->where('sale_type', 'animal_sale')
                ->where('sale_status', 'completed')
                ->whereBetween('sale_date', [$from, $to])
                ->count(),
            0,
        );
    }

    /**
     * @param  array{period: string, from: string, to: string}  $filters
     * @return array{labels: list<string>, values: list<float>}
     */
    private function buildMilkSoldChart(array $filters): array
    {
        $start = Carbon::parse($filters['from'])->startOfDay();
        $end = Carbon::parse($filters['to'])->endOfDay();

        $bucket = match ($filters['period']) {
            'daily', 'monthly' => 'day',
            'yearly', 'all' => 'month',
            default => $this->resolveCustomBucket($start, $end),
        };

        if ($filters['period'] === 'all') {
            $start = now()->subMonths(11)->startOfMonth();
            if ($start->lt(Carbon::parse($filters['from'])->startOfDay())) {
                $start = Carbon::parse($filters['from'])->startOfDay();
            }
        }

        return match ($bucket) {
            'month' => $this->bucketMilkSoldByMonth($start, $end),
            'year' => $this->bucketMilkSoldByYear($start, $end),
            default => $this->bucketMilkSoldByDay($start, $end),
        };
    }

    private function resolveCustomBucket(Carbon $start, Carbon $end): string
    {
        $days = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;

        if ($days <= 31) {
            return 'day';
        }

        if ($days <= 366) {
            return 'month';
        }

        return 'year';
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    private function bucketMilkSoldByDay(Carbon $start, Carbon $end): array
    {
        $labels = [];
        $values = [];
        $cursor = $start->copy()->startOfDay();
        $limit = $end->copy()->startOfDay();

        while ($cursor->lte($limit)) {
            $date = $cursor->toDateString();
            $labels[] = $cursor->format('M j');
            $values[] = $this->litersSoldOnDate($date);
            $cursor->addDay();
        }

        return compact('labels', 'values');
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    private function bucketMilkSoldByMonth(Carbon $start, Carbon $end): array
    {
        $labels = [];
        $values = [];
        $cursor = $start->copy()->startOfMonth();
        $limit = $end->copy()->startOfMonth();

        while ($cursor->lte($limit)) {
            $from = $cursor->copy()->startOfMonth()->max($start)->toDateString();
            $to = $cursor->copy()->endOfMonth()->min($end)->toDateString();
            $labels[] = $cursor->format('M Y');
            $values[] = $this->litersSoldBetween($from, $to);
            $cursor->addMonth();
        }

        return compact('labels', 'values');
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    private function bucketMilkSoldByYear(Carbon $start, Carbon $end): array
    {
        $labels = [];
        $values = [];
        $cursor = $start->copy()->startOfYear();
        $limit = $end->copy()->startOfYear();

        while ($cursor->lte($limit)) {
            $from = $cursor->copy()->startOfYear()->max($start)->toDateString();
            $to = $cursor->copy()->endOfYear()->min($end)->toDateString();
            $labels[] = (string) $cursor->year;
            $values[] = $this->litersSoldBetween($from, $to);
            $cursor->addYear();
        }

        return compact('labels', 'values');
    }

    private function litersSoldOnDate(string $date): float
    {
        return $this->litersSoldBetween($date, $date);
    }

    private function litersSoldBetween(string $from, string $to): float
    {
        return (float) $this->safeQuery(
            SaleItem::class,
            fn ($query) => (float) $query
                ->join('sale_transactions', 'sale_items.sale_transaction_id', '=', 'sale_transactions.id')
                ->where('sale_transactions.sale_type', 'milk_sale')
                ->where('sale_transactions.sale_status', 'completed')
                ->whereBetween('sale_transactions.sale_date', [$from, $to])
                ->sum('sale_items.quantity'),
            0.0,
        );
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    private function bucketMilkYieldByMonth(Carbon $start, Carbon $end): array
    {
        $labels = [];
        $values = [];
        $cursor = $start->copy()->startOfMonth();
        $limit = $end->copy()->startOfMonth();

        while ($cursor->lte($limit)) {
            $from = $cursor->copy()->startOfMonth()->max($start)->toDateString();
            $to = $cursor->copy()->endOfMonth()->min($end)->toDateString();
            $labels[] = $cursor->format('M Y');
            $values[] = $this->litersYieldBetween($from, $to);
            $cursor->addMonth();
        }

        return compact('labels', 'values');
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    private function bucketMilkYieldByYear(Carbon $start, Carbon $end): array
    {
        $labels = [];
        $values = [];
        $cursor = $start->copy()->startOfYear();
        $limit = $end->copy()->startOfYear();

        while ($cursor->lte($limit)) {
            $from = $cursor->copy()->startOfYear()->max($start)->toDateString();
            $to = $cursor->copy()->endOfYear()->min($end)->toDateString();
            $labels[] = (string) $cursor->year;
            $values[] = $this->litersYieldBetween($from, $to);
            $cursor->addYear();
        }

        return compact('labels', 'values');
    }

    private function litersYieldBetween(string $from, string $to): float
    {
        return (float) $this->safeQuery(
            MilkSession::class,
            fn ($query) => (float) $query
                ->where('status', 'completed')
                ->whereBetween('session_date', [$from, $to])
                ->sum('total_yield_liters'),
            0.0,
        );
    }

    /**
     * @return list<array{at: \Carbon\Carbon|null, icon: string, module: string, title: string, meta: string}>
     */
    private function buildRecentActivity(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $items = collect();
        $inRange = fn ($query) => $query
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->orderByDesc('created_at');

        $this->safeQuery(
            TenantAccount::class,
            fn ($query) => $inRange($query)->limit(6)->get()
                ->each(function (TenantAccount $account) use ($items) {
                    $items->push([
                        'at' => $account->created_at,
                        'icon' => 'customer',
                        'module' => 'Account',
                        'title' => 'Farmer signed up',
                        'meta' => $account->email.' · '.$account->tenant_id,
                    ]);
                }),
            null,
        );

        $this->safeQuery(
            Farm::class,
            fn ($query) => $inRange($query)->limit(6)->get()
                ->each(function (Farm $farm) use ($items) {
                    $location = collect([$farm->district, $farm->province])->filter()->implode(', ') ?: 'Rwanda';

                    $items->push([
                        'at' => $farm->created_at,
                        'icon' => 'farm',
                        'module' => 'Farm',
                        'title' => $farm->name,
                        'meta' => $location.' · '.$farm->tenant_id,
                    ]);
                }),
            null,
        );

        $this->safeQuery(
            Livestock::class,
            fn ($query) => $inRange($query->with('farm:id,name'))->limit(5)->get()
                ->each(function (Livestock $group) use ($items) {
                    $items->push([
                        'at' => $group->created_at,
                        'icon' => 'livestock',
                        'module' => 'Livestock',
                        'title' => $group->name,
                        'meta' => ($group->farm?->name ?? 'Farm').' · '.$group->head_count.' head',
                    ]);
                }),
            null,
        );

        $this->safeQuery(
            Animal::class,
            fn ($query) => $inRange($query)->limit(5)->get()
                ->each(function (Animal $animal) use ($items) {
                    $label = $animal->name ?: $animal->tag_number ?: 'Animal #'.$animal->id;

                    $items->push([
                        'at' => $animal->created_at,
                        'icon' => 'animal',
                        'module' => 'Animal',
                        'title' => $label,
                        'meta' => ($animal->species ?? 'Livestock').' · '.$animal->tenant_id,
                    ]);
                }),
            null,
        );

        $this->safeQuery(
            ContactMessage::class,
            fn ($query) => $inRange($query)->limit(4)->get()
                ->each(function (ContactMessage $message) use ($items) {
                    $items->push([
                        'at' => $message->created_at,
                        'icon' => 'mail',
                        'module' => 'Contact',
                        'title' => $message->subject,
                        'meta' => $message->name.' · '.ucfirst($message->status),
                    ]);
                }),
            null,
        );

        return $items
            ->filter(fn (array $item) => $item['at'] !== null)
            ->sortByDesc(fn (array $item) => $item['at']->timestamp)
            ->take(12)
            ->values()
            ->all();
    }

    private function safeCount(string $model, ?callable $callback = null): int
    {
        return $this->safeQuery($model, function ($query) use ($callback) {
            if ($callback) {
                $callback($query);
            }

            return $query->count();
        }, 0);
    }

    private function safeQuery(string $model, callable $callback, mixed $default): mixed
    {
        try {
            $table = (new $model)->getTable();

            if (! Schema::connection((new $model)->getConnectionName())->hasTable($table)) {
                return $default;
            }

            return $callback($model::query());
        } catch (\Throwable) {
            return $default;
        }
    }
}
