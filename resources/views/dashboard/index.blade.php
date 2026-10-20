@extends('layouts.dashboard')

@section('title', __('Dashboard'))

@php
    $f = $dashboard['filters'] ?? [];
    $strip = $dashboard['alertStrip'] ?? [];
    $fin = $dashboard['financial'] ?? [];
    $live = $dashboard['livestock'] ?? [];
    $charts = $dashboard['charts'] ?? [];
    $sales = $dashboard['recentSales'] ?? [];
    $alertGroups = $dashboard['pendingAlerts'] ?? [];
    $topAnimals = $dashboard['topAnimals'] ?? [];
    $topCustomers = $dashboard['topCustomers'] ?? [];
    $activity = $dashboard['activity'] ?? [];
    $moduleStrips = $dashboard['moduleStrips'] ?? [];
    $isPoultry = ($dashboard['species'] ?? 'cattle') === 'poultry';
    $periodLabel = $f['label'] ?? null;

    $money = function (float $amount): string {
        if (abs($amount) >= 1_000_000) {
            return number_format($amount / 1_000_000, 1).'M';
        }
        if (abs($amount) >= 10_000) {
            return number_format($amount / 1_000, 0).'K';
        }

        return number_format($amount, 0);
    };

    $trendFromSeries = function (array $series): ?array {
        if (count($series) < 2) {
            return null;
        }

        $current = (float) $series[count($series) - 1];
        $previous = (float) $series[count($series) - 2];

        if ($previous == 0.0 && $current == 0.0) {
            return null;
        }

        if ($previous == 0.0) {
            return ['direction' => 'up', 'label' => __('New'), 'pct' => null];
        }

        $pct = (($current - $previous) / abs($previous)) * 100;
        $direction = $pct > 0.5 ? 'up' : ($pct < -0.5 ? 'down' : 'flat');

        return [
            'direction' => $direction,
            'pct' => round(abs($pct), 0),
            'label' => $direction === 'flat'
                ? __('vs last month')
                : __(':pct% vs last month', ['pct' => number_format(abs($pct), 0)]),
        ];
    };

    $revenueTrend = $trendFromSeries($charts['revenueExpenses']['revenue'] ?? []);
    $expenseTrend = $trendFromSeries($charts['revenueExpenses']['expenses'] ?? []);

    $netProfit = (float) ($fin['net_profit'] ?? 0);
    $stockRoute = $isPoultry
        ? route('flocks.index', $f['farm_id'] ? ['farm_id' => $f['farm_id']] : [])
        : route('animals.index', $f['farm_id'] ? ['farm_id' => $f['farm_id']] : []);
@endphp

