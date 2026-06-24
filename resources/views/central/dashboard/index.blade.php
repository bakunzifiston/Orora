@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @include('central.dashboard.partials.toolbar')

    <section class="dash-ops-row" aria-label="Farmer and farm summary">
        @include('central.partials.platform-kpis')
    </section>

    <section class="dash-ops-row dash-ops-charts-2" aria-label="Platform charts">
        <div class="dash-panel">
            <div class="dash-panel-title">Milk sold · {{ $filters['label'] }}</div>
            <div class="dash-home-chart-wrap"><canvas id="admin-chart-milk-sold" aria-label="Milk sold bar chart"></canvas></div>
        </div>
        <div class="dash-panel">
            <div class="dash-panel-title">Animals by livestock group</div>
            <div class="dash-home-chart-wrap"><canvas id="admin-chart-pie" aria-label="Animals by livestock group pie chart"></canvas></div>
        </div>
    </section>

    <section class="dash-ops-row dash-ops-charts-2" aria-label="Recent farms and activity">
        <div class="dash-panel">
            <h2 class="dash-panel-title">Recent farms</h2>
            @if ($recentFarms->isEmpty())
                <p class="dash-empty">No farms registered yet.</p>
            @else
                <div class="dash-table-wrap">
                    <table class="dash-table dash-table--compact">
                        <thead>
                            <tr>
                                <th>Farm</th>
                                <th>Workspace</th>
                                <th>Location</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentFarms as $farm)
                                <tr>
                                    <td>
                                        <strong>{{ $farm->name }}</strong>
                                        @if ($farm->status)
                                            <div style="color: var(--orora-gray); font-size: 0.75rem;">{{ ucfirst($farm->status) }}</div>
                                        @endif
                                    </td>
                                    <td><code>{{ $farm->tenant_id }}</code></td>
                                    <td>{{ collect([$farm->district, $farm->province])->filter()->implode(', ') ?: '—' }}</td>
                                    <td>{{ $farm->created_at?->format('M j, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="dash-panel">
            <h2 class="dash-panel-title">Recent activity</h2>
            @if (empty($recentActivity))
                <p class="dash-empty">No recent platform events.</p>
            @else
                <ul class="dash-home-activity">
                    @foreach ($recentActivity as $item)
                        <li class="dash-home-activity__item">
                            <div class="dash-home-activity__icon">
                                @include('layouts.partials.dashboard-nav-icon', ['icon' => $item['icon']])
                            </div>
                            <div class="dash-home-activity__body">
                                <span class="dash-home-activity__title">{{ $item['title'] }}</span>
                                <span class="dash-home-activity__meta">{{ $item['module'] }} · {{ $item['meta'] }}</span>
                                <time class="dash-home-activity__time" datetime="{{ $item['at']->toIso8601String() }}">{{ $item['at']->diffForHumans() }}</time>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    @if ($livestockGroups->isNotEmpty())
        <section class="dash-ops-row" aria-label="Animals per livestock group">
            <div class="dash-panel">
                <h2 class="dash-panel-title">Animals in each group</h2>
                <p class="dash-field-hint" style="margin: -0.5rem 0 1rem;">
                    Groups added in period; animals registered in {{ $filters['label'] }}.
                </p>
                <div class="dash-table-wrap">
                    <table class="dash-table dash-table--compact">
                        <thead>
                            <tr>
                                <th>Group</th>
                                <th>Farm</th>
                                <th>Workspace</th>
                                <th>Head count</th>
                                <th>Animals registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($livestockGroups as $group)
                                <tr>
                                    <td><strong>{{ $group->name }}</strong></td>
                                    <td>{{ $group->farm?->name ?? '—' }}</td>
                                    <td><code>{{ $group->farm?->tenant_id ?? $group->tenant_id }}</code></td>
                                    <td>{{ number_format($group->head_count) }}</td>
                                    <td>{{ number_format($group->animals_count) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">Total</th>
                                <th>{{ number_format($stats['head_count']) }}</th>
                                <th>{{ number_format($stats['animals']) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </section>
    @endif

    <section class="dash-ops-row" aria-label="Contact inbox">
        <div class="dash-panel">
            <div class="dash-page-header" style="margin-bottom: 1rem;">
                <h2 class="dash-panel-title" style="margin: 0;">Contact inbox</h2>
                <a href="{{ route('central.contact-messages.index') }}" class="dash-back-link">
                    @if ($stats['contact_new'] > 0)
                        {{ number_format($stats['contact_new']) }} new →
                    @else
                        Open inbox →
                    @endif
                </a>
            </div>
            @if ($recentContacts->isEmpty())
                <p class="dash-empty">No contact messages yet.</p>
            @else
                <div class="dash-table-wrap">
                    <table class="dash-table dash-table--compact">
                        <thead>
                            <tr>
                                <th>From</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentContacts as $message)
                                <tr>
                                    <td>{{ $message->name }}</td>
                                    <td>{{ Str::limit($message->subject, 40) }}</td>
                                    <td><span class="dash-badge-green">{{ ucfirst($message->status) }}</span></td>
                                    <td>{{ $message->created_at?->format('M j, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
            const palette = [brand.lime, brand.teal, '#4ade80', '#60a5fa', '#fb923c', '#a78bfa', '#f472b6', '#fbbf24'];
            const base = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { boxWidth: 12, font: { size: 11 } } } },
            };

            const milkSold = charts.milkSold || {};
            if (milkSold.labels?.length) {
                new Chart(document.getElementById('admin-chart-milk-sold'), {
                    type: 'bar',
                    data: {
                        labels: milkSold.labels,
                        datasets: [{
                            label: 'Liters sold',
                            data: milkSold.values,
                            backgroundColor: brand.teal,
                            borderRadius: 6,
                            maxBarThickness: 48,
                        }],
                    },
                    options: {
                        ...base,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    font: { size: 10 },
                                    callback: (value) => value >= 1000 ? (value / 1000).toFixed(1) + 'k L' : value + ' L',
                                },
                            },
                        },
                    },
                });
            }

            const pie = charts.pie || {};
            if (pie.labels?.length && pie.values?.some((value) => value > 0)) {
                new Chart(document.getElementById('admin-chart-pie'), {
                    type: 'pie',
                    data: {
                        labels: pie.labels,
                        datasets: [{
                            data: pie.values,
                            backgroundColor: pie.labels.map((_, index) => palette[index % palette.length]),
                            borderWidth: 2,
                            borderColor: '#fff',
                        }],
                    },
                    options: base,
                });
            }
        })();

        document.getElementById('admin_filter_period')?.addEventListener('change', function () {
            const custom = this.value === 'custom';
            document.getElementById('admin-dash-custom-dates')?.classList.toggle('dash-ops-field--muted', !custom);
        });
    </script>
@endpush
