@extends('layouts.milk-module')

@section('title', 'Milk — Overview')

@section('milk-content')
    @include('modules.partials.header', [
        'title' => __('Milk overview'),
        'createRoute' => 'milk.sessions.create',
        'createLabel' => '+ '. __('Open session'),
    ])
    @include('modules.partials.flash')

    <div class="milk-overview">
        <form method="GET" action="{{ route('milk.overview') }}" class="dash-ops-toolbar">
            <div class="dash-ops-toolbar__brand">
                <span class="admin-panel-meta">{{ __($periodLabel) }}</span>
            </div>
            <div class="dash-ops-toolbar__controls">
                @include('modules.milk.partials.farm-filter', [
                    'farms' => $farms,
                    'selectedFarm' => $selectedFarm,
                ])
                <div class="dash-ops-field">
                    <label for="milk_filter_period">{{ __('Period') }}</label>
                    <select name="period" id="milk_filter_period" onchange="this.form.submit()">
                        <option value="all" @selected(($period ?? 'all') === 'all')>{{ __('All time') }}</option>
                        <option value="today" @selected(($period ?? '') === 'today')>{{ __('Today') }}</option>
                        <option value="monthly" @selected(($period ?? '') === 'monthly')>{{ __('This month') }}</option>
                        <option value="yearly" @selected(($period ?? '') === 'yearly')>{{ __('This year') }}</option>
                    </select>
                </div>
            </div>
        </form>

        <section class="dash-ops-row" aria-label="Milk summary">
            <div class="dash-stats milk-overview__kpis">
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">{{ __('Yield') }}</div>
                        <div class="dash-stat-value accent">{{ number_format($stats['period_total'], 0) }} <span class="dash-home-stat__suffix">L</span></div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'milk', 'label' => __('Yield')])
                </div>
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">{{ __('Sessions') }}</div>
                        <div class="dash-stat-value">{{ number_format($stats['period_sessions']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'chart', 'label' => __('Sessions')])
                </div>
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">{{ __('Animals') }}</div>
                        <div class="dash-stat-value">{{ number_format($stats['animals_milked']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'animal', 'label' => __('Animals')])
                </div>
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">{{ __('Avg / session') }}</div>
                        <div class="dash-stat-value">{{ number_format($stats['avg_per_session'], 0) }} <span class="dash-home-stat__suffix">L</span></div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'movement', 'label' => __('Avg / session')])
                </div>
                @include('modules.milk.partials.cost-per-litre-card', [
                    'label' => __('Cost / L'),
                    'cost' => $costCurrent,
                    'compareDelta' => ($period ?? 'all') === 'all' ? null : $costCompareDelta,
                    'compareLabel' => match ($period ?? 'all') {
                        'today' => __('yesterday'),
                        'monthly' => __('Last month'),
                        'yearly' => __('Last year'),
                        default => __('previous'),
                    },
                ])
            </div>
        </section>

        @if (($costCurrent['is_combined'] ?? false) && ! empty($costCurrent['per_farm']))
            @include('modules.milk.partials.per-farm-cost-breakdown', [
                'title' => __('Cost by farm'),
                'combined' => $costCurrent,
                'perFarm' => $costCurrent['per_farm'],
            ])
        @endif

        <div class="dash-health-grid milk-overview__charts-2">
            <div class="dash-panel">
                <h2 class="dash-panel-title">{{ __('Cost trend') }}</h2>
                @if (collect($costTrend)->where('has_data', true)->isEmpty())
                    <p class="dash-empty">{{ __('Not enough data yet.') }}</p>
                @else
                    <div class="milk-overview__chart"><canvas id="milk-cost-trend-chart" aria-label="Cost per litre trend"></canvas></div>
                @endif
            </div>
            <div class="dash-panel">
                <h2 class="dash-panel-title">{{ __('Expenses') }}</h2>
                @if (empty($expenseBreakdown['values']))
                    <p class="dash-empty">{{ __('No paid expenses in this period.') }}</p>
                @else
                    <div class="milk-overview__chart milk-overview__chart--pie"><canvas id="milk-expense-pie-chart" aria-label="Expense breakdown pie chart"></canvas></div>
                @endif
            </div>
        </div>

        <div class="dash-health-grid">
            <div class="dash-panel">
                <h2 class="dash-panel-title">{{ __('Yield per animal') }}</h2>
                @if (empty($charts['animalsCompare']))
                    <p class="dash-empty">{{ __('No completed sessions yet.') }}</p>
                @else
                    <table class="dash-table">
                        <thead><tr><th>{{ __('Animal') }}</th><th style="text-align:right;">{{ __('Litres') }}</th></tr></thead>
                        <tbody>
                            @foreach ($charts['animalsCompare'] as $row)
                                <tr>
                                    <td><strong>{{ $row['tag'] }}</strong>@if($row['name'])<div class="milk-overview__sub">{{ $row['name'] }}</div>@endif</td>
                                    <td style="text-align:right;">{{ number_format($row['liters'], 0) }} L</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div class="dash-panel">
                <h2 class="dash-panel-title">{{ __('Yield per herd') }}</h2>
                @if (empty($charts['herdsCompare']))
                    <p class="dash-empty">{{ __('No herd data yet.') }}</p>
                @else
                    <table class="dash-table">
                        <thead><tr><th>{{ __('Herd') }}</th><th style="text-align:right;">{{ __('Litres') }}</th></tr></thead>
                        <tbody>
                            @foreach ($charts['herdsCompare'] as $row)
                                <tr>
                                    <td>{{ $row['name'] }}</td>
                                    <td style="text-align:right;">{{ number_format($row['liters'], 0) }} L</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="dash-health-grid">
            <div class="dash-panel">
                <h2 class="dash-panel-title">{{ __('Recent sessions') }}</h2>
                @if ($recentSessions->isEmpty())
                    <p class="dash-empty">{{ __('No sessions yet.') }} <a href="{{ route('milk.sessions.create') }}">{{ __('Open a session') }}</a>.</p>
                @else
                    <ul class="dash-health-activity">
                        @foreach ($recentSessions as $session)
                            <li>
                                <div>
                                    <a href="{{ route('milk.sessions.edit', $session) }}"><strong>{{ $session->session_code }}</strong></a>
                                    <span class="milk-overview__meta">{{ $session->session_date->format('M j') }} · {{ $session->shiftLabel() }}</span>
                                </div>
                                <span>{{ number_format($session->total_yield_liters, 0) }} L</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="dash-panel">
                <h2 class="dash-panel-title">{{ __('Top producers') }}</h2>
                @if ($topProducers->isEmpty())
                    <p class="dash-empty">{{ __('No production data yet.') }}</p>
                @else
                    <ul class="dash-health-activity">
                        @foreach ($topProducers as $row)
                            <li>
                                <div><strong>{{ $row->tag_number }}</strong>@if($row->name)<span class="milk-overview__meta">{{ $row->name }}</span>@endif</div>
                                <span>{{ number_format($row->total, 0) }} L</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        @if ($byShift->isNotEmpty())
            <div class="dash-panel">
                <h2 class="dash-panel-title">{{ __('By shift') }}</h2>
                <ul class="dash-health-activity milk-overview__shift-list">
                    @foreach ($byShift as $shift => $total)
                        <li>
                            <strong>{{ config('modules.milk_session_shift_labels')[$shift] ?? ucfirst($shift) }}</strong>
                            <span>{{ number_format($total, 0) }} L</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .milk-overview {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .milk-overview .dash-ops-toolbar,
        .milk-overview .dash-ops-row,
        .milk-overview .dash-health-grid,
        .milk-overview .dash-panel { margin: 0; }
        .milk-overview .dash-panel-title { margin-bottom: 1rem; }
        .milk-overview__kpis {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 1rem;
            margin: 0;
        }
        .milk-overview__chart {
            height: 220px;
            position: relative;
        }
        .milk-overview__chart--pie {
            max-width: 280px;
            margin-inline: auto;
        }
        .milk-overview__charts-2 {
            grid-template-columns: 1.2fr 1fr;
        }
        @media (max-width: 900px) {
            .milk-overview__charts-2 {
                grid-template-columns: 1fr;
            }
        }
        .milk-overview__sub,
        .milk-overview__meta {
            font-size: 0.75rem;
            color: #808080;
        }
        .milk-overview__meta { margin-left: 0.35rem; }
        .milk-overview__shift-list {
            padding: 0 1.25rem 1rem;
        }
        .milk-overview .dash-stat-card--cost .dash-cost-details__summary {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            width: 100%;
        }
        @media (max-width: 1100px) {
            .milk-overview__kpis {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 640px) {
            .milk-overview__kpis {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@if (collect($costTrend)->where('has_data', true)->isNotEmpty() || ! empty($expenseBreakdown['values']))
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                const brand = { lime: '#A4D400', teal: '#002B2B' };
                const palette = [brand.lime, brand.teal, '#4ade80', '#60a5fa', '#fb923c', '#a78bfa', '#f472b6'];

                const trend = @json($costTrend);
                const trendEl = document.getElementById('milk-cost-trend-chart');
                if (trendEl && trend.some((r) => r.has_data)) {
                    new Chart(trendEl, {
                        type: 'line',
                        data: {
                            labels: trend.map((r) => r.month),
                            datasets: [{
                                label: 'Cost per litre (RWF)',
                                data: trend.map((r) => r.has_data ? r.cost_per_litre : null),
                                borderColor: brand.teal,
                                backgroundColor: 'rgba(0, 43, 43, 0.08)',
                                tension: 0.35,
                                fill: true,
                                spanGaps: true,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        afterLabel(ctx) {
                                            const litres = trend[ctx.dataIndex]?.total_litres;
                                            return litres != null ? litres.toLocaleString() + ' L produced' : '';
                                        },
                                    },
                                },
                            },
                            scales: {
                                x: { grid: { display: false } },
                                y: { beginAtZero: true, title: { display: true, text: 'RWF / L' } },
                            },
                        },
                    });
                }

                const expense = @json($expenseBreakdown);
                const pieEl = document.getElementById('milk-expense-pie-chart');
                if (pieEl && expense.values?.length) {
                    new Chart(pieEl, {
                        type: 'doughnut',
                        data: {
                            labels: expense.labels,
                            datasets: [{
                                data: expense.values,
                                backgroundColor: expense.labels.map((_, i) => palette[i % palette.length]),
                                borderWidth: 2,
                                borderColor: '#fff',
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '62%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { boxWidth: 10, font: { size: 11 } },
                                },
                                tooltip: {
                                    callbacks: {
                                        label(ctx) {
                                            const value = ctx.parsed || 0;
                                            const total = ctx.dataset.data.reduce((sum, n) => sum + n, 0);
                                            const pct = total > 0 ? Math.round((value / total) * 100) : 0;
                                            return ` ${Number(value).toLocaleString()} RWF (${pct}%)`;
                                        },
                                    },
                                },
                            },
                        },
                    });
                }
            })();
        </script>
    @endpush
@endif