@section('content')
    <div class="farm-dash">
        @include('dashboard.partials.toolbar')

        @php
            $severityOrder = ['critical' => 0, 'warning' => 1, 'info' => 2];
            $allAlerts = collect($alertGroups ?? [])->flatten(1)
                ->sortBy(fn ($alert) => $severityOrder[$alert['severity']] ?? 9)
                ->values();
            $severityLabels = [
                'critical' => __('Critical'),
                'warning' => __('Warning'),
                'info' => __('For your information'),
            ];
            $releaseUpdates = collect();
            try {
                $releaseUpdates = auth()->user()->unreadNotifications()->whereNotNull('platform_release_id')->limit(10)->get();
            } catch (\Illuminate\Database\QueryException) {
                // Notifications table not migrated yet.
            }
            $releaseTones = config('releases.category_tones', []);
            $severityMeaning = [
                'update' => __('New OroraFarm features and changes you have not read yet.'),
                'all' => __('Everything on your farms that needs attention, most urgent first.'),
                'critical' => __('Critical: act today. These can harm animals or cost money if left.'),
                'warning' => __('Warnings: act soon, usually within the next days or weeks.'),
                'info' => __('For your information: nothing is wrong, but plan ahead.'),
            ];
        @endphp

        <section class="farm-dash__alerts dash-alert-center" data-alert-center aria-label="{{ __('Alert summary') }}">
            @if ($allAlerts->isEmpty() && $releaseUpdates->isEmpty())
                <div class="dash-ops-alert-strip dash-alert-center__clear">
                    <span class="dash-alert-center__ok" aria-hidden="true">✓</span>
                    <span><strong>{{ __('No alerts') }}</strong> · {{ __('Nothing needs your attention right now.') }}</span>
                    <a href="{{ route('whats-new.index') }}" class="dash-alert-center__tab dash-alert-center__tab--update" style="margin-left: auto;">{{ __("What's new") }} →</a>
                </div>
            @else
                <div class="dash-ops-alert-strip dash-alert-center__strip">
                    <span class="dash-alert-center__label">{{ __('Needs attention') }}</span>
                    @foreach (['all' => __('All'), 'critical' => __('Critical'), 'warning' => __('Warnings'), 'info' => __('Info')] as $key => $label)
                        @php $count = $key === 'all' ? $allAlerts->count() : $allAlerts->where('severity', $key)->count(); @endphp
                        @if ($key !== 'info' || $count > 0)
                            <button
                                type="button"
                                class="dash-alert-center__tab dash-alert-center__tab--{{ $key }}"
                                data-alert-filter="{{ $key }}"
                                aria-expanded="false"
                                aria-controls="dash-alert-drawer"
                                @disabled($count === 0)
                            >
                                @if ($key !== 'all')<span class="dash-ops-alert-strip__dot" aria-hidden="true"></span>@endif
                                <strong>{{ $count }}</strong> {{ $label }}
                            </button>
                        @endif
                    @endforeach
                    @if ($releaseUpdates->isNotEmpty())
                        <button
                            type="button"
                            class="dash-alert-center__tab dash-alert-center__tab--update"
                            data-alert-filter="update"
                            aria-expanded="false"
                            aria-controls="dash-alert-drawer"
                        >
                            <span class="dash-ops-alert-strip__dot" aria-hidden="true"></span>
                            <strong>{{ $releaseUpdates->count() }}</strong> {{ __("What's new") }}
                        </button>
                    @else
                        <a href="{{ route('whats-new.index') }}" class="dash-alert-center__tab dash-alert-center__tab--update">
                            {{ __("What's new") }} →
                        </a>
                    @endif
                    <span class="dash-alert-center__tip">{{ __('Click to see details') }}</span>
                </div>

                <div class="dash-alert-center__drawer" id="dash-alert-drawer" data-alert-drawer hidden>
                    <div class="dash-alert-center__drawer-head">
                        <p class="dash-alert-center__meaning" data-alert-meaning aria-live="polite">{{ $severityMeaning['all'] }}</p>
                        <button type="button" class="dash-alert-center__close" data-alert-close aria-label="{{ __('Close alert details') }}">✕</button>
                    </div>
                    <ul class="dash-alert-center__list">
                        @foreach ($allAlerts as $alert)
                            @php
                                $alertUrl = $alert['route'] ? route($alert['route']).(! empty($alert['route_fragment']) ? '#'.$alert['route_fragment'] : '') : null;
                            @endphp
                            <li class="dash-alert-center__item dash-alert-center__item--{{ $alert['severity'] }}" data-alert-severity="{{ $alert['severity'] }}">
                                <div class="dash-alert-center__icon" aria-hidden="true">
                                    @include('layouts.partials.dashboard-nav-icon', ['icon' => $alert['icon'] ?? 'shield'])
                                </div>
                                <div class="dash-alert-center__text">
                                    <div class="dash-alert-center__title-row">
                                        <span class="dash-alert-center__badge dash-alert-center__badge--{{ $alert['severity'] }}">{{ $severityLabels[$alert['severity']] ?? ucfirst($alert['severity']) }}</span>
                                        <strong>{{ __($alert['title']) }}</strong>
                                        <span class="dash-alert-center__module">{{ __($alert['module']) }}</span>
                                    </div>
                                    <p class="dash-alert-center__message">{{ __($alert['message'], $alert['message_replace'] ?? []) }}</p>
                                    @if (! empty($alert['hint']))
                                        <p class="dash-alert-center__hint"><span>{{ __('What to do:') }}</span> {{ __($alert['hint']) }}</p>
                                    @endif
                                </div>
                                @if ($alertUrl)
                                    <a href="{{ $alertUrl }}" class="dash-alert-center__action">{{ __('Open :module', ['module' => __($alert['module'])]) }} →</a>
                                @endif
                            </li>
                        @endforeach
                        @foreach ($releaseUpdates as $update)
                            @php $data = $update->data; @endphp
                            <li class="dash-alert-center__item dash-alert-center__item--update" data-alert-severity="update">
                                <div class="dash-alert-center__icon" aria-hidden="true">
                                    @include('layouts.partials.dashboard-nav-icon', ['icon' => config('releases.category_icons.'.($data['category'] ?? ''), 'box')])
                                </div>
                                <div class="dash-alert-center__text">
                                    <div class="dash-alert-center__title-row">
                                        <span class="health-table__pill health-table__pill--{{ $releaseTones[$data['category'] ?? ''] ?? 'muted' }}">{{ __($data['category_label'] ?? 'Update') }}</span>
                                        <strong>{{ $data['title'] ?? __('Update') }}</strong>
                                        @if (! empty($data['version']))
                                            <span class="dash-alert-center__module">{{ $data['version'] }} · {{ $update->created_at->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                    @if (! empty($data['message']))
                                        <p class="dash-alert-center__hint">{{ $data['message'] }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('notifications.open', $update->id) }}" class="dash-alert-center__action">{{ __('Read more') }} →</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

        {{-- All KPIs --}}
        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <a href="{{ route('finance.overview', request()->only(['farm_id', 'from', 'to', 'period'])) }}" class="farm-kpi farm-kpi--revenue">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Revenue') }}</div>
                        <div class="farm-kpi__value">
                            {{ $money($fin['revenue'] ?? 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                        @if ($revenueTrend)
                            <div class="farm-kpi__trend farm-kpi__trend--{{ $revenueTrend['direction'] }}">
                                <span class="farm-kpi__trend-arrow" aria-hidden="true"></span>
                                {{ $revenueTrend['label'] }}
                            </div>
                        @endif
                    </div>
                </a>

                <a href="{{ route('expenses.overview') }}" class="farm-kpi farm-kpi--expense">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'expense'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Expenses') }}</div>
                        <div class="farm-kpi__value">
                            {{ $money($fin['expenses'] ?? 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                        @if ($expenseTrend)
                            <div class="farm-kpi__trend farm-kpi__trend--{{ $expenseTrend['direction'] }}">
                                <span class="farm-kpi__trend-arrow" aria-hidden="true"></span>
                                {{ $expenseTrend['label'] }}
                            </div>
                        @endif
                    </div>
                </a>

                <a href="{{ route('finance.reports.profit_loss', request()->only(['farm_id', 'from', 'to'])) }}" class="farm-kpi farm-kpi--profit {{ $netProfit < 0 ? 'farm-kpi--negative' : '' }}">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'finance'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Net profit') }}</div>
                        <div class="farm-kpi__value">
                            {{ $money($netProfit) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                        <div class="farm-kpi__hint">{{ __('Revenue minus expenses') }}</div>
                    </div>
                </a>

                <a href="{{ route('customers.overview') }}" class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'customer'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Accounts receivable') }}</div>
                        <div class="farm-kpi__value">
                            {{ $money($fin['accounts_receivable'] ?? 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                        <div class="farm-kpi__hint">{{ __('Outstanding customer balances') }}</div>
                    </div>
                </a>

                <a href="{{ $stockRoute }}" class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ $isPoultry ? __('Birds on hand') : __('Active animals') }}</div>
                        <div class="farm-kpi__value">{{ number_format($live['total_animals'] ?? 0) }}</div>
                    </div>
                </a>

                <a href="{{ route('farms.index') }}" class="farm-kpi farm-kpi--farms">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'farm'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Active farms') }}</div>
                        <div class="farm-kpi__value">{{ number_format($live['active_farms'] ?? 0) }}</div>
                    </div>
                </a>

                @if ($isPoultry)
                    <a href="{{ route('eggs.overview') }}" class="farm-kpi farm-kpi--health">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Flock mortality') }}</div>
                            <div class="farm-kpi__value">{{ number_format($live['mortality_percent'] ?? 0, 1) }}<span class="farm-kpi__unit">%</span></div>
                        </div>
                    </a>
                    <a href="{{ route('eggs.overview') }}" class="farm-kpi farm-kpi--production">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'milk'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Eggs collected') }}</div>
                            <div class="farm-kpi__value">{{ number_format($live['eggs_period'] ?? 0) }}</div>
                            @if ($periodLabel)
                                <div class="farm-kpi__hint">{{ $periodLabel }}</div>
                            @endif
                        </div>
                    </a>
                @else
                    <a href="{{ route('milk.overview') }}" class="farm-kpi farm-kpi--production">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'milk'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Lactating cows') }}</div>
                            <div class="farm-kpi__value">{{ number_format($live['lactating'] ?? 0) }}</div>
                        </div>
                    </a>
                    <a href="{{ route('sales.overview') }}" class="farm-kpi farm-kpi--sales">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Animals for sale') }}</div>
                            <div class="farm-kpi__value">{{ number_format($live['for_sale'] ?? 0) }}</div>
                            <div class="farm-kpi__hint">{{ __('Open sale listings') }}</div>
                        </div>
                    </a>
                @endif

                @foreach ($moduleStrips as $group)
                    @continue(($group['key'] ?? '') === 'feeding')
                    @php
                        $groupKey = $group['key'] ?? 'module';
                        $groupRoute = ! empty($group['route']) && Route::has($group['route'])
                            ? route($group['route'])
                            : '#';
                        $groupIcon = $group['icon'] ?? 'grid';
                    @endphp
                    @foreach (($group['metrics'] ?? []) as $metric)
                        <a href="{{ $groupRoute }}" class="farm-kpi farm-kpi--{{ $groupKey }}">
                            <div class="farm-kpi__icon" aria-hidden="true">
                                @include('layouts.partials.dashboard-nav-icon', ['icon' => $groupIcon])
                            </div>
                            <div class="farm-kpi__body">
                                <div class="farm-kpi__label">{{ __($metric['label'] ?? '') }}</div>
                                <div class="farm-kpi__value">{{ $metric['value'] ?? '0' }}</div>
                            </div>
                        </a>
                    @endforeach
                @endforeach
            </div>
        </section>

        {{-- Trend charts --}}
        <section class="farm-dash__section" aria-label="{{ __('Trend charts') }}">
            <div class="farm-dash__charts farm-dash__charts--2">
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Revenue vs expenses') }}</h2>
                            <p class="farm-panel__desc">{{ __('Last 6 months') }}</p>
                        </div>
                    </header>
                    <div class="farm-panel__chart farm-panel__chart--lg">
                        <canvas id="chart-revenue-expenses" aria-label="{{ __('Revenue vs expenses') }}"></canvas>
                        <p class="farm-panel__empty" id="chart-revenue-expenses-empty" hidden>{{ __('No financial data for this range.') }}</p>
                    </div>
                </article>
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ $charts['productionChartTitle'] ?? __('Milk production') }}</h2>
                            <p class="farm-panel__desc">{{ __('Last 6 months by farm') }}</p>
                        </div>
                    </header>
                    <div class="farm-panel__chart farm-panel__chart--lg">
                        <canvas id="chart-milk-trend" aria-label="{{ __('Production trend') }}"></canvas>
                        <p class="farm-panel__empty" id="chart-milk-trend-empty" hidden>{{ __('No production data for this range.') }}</p>
                    </div>
                </article>
            </div>
        </section>

        {{-- Sales + Alerts --}}
        <section class="farm-dash__section" aria-label="{{ __('Sales and alerts') }}">
            <div class="farm-dash__charts farm-dash__charts--2">
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Recent sales') }}</h2>
                            <p class="farm-panel__desc">{{ __('Latest transactions in this period') }}</p>
                        </div>
                    </header>
                    @if (empty($sales))
                        <p class="dash-empty">{{ __('No sales in this period.') }}</p>
                    @else
                        <div class="dash-table-wrap farm-table-wrap">
                            <table class="dash-table farm-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Sale') }}</th>
                                        <th>{{ __('Customer') }}</th>
                                        <th class="farm-table__num">{{ __('Amount') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sales as $sale)
                                        <tr>
                                            <td>
                                                <a href="{{ route($sale['route'], $sale['params']) }}" class="farm-table__entity">
                                                    <span class="farm-table__avatar farm-table__avatar--sale" aria-hidden="true">
                                                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                                                    </span>
                                                    <span class="farm-table__entity-text">
                                                        <span class="farm-table__primary">{{ $sale['number'] }}</span>
                                                        <span class="farm-table__secondary">{{ $sale['date'] ?? '' }}</span>
                                                    </span>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="farm-table__primary">{{ $sale['customer'] }}</span>
                                            </td>
                                            <td class="farm-table__num">
                                                <span class="farm-table__amount">{{ number_format($sale['amount'], 0) }}</span>
                                                <span class="farm-table__secondary">{{ $sale['currency'] }}</span>
                                            </td>
                                            <td>
                                                @include('modules.sales.partials.sale-status-badge', [
                                                    'saleStatus' => $sale['sale_status'] ?? null,
                                                    'saleStatusLabel' => $sale['status'],
                                                ])
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </article>

                <article class="farm-panel" id="dashboard-alerts" tabindex="-1">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Alerts') }}</h2>
                            <p class="farm-panel__desc">{{ __('Items that need attention') }}</p>
                        </div>
                    </header>
                    @if (empty($alertGroups))
                        <p class="dash-empty">{{ __('No pending alerts.') }}</p>
                    @else
                        <p class="dash-ops-alerts-filter-empty dash-empty" hidden>{{ __('No alerts match this filter.') }}</p>
                        <div class="dash-ops-alert-groups farm-alerts" data-alert-list>
                            @foreach ($alertGroups as $module => $moduleAlerts)
                                <div class="dash-ops-alert-group" data-alert-group>
                                    <div class="dash-ops-alert-group__title">{{ __($module) }}</div>
                                    <ul>
                                        @foreach ($moduleAlerts as $alert)
                                            <li data-alert-severity="{{ $alert['severity'] }}">
                                                @if ($alert['route'])
                                                    @php
                                                        $alertUrl = route($alert['route']);
                                                        if (! empty($alert['route_fragment'])) {
                                                            $alertUrl .= '#'.$alert['route_fragment'];
                                                        }
                                                    @endphp
                                                    <a href="{{ $alertUrl }}" class="dash-ops-alert-line dash-ops-alert-line--{{ $alert['severity'] }} dash-ops-alert-line--link farm-alert-line">
                                                        <span class="farm-alert-line__dot" aria-hidden="true"></span>
                                                        <span class="farm-alert-line__content">
                                                            <strong>{{ __($alert['title']) }}</strong>
                                                            <span>{{ __($alert['message'], $alert['message_replace'] ?? []) }}</span>
                                                        </span>
                                                    </a>
                                                @else
                                                    <div class="dash-ops-alert-line dash-ops-alert-line--{{ $alert['severity'] }} farm-alert-line">
                                                        <span class="farm-alert-line__dot" aria-hidden="true"></span>
                                                        <span class="farm-alert-line__content">
                                                            <strong>{{ __($alert['title']) }}</strong>
                                                            <span>{{ __($alert['message'], $alert['message_replace'] ?? []) }}</span>
                                                        </span>
                                                    </div>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>
            </div>
        </section>

        {{-- Rankings + Activity --}}
        <section class="farm-dash__section" aria-label="{{ __('Rankings and activity') }}">
            <div class="farm-dash__charts farm-dash__charts--3">
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Top animals') }}</h2>
                            <p class="farm-panel__desc">{{ __('Highest milk yield this period') }}</p>
                        </div>
                    </header>
                    @if (empty($topAnimals))
                        <p class="dash-empty">{{ $isPoultry ? __('Not available for poultry farms.') : __('No milk records in this period.') }}</p>
                    @else
                        <ol class="farm-rank">
                            @foreach ($topAnimals as $i => $animal)
                                <li>
                                    <span class="farm-rank__n" aria-hidden="true">{{ $i + 1 }}</span>
                                    <span class="farm-rank__label">{{ $animal['label'] }}</span>
                                    <span class="farm-rank__value">{{ $animal['display'] }}</span>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </article>

                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Top customers') }}</h2>
                            <p class="farm-panel__desc">{{ __('Highest spend this period') }}</p>
                        </div>
                    </header>
                    @if (empty($topCustomers))
                        <p class="dash-empty">{{ __('No customer sales in this period.') }}</p>
                    @else
                        <ol class="farm-rank">
                            @foreach ($topCustomers as $i => $customer)
                                <li>
                                    <span class="farm-rank__n farm-rank__n--customer" aria-hidden="true">
                                        {{ mb_strtoupper(mb_substr($customer['label'], 0, 1)) }}
                                    </span>
                                    @if ($customer['route'])
                                        <a href="{{ route($customer['route'], $customer['params']) }}" class="farm-rank__label">{{ $customer['label'] }}</a>
                                    @else
                                        <span class="farm-rank__label">{{ $customer['label'] }}</span>
                                    @endif
                                    <span class="farm-rank__value">{{ $customer['display'] }}</span>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </article>

                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Recent activity') }}</h2>
                            <p class="farm-panel__desc">{{ __('Latest events across modules') }}</p>
                        </div>
                    </header>
                    @if (empty($activity))
                        <p class="dash-empty">{{ __('No recent events.') }}</p>
                    @else
                        <ul class="farm-activity">
                            @foreach ($activity as $item)
                                <li class="farm-activity__item">
                                    <div class="farm-activity__icon" aria-hidden="true">
                                        @include('layouts.partials.dashboard-nav-icon', ['icon' => $item['icon']])
                                    </div>
                                    <div class="farm-activity__body">
                                        @if (! empty($item['route']) && Route::has($item['route']))
                                            <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="farm-activity__title">{{ $item['title'] }}</a>
                                        @else
                                            <span class="farm-activity__title">{{ $item['title'] }}</span>
                                        @endif
                                        <span class="farm-activity__meta">{{ __($item['module']) }} · {{ $item['meta'] }}</span>
                                        <time class="farm-activity__time" datetime="{{ $item['at']->toIso8601String() }}">{{ $item['at']->diffForHumans() }}</time>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </article>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const charts = @json($charts);
            const brand = {
                lime: '#A4D400',
                teal: '#002B2B',
                muted: '#94a3b8',
                grid: 'rgba(148, 163, 184, 0.18)',
                text: '#64748b',
            };

            const font = { family: 'inherit', size: 11 };

            const tooltip = {
                backgroundColor: '#002B2B',
                titleColor: '#fff',
                bodyColor: 'rgba(255,255,255,0.85)',
                padding: 10,
                cornerRadius: 8,
                displayColors: true,
                boxPadding: 4,
            };

            const basePlugins = {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        boxHeight: 10,
                        borderRadius: 2,
                        useBorderRadius: true,
                        padding: 14,
                        color: brand.text,
                        font,
                    },
                },
                tooltip,
            };

            const yFmt = {
                beginAtZero: true,
                border: { display: false },
                grid: { color: brand.grid, drawTicks: false },
                ticks: {
                    color: brand.text,
                    font,
                    padding: 6,
                    callback: (v) => v >= 1e6 ? (v / 1e6).toFixed(1) + 'M' : v >= 1e3 ? (v / 1e3).toFixed(0) + 'k' : v,
                },
            };

            const xClean = {
                border: { display: false },
                grid: { display: false },
                ticks: { color: brand.text, font, maxRotation: 0 },
            };

            function mountOrEmpty(canvasId, emptyId, hasData, factory) {
                const canvas = document.getElementById(canvasId);
                const empty = document.getElementById(emptyId);
                if (!canvas) return;
                if (!hasData) {
                    canvas.hidden = true;
                    if (empty) empty.hidden = false;
                    return;
                }
                if (empty) empty.hidden = true;
                factory(canvas);
            }

            const rev = charts.revenueExpenses || {};
            mountOrEmpty('chart-revenue-expenses', 'chart-revenue-expenses-empty', !!rev.labels?.length, (el) => {
                new Chart(el, {
                    type: 'bar',
                    data: {
                        labels: rev.labels,
                        datasets: [
                            {
                                label: @json(__('Revenue')),
                                data: rev.revenue,
                                backgroundColor: brand.lime,
                                borderRadius: 6,
                                borderSkipped: false,
                                maxBarThickness: 28,
                            },
                            {
                                label: @json(__('Expenses')),
                                data: rev.expenses,
                                backgroundColor: brand.teal,
                                borderRadius: 6,
                                borderSkipped: false,
                                maxBarThickness: 28,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: basePlugins,
                        scales: { x: xClean, y: yFmt },
                    },
                });
            });

            const milk = charts.milkTrend || {};
            mountOrEmpty(
                'chart-milk-trend',
                'chart-milk-trend-empty',
                !!(milk.labels?.length && milk.datasets?.length),
                (el) => {
                    new Chart(el, {
                        type: 'line',
                        data: {
                            labels: milk.labels,
                            datasets: milk.datasets.map((ds) => ({
                                label: ds.label,
                                data: ds.data,
                                borderColor: ds.color,
                                backgroundColor: ds.color + '18',
                                borderWidth: 2,
                                tension: 0.35,
                                fill: false,
                                pointRadius: 3,
                                pointHoverRadius: 5,
                                pointBackgroundColor: ds.color,
                            })),
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: basePlugins,
                            scales: {
                                x: xClean,
                                y: { ...yFmt, ticks: { ...yFmt.ticks, callback: (v) => v } },
                            },
                        },
                    });
                }
            );

            document.getElementById('filter_period')?.addEventListener('change', function () {
                const custom = this.value === 'custom';
                document.getElementById('dash-custom-dates')?.classList.toggle('dash-ops-field--muted', !custom);
                if (!custom) document.getElementById('dash-filters-form')?.requestSubmit();
            });

            const alertCenter = document.querySelector('[data-alert-center]');
            const drawer = alertCenter?.querySelector('[data-alert-drawer]');
            const tabs = alertCenter ? alertCenter.querySelectorAll('[data-alert-filter]') : [];
            const meaning = drawer?.querySelector('[data-alert-meaning]');
            const meanings = @js($severityMeaning);
            let activeFilter = null;

            function showAlerts(filter) {
                if (!drawer) return;

                activeFilter = filter;
                drawer.hidden = filter === null;

                drawer.querySelectorAll('[data-alert-severity]').forEach((item) => {
                    item.hidden = filter === 'all'
                        ? item.dataset.alertSeverity === 'update'
                        : item.dataset.alertSeverity !== filter;
                });

                if (meaning && filter) meaning.textContent = meanings[filter] || '';

                tabs.forEach((tab) => {
                    const on = tab.dataset.alertFilter === filter;
                    tab.classList.toggle('is-active', on);
                    tab.setAttribute('aria-expanded', on ? 'true' : 'false');
                });

                history.replaceState(null, '', filter ? '#dashboard-alerts' + (filter === 'all' ? '' : '-' + filter) : location.pathname + location.search);
            }

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    const filter = tab.dataset.alertFilter || 'all';
                    showAlerts(activeFilter === filter ? null : filter);
                });
            });

            drawer?.querySelector('[data-alert-close]')?.addEventListener('click', () => showAlerts(null));

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && activeFilter !== null) showAlerts(null);
            });

            const hashFilter = location.hash.match(/^#dashboard-alerts(?:-(critical|warning|info|update))?$/);
            if (hashFilter && drawer) {
                showAlerts(hashFilter[1] || 'all');
                alertCenter.scrollIntoView({ behavior: 'auto', block: 'start' });
            }
        })();
    </script>
@endpush
