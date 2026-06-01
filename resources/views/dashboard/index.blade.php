@extends('layouts.dashboard')

@section('title', 'Dashboard')

@php
    $f = $dashboard['filters'] ?? [];
    $strip = $dashboard['alertStrip'] ?? [];
    $fin = $dashboard['financial'] ?? [];
    $live = $dashboard['livestock'] ?? [];
    $charts = $dashboard['charts'] ?? [];
    $strips = $dashboard['moduleStrips'] ?? [];
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
    @include('dashboard.partials.toolbar')

    {{-- Alert strip --}}
    <nav class="dash-ops-alert-strip" aria-label="Alert summary">
        <a href="#dashboard-alerts" class="dash-ops-alert-strip__item" data-alert-filter="all">
            <span class="dash-ops-alert-strip__icon" aria-hidden="true">⚠</span>
            <strong>{{ $strip['total'] ?? 0 }}</strong> alerts
        </a>
        <a href="#dashboard-alerts-critical" class="dash-ops-alert-strip__item dash-ops-alert-strip__item--critical" data-alert-filter="critical">
            <span class="dash-ops-alert-strip__dot" aria-hidden="true"></span>
            <strong>{{ $strip['critical'] ?? 0 }}</strong> critical
        </a>
        <a href="#dashboard-alerts-warning" class="dash-ops-alert-strip__item dash-ops-alert-strip__item--warning" data-alert-filter="warning">
            <span class="dash-ops-alert-strip__dot" aria-hidden="true"></span>
            <strong>{{ $strip['warning'] ?? 0 }}</strong> warnings
        </a>
        @if (($strip['info'] ?? 0) > 0)
            <a href="#dashboard-alerts-info" class="dash-ops-alert-strip__item dash-ops-alert-strip__item--info" data-alert-filter="info">
                <span class="dash-ops-alert-strip__dot" aria-hidden="true"></span>
                <strong>{{ $strip['info'] }}</strong> info
            </a>
        @endif
    </nav>

    {{-- Row 1: Financial --}}
    <section class="dash-ops-row" aria-label="Financial summary">
        <div class="dash-stats dash-ops-stats-4">
            <a href="{{ route('finance.overview', request()->only(['farm_id', 'from', 'to', 'period'])) }}" class="dash-stat-card dash-ops-kpi">
                <div>
                    <div class="dash-stat-label">Revenue</div>
                    <div class="dash-stat-value accent">{{ $money($fin['revenue'] ?? 0) }} <span class="dash-home-stat__suffix">RWF</span></div>
                </div>
                @include('modules.partials.stat-icon', ['icon' => 'sale', 'label' => 'Revenue'])
            </a>
            <a href="{{ route('expenses.overview') }}" class="dash-stat-card dash-ops-kpi">
                <div>
                    <div class="dash-stat-label">Expenses</div>
                    <div class="dash-stat-value">{{ $money($fin['expenses'] ?? 0) }} <span class="dash-home-stat__suffix">RWF</span></div>
                </div>
                @include('modules.partials.stat-icon', ['icon' => 'expense', 'label' => 'Expenses'])
            </a>
            <a href="{{ route('finance.reports.profit_loss', request()->only(['farm_id', 'from', 'to'])) }}" class="dash-stat-card dash-ops-kpi">
                <div>
                    <div class="dash-stat-label">Net profit</div>
                    <div class="dash-stat-value @if(($fin['net_profit'] ?? 0) < 0) alert @else accent @endif">{{ $money($fin['net_profit'] ?? 0) }} <span class="dash-home-stat__suffix">RWF</span></div>
                </div>
                @include('modules.partials.stat-icon', ['icon' => 'finance', 'label' => 'Net profit'])
            </a>
            <a href="{{ route('customers.overview') }}" class="dash-stat-card dash-ops-kpi">
                <div>
                    <div class="dash-stat-label">A/R</div>
                    <div class="dash-stat-value">{{ $money($fin['accounts_receivable'] ?? 0) }} <span class="dash-home-stat__suffix">RWF</span></div>
                </div>
                @include('modules.partials.stat-icon', ['icon' => 'customer', 'label' => 'Accounts receivable'])
            </a>
        </div>
    </section>

    {{-- Row 2: Livestock --}}
    <section class="dash-ops-row" aria-label="Livestock summary">
        <div class="dash-stats dash-ops-stats-4">
            <a href="{{ route('animals.index', $f['farm_id'] ? ['farm_id' => $f['farm_id']] : []) }}" class="dash-stat-card dash-ops-kpi">
                <div>
                    <div class="dash-stat-label">Total animals</div>
                    <div class="dash-stat-value">{{ number_format($live['total_animals'] ?? 0) }}</div>
                </div>
                @include('modules.partials.stat-icon', ['icon' => 'animal', 'label' => 'Total animals'])
            </a>
            <a href="{{ route('farms.index') }}" class="dash-stat-card dash-ops-kpi">
                <div>
                    <div class="dash-stat-label">Active farms</div>
                    <div class="dash-stat-value">{{ number_format($live['active_farms'] ?? 0) }}</div>
                </div>
                @include('modules.partials.stat-icon', ['icon' => 'farm', 'label' => 'Active farms'])
            </a>
            <a href="{{ route('milk.overview') }}" class="dash-stat-card dash-ops-kpi">
                <div>
                    <div class="dash-stat-label">Lactating animals</div>
                    <div class="dash-stat-value accent">{{ number_format($live['lactating'] ?? 0) }}</div>
                </div>
                @include('modules.partials.stat-icon', ['icon' => 'milk', 'label' => 'Lactating animals'])
            </a>
            <a href="{{ route('sales.overview') }}" class="dash-stat-card dash-ops-kpi">
                <div>
                    <div class="dash-stat-label">For sale</div>
                    <div class="dash-stat-value">{{ number_format($live['for_sale'] ?? 0) }}</div>
                </div>
                @include('modules.partials.stat-icon', ['icon' => 'sale', 'label' => 'For sale'])
            </a>
        </div>
    </section>

    {{-- Row 3: Wide charts --}}
    <section class="dash-ops-row dash-ops-charts-2" aria-label="Trend charts">
        <div class="dash-panel">
            <div class="dash-panel-title">Revenue vs expenses</div>
            <div class="dash-home-chart-wrap"><canvas id="chart-revenue-expenses"></canvas></div>
        </div>
        <div class="dash-panel">
            <div class="dash-panel-title">Milk production trend</div>
            <div class="dash-home-chart-wrap"><canvas id="chart-milk-trend"></canvas></div>
        </div>
    </section>

    {{-- Row 4: Medium charts --}}
    <section class="dash-ops-row dash-ops-charts-3" aria-label="Breakdown charts">
        <div class="dash-panel">
            <div class="dash-panel-title">Sales by type</div>
            <div class="dash-ops-chart-sm"><canvas id="chart-sales-type"></canvas></div>
        </div>
        <div class="dash-panel">
            <div class="dash-panel-title">Expense breakdown</div>
            <div class="dash-ops-chart-sm"><canvas id="chart-expenses"></canvas></div>
        </div>
        <div class="dash-panel">
            <div class="dash-panel-title">Animal health status</div>
            <div class="dash-ops-chart-sm"><canvas id="chart-health"></canvas></div>
        </div>
    </section>

    {{-- Row 5: Module KPI strips --}}
    <section class="dash-ops-row" aria-label="Module highlights">
        <div class="dash-ops-strips">
            @foreach ($strips as $strip)
                <a href="{{ route($strip['route']) }}" class="dash-ops-strip">
                    <div class="dash-ops-strip__head">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => $strip['icon']])
                        <span>{{ $strip['label'] }}</span>
                    </div>
                    <div class="dash-ops-strip__metrics">
                        @foreach ($strip['metrics'] as $metric)
                            <div>
                                <span class="dash-ops-strip__label">{{ $metric['label'] }}</span>
                                <span class="dash-ops-strip__value">{{ $metric['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Row 6: Tables --}}
    <section class="dash-ops-row dash-ops-charts-2" aria-label="Sales and alerts">
        <div class="dash-panel">
            <div class="dash-panel-title">Recent sales</div>
            @if (empty($sales))
                <p class="dash-empty">No sales in this period.</p>
            @else
                <div class="dash-table-wrap">
                    <table class="dash-table dash-table--compact">
                        <thead>
                            <tr>
                                <th>Sale #</th>
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
            <div class="dash-panel-title">Pending alerts</div>
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
                                                <span class="dash-ops-alert-line__action">View →</span>
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

    {{-- Row 7: Bottom widgets --}}
    <section class="dash-ops-row dash-ops-charts-3" aria-label="Rankings and activity">
        <div class="dash-panel">
            <div class="dash-panel-title">Top animals (milk yield)</div>
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
            <div class="dash-panel-title">Top customers</div>
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
            <div class="dash-panel-title">Activity feed</div>
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
@endsection

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
