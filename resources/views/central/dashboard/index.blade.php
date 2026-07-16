@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="admin-dash">
        @include('central.dashboard.partials.toolbar')

        <section class="dash-ops-row" aria-label="Summary">
            @include('central.partials.platform-kpis')
        </section>

        <section class="dash-ops-row dash-ops-charts-3" aria-label="Charts">
            <div class="dash-panel admin-chart-panel admin-chart-ref">
                <div class="admin-panel-head">
                    <h2 class="dash-panel-title">Milk yield</h2>
                    <span class="admin-panel-meta">{{ ($charts['milkYield']['interval'] ?? 'month') === 'year' ? 'Yearly' : 'Monthly' }} · {{ $filters['label'] }}</span>
                </div>
                <div class="dash-home-chart-wrap admin-chart-ref__canvas"><canvas id="admin-chart-milk-yield" aria-label="Milk yield line chart"></canvas></div>
            </div>
            <div class="dash-panel admin-chart-panel admin-chart-ref">
                <div class="admin-panel-head">
                    <h2 class="dash-panel-title">Animals sold</h2>
                    <span class="admin-panel-meta">{{ ($charts['animalsSold']['interval'] ?? 'month') === 'year' ? 'Yearly' : 'Monthly' }} · {{ $filters['label'] }}</span>
                </div>
                <div class="dash-home-chart-wrap admin-chart-ref__canvas"><canvas id="admin-chart-animals-sold" aria-label="Animals sold bar chart"></canvas></div>
            </div>
            <div class="dash-panel admin-chart-panel admin-chart-panel--groups">
                <div class="admin-panel-head">
                    <h2 class="dash-panel-title">Livestock groups</h2>
                    <span class="admin-panel-meta">Animals</span>
                </div>
                @if (empty($charts['groups']['values']) || ! collect($charts['groups']['values'])->sum())
                    <p class="dash-empty">No livestock groups yet.</p>
                @else
                    @php
                        $groupColors = ['#A4D400', '#002B2B', '#4ade80', '#60a5fa', '#fb923c', '#a78bfa', '#f472b6', '#fbbf24'];
                        $groupTotal = collect($charts['groups']['values'])->sum();
                    @endphp
                    <div class="admin-donut admin-donut--compact">
                        <div class="admin-donut__chart">
                            <canvas id="admin-chart-groups" aria-label="Livestock groups donut chart"></canvas>
                            <div class="admin-donut__center" aria-hidden="true">
                                <span class="admin-donut__total">{{ number_format($groupTotal) }}</span>
                                <span class="admin-donut__label">animals</span>
                            </div>
                        </div>
                        <ul class="admin-donut__legend">
                            @foreach ($charts['groups']['labels'] as $index => $label)
                                <li class="admin-donut__item">
                                    <span class="admin-donut__swatch" style="background: {{ $groupColors[$index % count($groupColors)] }}"></span>
                                    <span class="admin-donut__name">{{ $label }}</span>
                                    <span class="admin-donut__value">{{ number_format($charts['groups']['values'][$index]) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </section>

        <section class="dash-ops-row dash-ops-charts-2 admin-map-row" aria-label="Farm map and recent farms">
            @include('central.dashboard.partials.farms-map')

            <div class="dash-panel">
                <h2 class="dash-panel-title">Recent farms</h2>
                @if ($recentFarms->isEmpty())
                    <p class="dash-empty">No farms yet.</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="dash-table dash-table--compact">
                            <thead>
                                <tr>
                                    <th>Farm</th>
                                    <th>Location</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentFarms as $farm)
                                    <tr>
                                        <td>
                                            <a href="{{ route('central.users.show', $farm) }}" class="admin-farm-row-link">{{ $farm->name }}</a>
                                        </td>
                                        <td>{{ collect([$farm->district, $farm->province])->filter()->implode(', ') ?: '—' }}</td>
                                        <td>{{ $farm->created_at?->format('M j, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>

        <section class="dash-ops-row" aria-label="Recent activity">
            <div class="dash-panel">
                <h2 class="dash-panel-title">Recent activity</h2>
                @if (empty($recentActivity))
                    <p class="dash-empty">No activity yet.</p>
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

        <section class="dash-ops-row" aria-label="Contact inbox">
            <div class="dash-panel">
                <div class="admin-panel-head">
                    <h2 class="dash-panel-title">Contact inbox</h2>
                    <a href="{{ route('central.contact-messages.index') }}" class="admin-inbox-link">
                        @if ($stats['contact_new'] > 0)
                            {{ number_format($stats['contact_new']) }} new
                        @else
                            View all
                        @endif
                    </a>
                </div>
                @if ($recentContacts->isEmpty())
                    <p class="dash-empty">No messages yet.</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="dash-table dash-table--compact">
                            <thead>
                                <tr>
                                    <th>From</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentContacts as $message)
                                    <tr>
                                        <td>{{ $message->name }}</td>
                                        <td>{{ Str::limit($message->subject, 48) }}</td>
                                        <td>{{ $message->created_at?->format('M j, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection

@push('styles')
    @include('central.dashboard.partials.styles')
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @if (! empty($farmMapMarkers))
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            (function () {
                const markers = @json($farmMapMarkers);
                const mapEl = document.getElementById('admin-farms-map');

                if (!mapEl || !markers.length || typeof L === 'undefined') {
                    return;
                }

                const map = L.map(mapEl, { scrollWheelZoom: false }).setView([-1.9403, 29.8739], 8);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '&copy; OpenStreetMap',
                }).addTo(map);

                const bounds = [];
                const escapeHtml = (value) => String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');

                markers.forEach((farm) => {
                    const icon = L.divIcon({
                        className: '',
                        html: '<div class="admin-farm-marker" aria-hidden="true"></div>',
                        iconSize: [12, 12],
                        iconAnchor: [6, 6],
                    });

                    const marker = L.marker([farm.lat, farm.lng], { icon }).addTo(map);
                    bounds.push([farm.lat, farm.lng]);

                    marker.bindPopup(`
                        <a class="admin-farm-popup__title" href="${escapeHtml(farm.url)}">${escapeHtml(farm.name)}</a>
                        <p class="admin-farm-popup__meta">${escapeHtml(farm.location)}</p>
                    `);
                });

                if (bounds.length === 1) {
                    map.setView(bounds[0], 11);
                } else if (bounds.length > 1) {
                    map.fitBounds(bounds, { padding: [40, 40] });
                }
            })();
        </script>
    @endif
    <script>
        (function () {
            const charts = @json($charts);
            const brand = { lime: '#A4D400', teal: '#002B2B' };
            const palette = [brand.lime, brand.teal, '#4ade80', '#60a5fa', '#fb923c', '#a78bfa', '#f472b6', '#fbbf24'];
            const barPalette = [brand.lime, brand.teal, '#7BA300', '#004D4D', brand.lime, brand.teal, '#7BA300', '#004D4D'];
            const base = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
            };
            const refScales = {
                x: {
                    grid: { display: false },
                    border: { color: '#e5e7eb' },
                    ticks: { font: { size: 10 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 12 },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f3f4f6' },
                    border: { display: false },
                    ticks: { font: { size: 10 } },
                },
            };

            const milkYield = charts.milkYield || {};
            if (milkYield.labels?.length) {
                new Chart(document.getElementById('admin-chart-milk-yield'), {
                    type: 'line',
                    data: {
                        labels: milkYield.labels,
                        datasets: [{
                            label: 'Liters',
                            data: milkYield.values,
                            borderColor: brand.lime,
                            backgroundColor: brand.lime,
                            borderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 6,
                            pointBackgroundColor: brand.lime,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 1,
                            tension: 0.35,
                            fill: false,
                        }],
                    },
                    options: {
                        ...base,
                        scales: {
                            ...refScales,
                            y: {
                                ...refScales.y,
                                ticks: {
                                    ...refScales.y.ticks,
                                    callback: (value) => value >= 1000 ? (value / 1000).toFixed(1) + 'k' : value,
                                },
                            },
                        },
                        interaction: { mode: 'index', intersect: false },
                    },
                });
            }

            const animalsSold = charts.animalsSold || {};
            if (animalsSold.labels?.length) {
                new Chart(document.getElementById('admin-chart-animals-sold'), {
                    type: 'bar',
                    data: {
                        labels: animalsSold.labels,
                        datasets: [{
                            label: 'Animals',
                            data: animalsSold.values,
                            backgroundColor: animalsSold.labels.map((_, index) => barPalette[index % barPalette.length]),
                            borderRadius: 4,
                            maxBarThickness: 42,
                            barPercentage: 0.65,
                            categoryPercentage: 0.8,
                        }],
                    },
                    options: {
                        ...base,
                        scales: refScales,
                    },
                });
            }

            const groups = charts.groups || {};
            if (groups.labels?.length && groups.values?.some((value) => value > 0)) {
                new Chart(document.getElementById('admin-chart-groups'), {
                    type: 'doughnut',
                    data: {
                        labels: groups.labels,
                        datasets: [{
                            data: groups.values,
                            backgroundColor: groups.labels.map((_, index) => palette[index % palette.length]),
                            borderWidth: 2,
                            borderColor: '#fff',
                        }],
                    },
                    options: {
                        ...base,
                        cutout: '72%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => ` ${ctx.parsed} animals`,
                                },
                            },
                        },
                    },
                });
            }
        })();

        document.getElementById('admin_filter_period')?.addEventListener('change', function () {
            const custom = this.value === 'custom';
            document.getElementById('admin-dash-custom-dates')?.classList.toggle('dash-ops-field--muted', !custom);
        });
    </script>
@endpush
