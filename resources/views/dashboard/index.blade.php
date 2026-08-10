@extends('layouts.dashboard')

@section('title', 'Dashboard')

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

    $money = function (float $amount): string {
        if ($amount >= 1_000_000) {
            return number_format($amount / 1_000_000, 1).'M';
        }
        if ($amount >= 1_000) {
            return number_format($amount / 1_000, 0).'K';
        }

        return number_format($amount, 0);
    };
@endphp

@section('content')
    <div class="farm-dash">
        @include('dashboard.partials.toolbar')

        <nav class="dash-ops-alert-strip" aria-label="Alert summary">
            <a href="#dashboard-alerts" class="dash-ops-alert-strip__item" data-alert-filter="all">
                <strong>{{ $strip['total'] ?? 0 }}</strong> {{ __('alerts') }}
            </a>
            <a href="#dashboard-alerts-critical" class="dash-ops-alert-strip__item dash-ops-alert-strip__item--critical" data-alert-filter="critical">
                <span class="dash-ops-alert-strip__dot" aria-hidden="true"></span>
                <strong>{{ $strip['critical'] ?? 0 }}</strong> {{ __('critical') }}
            </a>
            <a href="#dashboard-alerts-warning" class="dash-ops-alert-strip__item dash-ops-alert-strip__item--warning" data-alert-filter="warning">
                <span class="dash-ops-alert-strip__dot" aria-hidden="true"></span>
                <strong>{{ $strip['warning'] ?? 0 }}</strong> {{ __('warnings') }}
            </a>
            @if (($strip['info'] ?? 0) > 0)
                <a href="#dashboard-alerts-info" class="dash-ops-alert-strip__item dash-ops-alert-strip__item--info" data-alert-filter="info">
                    <span class="dash-ops-alert-strip__dot" aria-hidden="true"></span>
                    <strong>{{ $strip['info'] }}</strong> {{ __('info') }}
                </a>
            @endif
        </nav>

        <section class="dash-ops-row" aria-label="Summary">
            <div class="dash-stats dash-ops-stats-4 farm-dash__kpis">
                <a href="{{ route('finance.overview', request()->only(['farm_id', 'from', 'to', 'period'])) }}" class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">{{ __('Revenue') }}</div>
                        <div class="dash-stat-value accent">{{ $money($fin['revenue'] ?? 0) }} <span class="dash-home-stat__suffix">RWF</span></div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'sale', 'label' => 'Revenue'])
                </a>
                <a href="{{ route('expenses.overview') }}" class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">{{ __('Expenses') }}</div>
                        <div class="dash-stat-value">{{ $money($fin['expenses'] ?? 0) }} <span class="dash-home-stat__suffix">RWF</span></div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'expense', 'label' => 'Expenses'])
                </a>
                <a href="{{ route('finance.reports.profit_loss', request()->only(['farm_id', 'from', 'to'])) }}" class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">{{ __('Net profit') }}</div>
                        <div class="dash-stat-value @if(($fin['net_profit'] ?? 0) < 0) alert @else accent @endif">{{ $money($fin['net_profit'] ?? 0) }} <span class="dash-home-stat__suffix">RWF</span></div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'finance', 'label' => 'Net profit'])
                </a>
                <a href="{{ route('customers.overview') }}" class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">{{ __('Receivable') }}</div>
                        <div class="dash-stat-value">{{ $money($fin['accounts_receivable'] ?? 0) }} <span class="dash-home-stat__suffix">RWF</span></div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'customer', 'label' => 'Accounts receivable'])
                </a>
                <a href="{{ route('animals.index', $f['farm_id'] ? ['farm_id' => $f['farm_id']] : []) }}" class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">{{ __('Animals') }}</div>
                        <div class="dash-stat-value">{{ number_format($live['total_animals'] ?? 0) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'animal', 'label' => 'Animals'])
                </a>
                <a href="{{ route('farms.index') }}" class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">{{ __('Farms') }}</div>
                        <div class="dash-stat-value">{{ number_format($live['active_farms'] ?? 0) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'farm', 'label' => 'Farms'])
                </a>
                <a href="{{ route('milk.overview') }}" class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">{{ __('Lactating') }}</div>
                        <div class="dash-stat-value accent">{{ number_format($live['lactating'] ?? 0) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'milk', 'label' => 'Lactating'])
                </a>
                <a href="{{ route('sales.overview') }}" class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">{{ __('For sale') }}</div>
                        <div class="dash-stat-value">{{ number_format($live['for_sale'] ?? 0) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'sale', 'label' => 'For sale'])
                </a>
            </div>
        </section>

        <section class="dash-ops-row dash-ops-charts-2" aria-label="Trend charts">
            <div class="dash-panel">
                <h2 class="dash-panel-title">{{ __('Revenue vs expenses') }}</h2>
                <div class="dash-home-chart-wrap"><canvas id="chart-revenue-expenses" aria-label="Revenue vs expenses"></canvas></div>
            </div>
            <div class="dash-panel">
                <h2 class="dash-panel-title">{{ __('Milk production') }}</h2>
                <div class="dash-home-chart-wrap"><canvas id="chart-milk-trend" aria-label="Milk production trend"></canvas></div>
            </div>
        </section>

        <section class="dash-ops-row dash-ops-charts-3" aria-label="Breakdown charts">
            <div class="dash-panel">
                <h2 class="dash-panel-title">{{ __('Sales by type') }}</h2>
                <div class="dash-ops-chart-sm"><canvas id="chart-sales-type" aria-label="Sales by type"></canvas></div>
            </div>
            <div class="dash-panel">
                <h2 class="dash-panel-title">{{ __('Expenses') }}</h2>
                <div class="dash-ops-chart-sm"><canvas id="chart-expenses" aria-label="Expense breakdown"></canvas></div>
            </div>
            <div class="dash-panel">
                <h2 class="dash-panel-title">{{ __('Animal health') }}</h2>
                <div class="dash-ops-chart-sm"><canvas id="chart-health" aria-label="Animal health status"></canvas></div>
            </div>
        </section>

        <section class="dash-ops-row dash-ops-charts-2" aria-label="Sales and alerts">
            <div class="dash-panel">
                <h2 class="dash-panel-title">{{ __('Recent sales') }}</h2>
                @if (empty($sales))
                    <p class="dash-empty">No sales in this period.</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="dash-table dash-table--compact">
                            <thead>
                                <tr>
                                    <th>Sale</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sales as $sale)
                                    <tr>
                                        <td><a href="{{ route($sale['route'], $sale['params']) }}">{{ $sale['number'] }}</a></td>
                                        <td>{{ $sale['customer'] }}</td>
                                        <td>{{ number_format($sale['amount'], 0) }} {{ $sale['currency'] }}</td>
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
            </div>

            <div class="dash-panel" id="dashboard-alerts" tabindex="-1">
                <h2 class="dash-panel-title">{{ __('Alerts') }}</h2>
                @if (empty($alertGroups))
                    <p class="dash-empty">No pending alerts.</p>
                @else
                    <p class="dash-ops-alerts-filter-empty dash-empty" hidden>No alerts match this filter.</p>
                    <div class="dash-ops-alert-groups" data-alert-list>
                        @foreach ($alertGroups as $module => $moduleAlerts)
                            <div class="dash-ops-alert-group" data-alert-group>
                                <div class="dash-ops-alert-group__title">{{ $module }}</div>
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
                                                <a href="{{ $alertUrl }}" class="dash-ops-alert-line dash-ops-alert-line--{{ $alert['severity'] }} dash-ops-alert-line--link">
                                                    <strong>{{ $alert['title'] }}</strong>
                                                    <span>{{ $alert['message'] }}</span>
                                                </a>
                                            @else
                                                <div class="dash-ops-alert-line dash-ops-alert-line--{{ $alert['severity'] }}">
                                                    <strong>{{ $alert['title'] }}</strong>
                                                    <span>{{ $alert['message'] }}</span>
                                                </div>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section class="dash-ops-row dash-ops-charts-3" aria-label="Rankings and activity">
            <div class="dash-panel">
                <h2 class="dash-panel-title">{{ __('Top animals') }}</h2>
                @if (empty($topAnimals))
                    <p class="dash-empty">No milk records in this period.</p>
                @else
                    <ol class="dash-ops-rank">
                        @foreach ($topAnimals as $i => $animal)
                            <li>
                                <span class="dash-ops-rank__n">{{ $i + 1 }}</span>
                                <span class="dash-ops-rank__label">{{ $animal['label'] }}</span>
                                <span class="dash-ops-rank__value">{{ $animal['display'] }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
            <div class="dash-panel">
                <h2 class="dash-panel-title">{{ __('Top customers') }}</h2>
                @if (empty($topCustomers))
                    <p class="dash-empty">No customer sales in this period.</p>
                @else
                    <ol class="dash-ops-rank">
                        @foreach ($topCustomers as $i => $customer)
                            <li>
                                <span class="dash-ops-rank__n">{{ $i + 1 }}</span>
                                @if ($customer['route'])
                                    <a href="{{ route($customer['route'], $customer['params']) }}" class="dash-ops-rank__label">{{ $customer['label'] }}</a>
                                @else
                                    <span class="dash-ops-rank__label">{{ $customer['label'] }}</span>
                                @endif
                                <span class="dash-ops-rank__value">{{ $customer['display'] }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
            <div class="dash-panel">
                <h2 class="dash-panel-title">{{ __('Activity') }}</h2>
                @if (empty($activity))
                    <p class="dash-empty">No recent events.</p>
                @else
                    <ul class="dash-home-activity">
                        @foreach ($activity as $item)
                            <li class="dash-home-activity__item">
                                <div class="dash-home-activity__icon">
                                    @include('layouts.partials.dashboard-nav-icon', ['icon' => $item['icon']])
                                </div>
                                <div class="dash-home-activity__body">
                                    @if (! empty($item['route']) && Route::has($item['route']))
                                        <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="dash-home-activity__title">{{ $item['title'] }}</a>
                                    @else
                                        <span class="dash-home-activity__title">{{ $item['title'] }}</span>
                                    @endif
                                    <span class="dash-home-activity__meta">{{ $item['module'] }} · {{ $item['meta'] }}</span>
                                    <time class="dash-home-activity__time" datetime="{{ $item['at']->toIso8601String() }}">{{ $item['at']->diffForHumans() }}</time>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .farm-dash {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .farm-dash .dash-ops-row { margin: 0; }
        .farm-dash .dash-ops-alert-strip { margin: 0; }
        .farm-dash .dash-panel-title { margin-bottom: 1rem; }
        .farm-dash__kpis {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        @media (max-width: 1100px) {
            .farm-dash__kpis {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 640px) {
            .farm-dash__kpis {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const charts = @json($charts);
            const brand = { lime: '#A4D400', teal: '#002B2B', gray: '#9ca3af' };
            const base = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { boxWidth: 12, font: { size: 11 } } } },
            };
            const yFmt = {
                ticks: {
                    font: { size: 10 },
                    callback: (v) => v >= 1e6 ? (v / 1e6).toFixed(1) + 'M' : v >= 1e3 ? (v / 1e3).toFixed(0) + 'k' : v,
                },
            };

            const rev = charts.revenueExpenses || {};
            if (rev.labels?.length) {
                new Chart(document.getElementById('chart-revenue-expenses'), {
                    type: 'bar',
                    data: {
                        labels: rev.labels,
                        datasets: [
                            { label: 'Revenue', data: rev.revenue, backgroundColor: brand.lime, borderRadius: 6, maxBarThickness: 40 },
                            { label: 'Expenses', data: rev.expenses, backgroundColor: brand.teal, borderRadius: 6, maxBarThickness: 40 },
                        ],
                    },
                    options: { ...base, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ...yFmt } } },
                });
            }

            const milk = charts.milkTrend || {};
            if (milk.labels?.length && milk.datasets?.length) {
                new Chart(document.getElementById('chart-milk-trend'), {
                    type: 'line',
                    data: {
                        labels: milk.labels,
                        datasets: milk.datasets.map((ds) => ({
                            label: ds.label,
                            data: ds.data,
                            borderColor: ds.color,
                            backgroundColor: ds.color + '22',
                            tension: 0.35,
                            fill: false,
                        })),
                    },
                    options: { ...base, scales: { x: { grid: { display: false } }, y: { beginAtZero: true } } },
                });
            }

            const sales = charts.salesByType || {};
            if (sales.labels?.length) {
                new Chart(document.getElementById('chart-sales-type'), {
                    type: 'doughnut',
                    data: {
                        labels: sales.labels,
                        datasets: [{ data: sales.values, backgroundColor: [brand.lime, brand.teal, '#4ade80', '#60a5fa', '#fb923c'] }],
                    },
                    options: { ...base, cutout: '58%' },
                });
            }

            const exp = charts.expenseBreakdown || {};
            if (exp.labels?.length) {
                new Chart(document.getElementById('chart-expenses'), {
                    type: 'bar',
                    data: {
                        labels: exp.labels,
                        datasets: [{ label: 'Paid', data: exp.values, backgroundColor: brand.teal, borderRadius: 6 }],
                    },
                    options: {
                        ...base,
                        indexAxis: 'y',
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, ...yFmt }, y: { grid: { display: false } } },
                    },
                });
            }

            const health = charts.animalHealth || {};
            if (health.labels?.length) {
                new Chart(document.getElementById('chart-health'), {
                    type: 'doughnut',
                    data: {
                        labels: health.labels,
                        datasets: [{ data: health.values, backgroundColor: health.colors }],
                    },
                    options: { ...base, cutout: '55%' },
                });
            }

            document.getElementById('filter_period')?.addEventListener('change', function () {
                const custom = this.value === 'custom';
                document.getElementById('dash-custom-dates')?.classList.toggle('dash-ops-field--muted', !custom);
                if (!custom) document.getElementById('dash-filters-form')?.requestSubmit();
            });

            const alertsPanel = document.getElementById('dashboard-alerts');
            const stripLinks = document.querySelectorAll('[data-alert-filter]');
            const filterEmpty = alertsPanel?.querySelector('.dash-ops-alerts-filter-empty');

            function applyAlertFilter(filter) {
                if (!alertsPanel) {
                    return;
                }

                const severity = filter === 'all' ? null : filter;
                let visibleCount = 0;

                alertsPanel.querySelectorAll('[data-alert-severity]').forEach((item) => {
                    const show = !severity || item.dataset.alertSeverity === severity;
                    item.hidden = !show;
                    if (show) {
                        visibleCount++;
                    }
                });

                alertsPanel.querySelectorAll('[data-alert-group]').forEach((group) => {
                    const hasVisible = group.querySelector('[data-alert-severity]:not([hidden])');
                    group.hidden = !hasVisible;
                });

                if (filterEmpty) {
                    filterEmpty.hidden = visibleCount > 0;
                }

                stripLinks.forEach((link) => {
                    link.classList.toggle('is-active', link.dataset.alertFilter === filter);
                });
            }

            stripLinks.forEach((link) => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    const filter = link.dataset.alertFilter || 'all';
                    applyAlertFilter(filter);
                    alertsPanel?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    const hash = filter === 'all' ? 'dashboard-alerts' : 'dashboard-alerts-' + filter;
                    history.replaceState(null, '', '#' + hash);
                });
            });

            const hashFilter = location.hash.match(/^#dashboard-alerts(?:-(critical|warning|info))?$/);
            if (hashFilter) {
                const filter = hashFilter[1] || 'all';
                applyAlertFilter(filter);
                if (location.hash) {
                    alertsPanel?.scrollIntoView({ behavior: 'auto', block: 'start' });
                }
            }
        })();
    </script>
@endpush
