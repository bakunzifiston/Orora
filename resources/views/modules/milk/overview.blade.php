@extends('layouts.milk-module')

@section('title', __('Milk — Overview'))

@section('milk-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Milk'),
            'createRoute' => 'milk.sessions.create',
            'createLabel' => '+ '. __('Open session'),
        ])
        @include('modules.partials.flash')

        <form method="GET" action="{{ route('milk.overview') }}" class="dash-ops-toolbar farms-page__toolbar">
            <div class="dash-ops-toolbar__brand">
                <span class="farm-dash__toolbar-meta">{{ __($periodLabel) }}</span>
            </div>
            <div class="dash-ops-toolbar__controls farms-page__filters">
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

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <div class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'milk'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Yield') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['period_total'], 0) }}
                            <span class="farm-kpi__unit">L</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('milk.sessions') }}" class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'chart'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Sessions') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['period_sessions']) }}</div>
                    </div>
                </a>
                <div class="farm-kpi farm-kpi--health">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Animals') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['animals_milked']) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'movement'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Avg / session') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['avg_per_session'], 0) }}
                            <span class="farm-kpi__unit">L</span>
                        </div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--expense">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'expense'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Cost / L') }}</div>
                        <div class="farm-kpi__value">
                            @if ($costCurrent['has_data'] ?? false)
                                {{ number_format($costCurrent['cost_per_litre'], 0) }}
                                <span class="farm-kpi__unit">{{ $costCurrent['currency'] ?? 'RWF' }}</span>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if (($costCurrent['is_combined'] ?? false) && ! empty($costCurrent['per_farm']))
            @include('modules.milk.partials.per-farm-cost-breakdown', [
                'title' => __('Cost by farm'),
                'combined' => $costCurrent,
                'perFarm' => $costCurrent['per_farm'],
            ])
        @endif

        <section class="farm-dash__section" aria-label="{{ __('Milk charts') }}">
            <div class="farm-dash__charts farm-dash__charts--2">
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Cost trend') }}</h2>
                        </div>
                    </header>
                    @if (collect($costTrend)->where('has_data', true)->isEmpty())
                        <p class="dash-empty">{{ __('Not enough data yet.') }}</p>
                    @else
                        <div class="farm-panel__chart farm-panel__chart--sm">
                            <canvas id="milk-cost-trend-chart" aria-label="{{ __('Cost per litre trend') }}"></canvas>
                        </div>
                    @endif
                </article>
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Expenses') }}</h2>
                        </div>
                    </header>
                    @if (empty($expenseBreakdown['values']))
                        <p class="dash-empty">{{ __('No paid expenses in this period.') }}</p>
                    @else
                        <div class="farm-panel__chart farm-panel__chart--sm health-page__chart--donut">
                            <canvas id="milk-expense-pie-chart" aria-label="{{ __('Expense breakdown') }}"></canvas>
                        </div>
                    @endif
                </article>
            </div>
        </section>

        <div class="farm-dash__charts farm-dash__charts--2">
            <section class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Yield per animal') }}</h2>
                    </div>
                </header>
                @if (empty($charts['animalsCompare']))
                    <p class="dash-empty">{{ __('No completed sessions yet.') }}</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="health-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Animal') }}</th>
                                    <th>{{ __('Litres') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($charts['animalsCompare'] as $row)
                                    <tr>
                                        <td>
                                            <div class="health-table__primary">
                                                @include('modules.health.partials.table-icon', ['icon' => 'animal', 'tone' => 'default'])
                                                <div class="health-table__stack">
                                                    <span class="health-table__title">{{ $row['tag'] }}</span>
                                                    @if ($row['name'])
                                                        <span class="health-table__meta">{{ $row['name'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="health-table__value">{{ number_format($row['liters'], 0) }} L</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
            <section class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Yield per herd') }}</h2>
                    </div>
                </header>
                @if (empty($charts['herdsCompare']))
                    <p class="dash-empty">{{ __('No herd data yet.') }}</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="health-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Herd') }}</th>
                                    <th>{{ __('Litres') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($charts['herdsCompare'] as $row)
                                    <tr>
                                        <td>
                                            <div class="health-table__primary">
                                                @include('modules.health.partials.table-icon', ['icon' => 'livestock', 'tone' => 'default'])
                                                <div class="health-table__stack">
                                                    <span class="health-table__title">{{ $row['name'] }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="health-table__value">{{ number_format($row['liters'], 0) }} L</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        <div class="farm-dash__charts farm-dash__charts--2">
            <section class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Recent sessions') }}</h2>
                    </div>
                </header>
                @if ($recentSessions->isEmpty())
                    <p class="dash-empty">{{ __('No sessions yet.') }}</p>
                @else
                    <ul class="farm-activity">
                        @foreach ($recentSessions as $session)
                            <li class="farm-activity__item farm-activity__item--plain farm-activity__item--split">
                                <div class="farm-activity__body">
                                    <a href="{{ route('milk.sessions.edit', $session) }}" class="farm-activity__title">{{ $session->session_code }}</a>
                                    <span class="farm-activity__meta">{{ $session->session_date->format('M j') }} · {{ $session->shiftLabel() }}</span>
                                </div>
                                <span class="farm-activity__count">{{ number_format($session->total_yield_liters, 0) }} L</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
            <section class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Top producers') }}</h2>
                    </div>
                </header>
                @if ($topProducers->isEmpty())
                    <p class="dash-empty">{{ __('No production data yet.') }}</p>
                @else
                    <ul class="farm-activity">
                        @foreach ($topProducers as $row)
                            <li class="farm-activity__item farm-activity__item--plain farm-activity__item--split">
                                <div class="farm-activity__body">
                                    <span class="farm-activity__title">{{ $row->tag_number }}</span>
                                    @if ($row->name)
                                        <span class="farm-activity__meta">{{ $row->name }}</span>
                                    @endif
                                </div>
                                <span class="farm-activity__count">{{ number_format($row->total, 0) }} L</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        @if ($byShift->isNotEmpty())
            <section class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('By shift') }}</h2>
                    </div>
                </header>
                <ul class="farm-activity">
                    @foreach ($byShift as $shift => $total)
                        <li class="farm-activity__item farm-activity__item--plain farm-activity__item--split">
                            <div class="farm-activity__body">
                                <span class="farm-activity__title">{{ config('modules.milk_session_shift_labels')[$shift] ?? ucfirst($shift) }}</span>
                            </div>
                            <span class="farm-activity__count">{{ number_format($total, 0) }} L</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
@endsection

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
