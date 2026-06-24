<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Central\ContactMessage;
use App\Models\Farm;
use App\Models\Livestock;
use App\Models\SaleItem;
use App\Models\TenantAccount;
use App\Services\AdminDashboardFilterService;
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
            'charts' => $this->buildCharts($livestockGroups, $filters),
            'recentFarms' => $recentFarms,
            'recentActivity' => $recentActivity,
            'recentContacts' => $recentContacts,
            'livestockGroups' => $livestockGroups,
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Livestock>  $livestockGroups
     * @param  array{period: string, from: string, to: string, label: string}  $filters
     * @return array{
     *     milkSold: array{labels: list<string>, values: list<float>},
     *     pie: array{title: string, labels: list<string>, values: list<int>}
     * }
     */
    private function buildCharts($livestockGroups, array $filters): array
    {
        $milkSold = $this->buildMilkSoldChart($filters);

        $rankedGroups = $livestockGroups
            ->sortByDesc('animals_count')
            ->values();

        $topGroups = $rankedGroups->take(8);
        $otherAnimals = (int) $rankedGroups->slice(8)->sum('animals_count');

        $labels = $topGroups->pluck('name')->all();
        $values = $topGroups->pluck('animals_count')->map(fn ($count) => (int) $count)->all();

        if ($otherAnimals > 0) {
            $labels[] = 'Other groups';
            $values[] = $otherAnimals;
        }

        $pie = [
            'title' => 'Animals by livestock group',
            'labels' => $labels,
            'values' => $values,
        ];

        return [
            'milkSold' => $milkSold,
            'pie' => $pie,
        ];
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
            'yearly' => 'month',
            default => $this->resolveCustomBucket($start, $end),
        };

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
