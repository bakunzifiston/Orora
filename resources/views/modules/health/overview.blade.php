@extends('layouts.health-module')

@section('title', 'Health — Overview')

@section('health-content')
    @include('modules.partials.header', [
        'title' => 'Health overview',
        'subtitle' => 'Summary of herd health across your farms.',
        'createRoute' => 'health.records.create',
        'createRouteParams' => ['section' => 'overview'],
    ])
    @include('modules.partials.flash')

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Total animals</div>
                <div class="dash-stat-value">{{ number_format($stats['total_animals']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'animal'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Healthy</div>
                <div class="dash-stat-value accent">{{ number_format($stats['healthy']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'health'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Needs attention</div>
                <div class="dash-stat-value">{{ number_format($stats['needs_attention']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'shield'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Follow-ups (30 days)</div>
                <div class="dash-stat-value">{{ number_format($stats['upcoming_followups']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'certificate'])
        </div>
    </div>

    <div class="dash-health-charts">
        <div class="dash-panel dash-panel--wide">
            <div class="dash-panel-title">Health records (last {{ $charts['meta']['months'] }} months)</div>
            @if (array_sum($charts['recordsByMonth']['values']) === 0)
                <div class="dash-chart-empty"><p class="dash-empty">No records in this period yet.</p></div>
            @else
                <div class="dash-chart-wrap"><canvas id="health-records-month-chart" aria-label="Health records by month"></canvas></div>
            @endif
        </div>

        <div class="dash-panel">
            <div class="dash-panel-title">Herd health status</div>
            @if (empty($charts['animalsByStatus']['values']))
                <div class="dash-chart-empty"><p class="dash-empty">No animals registered yet.</p></div>
            @else
                <div class="dash-chart-wrap"><canvas id="health-status-chart" aria-label="Animals by health status"></canvas></div>
            @endif
        </div>

        <div class="dash-panel">
            <div class="dash-panel-title">Records by type</div>
            @if (empty($charts['recordsByType']['values']))
                <div class="dash-chart-empty"><p class="dash-empty">No breakdown available yet.</p></div>
            @else
                <div class="dash-chart-wrap"><canvas id="health-records-type-chart" aria-label="Health records by type"></canvas></div>
            @endif
        </div>
    </div>

    <div class="dash-panel">
        <div class="dash-panel-title">Recent health activity</div>
        @if ($recentRecords->isEmpty())
            <p class="dash-empty">No health records yet.</p>
        @else
            <ul class="dash-health-activity">
                @foreach ($recentRecords as $record)
                    <li>
                        <div>
                            <strong>{{ $record->record_type }}</strong> — {{ $record->animal->tag_number }}
                            <span style="color: #808080;">{{ $record->recorded_on->format('M j, Y') }}</span>
                        </div>
                        <span class="dash-badge">{{ $record->health_status }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection

@push('scripts')
    @if (array_sum($charts['recordsByMonth']['values']) > 0 || ! empty($charts['animalsByStatus']['values']) || ! empty($charts['recordsByType']['values']))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                const charts = @json($charts);
                const brand = { lime: '#A4D400', teal: '#002B2B', gray: '#9ca3af' };
                const typePalette = ['#A4D400', '#002B2B', '#4ade80', '#60a5fa', '#fb923c', '#fbbf24', '#f87171', '#94a3b8'];

                const baseOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { boxWidth: 12, font: { size: 11 } } },
                    },
                };

                if (charts.recordsByMonth.values.some(v => v > 0)) {
                    new Chart(document.getElementById('health-records-month-chart'), {
                        type: 'bar',
                        data: {
                            labels: charts.recordsByMonth.labels,
                            datasets: [{
                                label: 'Records',
                                data: charts.recordsByMonth.values,
                                backgroundColor: brand.lime,
                                borderRadius: 6,
                                maxBarThickness: 48,
                            }],
                        },
                        options: {
                            ...baseOptions,
                            plugins: { ...baseOptions.plugins, legend: { display: false } },
                            scales: {
                                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                                y: { beginAtZero: true, ticks: { precision: 0, font: { size: 11 } } },
                            },
                        },
                    });
                }

                if (charts.animalsByStatus.values.length) {
                    new Chart(document.getElementById('health-status-chart'), {
                        type: 'doughnut',
                        data: {
                            labels: charts.animalsByStatus.labels,
                            datasets: [{
                                data: charts.animalsByStatus.values,
                                backgroundColor: charts.animalsByStatus.colors,
                                borderWidth: 0,
                            }],
                        },
                        options: {
                            ...baseOptions,
                            cutout: '62%',
                            plugins: {
                                ...baseOptions.plugins,
                                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 }, padding: 12 } },
                            },
                        },
                    });
                }

                if (charts.recordsByType.values.length) {
                    new Chart(document.getElementById('health-records-type-chart'), {
                        type: 'bar',
                        data: {
                            labels: charts.recordsByType.labels,
                            datasets: [{
                                label: 'Records',
                                data: charts.recordsByType.values,
                                backgroundColor: typePalette.slice(0, charts.recordsByType.labels.length),
                                borderRadius: 6,
                                maxBarThickness: 32,
                            }],
                        },
                        options: {
                            indexAxis: 'y',
                            ...baseOptions,
                            plugins: { ...baseOptions.plugins, legend: { display: false } },
                            scales: {
                                x: { beginAtZero: true, ticks: { precision: 0, font: { size: 11 } } },
                                y: { grid: { display: false }, ticks: { font: { size: 11 } } },
                            },
                        },
                    });
                }
            })();
        </script>
    @endif
@endpush
