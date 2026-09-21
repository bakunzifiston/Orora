@extends('layouts.admin')

@section('title', __('Dashboard'))

@section('content')
    <div class="farm-dash farms-page health-page admin-dash">
        @include('central.dashboard.partials.toolbar')

        @include('central.partials.platform-kpis')

        <section class="farm-dash__section" aria-label="{{ __('Charts') }}">
            <div class="farm-dash__charts farm-dash__charts--3">
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Milk yield') }}</h2>
                            <p class="farm-panel__desc">
                                {{ ($charts['milkYield']['interval'] ?? 'month') === 'year' ? __('Yearly') : __('Monthly') }}
                                · {{ $filters['label'] }}
                            </p>
                        </div>
                    </header>
                    <div class="farm-panel__chart farm-panel__chart--sm admin-chart-ref__canvas">
                        <canvas id="admin-chart-milk-yield" aria-label="{{ __('Milk yield line chart') }}"></canvas>
                    </div>
                </article>

                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Animals sold') }}</h2>
                            <p class="farm-panel__desc">
                                {{ ($charts['animalsSold']['interval'] ?? 'month') === 'year' ? __('Yearly') : __('Monthly') }}
                                · {{ $filters['label'] }}
                            </p>
                        </div>
                    </header>
                    <div class="farm-panel__chart farm-panel__chart--sm admin-chart-ref__canvas">
                        <canvas id="admin-chart-animals-sold" aria-label="{{ __('Animals sold bar chart') }}"></canvas>
                    </div>
                </article>

                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Livestock groups') }}</h2>
                            <p class="farm-panel__desc">{{ __('Animals by group') }}</p>
                        </div>
                    </header>
                    @if (empty($charts['groups']['values']) || ! collect($charts['groups']['values'])->sum())
                        <p class="dash-empty">{{ __('No livestock groups yet.') }}</p>
                    @else
                        @php
                            $groupColors = ['#A4D400', '#002B2B', '#4ade80', '#60a5fa', '#fb923c', '#a78bfa', '#f472b6', '#fbbf24'];
                            $groupTotal = collect($charts['groups']['values'])->sum();
                        @endphp
                        <div class="admin-donut admin-donut--compact">
                            <div class="admin-donut__chart">
                                <canvas id="admin-chart-groups" aria-label="{{ __('Livestock groups donut chart') }}"></canvas>
                                <div class="admin-donut__center" aria-hidden="true">
                                    <span class="admin-donut__total">{{ number_format($groupTotal) }}</span>
                                    <span class="admin-donut__label">{{ __('animals') }}</span>
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
                </article>
            </div>
        </section>

        <section class="farm-dash__section" aria-label="{{ __('Farms') }}">
            <div class="farm-dash__charts farm-dash__charts--2 admin-map-row">
                @include('central.dashboard.partials.farms-map')

                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Recent farms') }}</h2>
                            <p class="farm-panel__desc">{{ __('Newly registered in this period') }}</p>
                        </div>
                    </header>
                    @if ($recentFarms->isEmpty())
                        <p class="dash-empty">{{ __('No farms yet.') }}</p>
                    @else
                        <div class="dash-table-wrap">
                            <table class="health-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Farm') }}</th>
                                        <th>{{ __('Location') }}</th>
                                        <th>{{ __('Registered') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentFarms as $farm)
                                        <tr>
                                            <td>
                                                <div class="health-table__primary">
                                                    @include('modules.health.partials.table-icon', ['icon' => 'farm', 'tone' => 'default'])
                                                    <a href="{{ route('central.farms.show', $farm) }}" class="health-table__title health-table__title--link">{{ $farm->name }}</a>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="health-table__value">{{ collect([$farm->district, $farm->province])->filter()->implode(', ') ?: '—' }}</span>
                                            </td>
                                            <td>
                                                <span class="health-table__meta">{{ $farm->created_at?->format('M j, Y') }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </article>
            </div>
        </section>

        <section class="farm-dash__section" aria-label="{{ __('Activity and inbox') }}">
            <div class="farm-dash__charts farm-dash__charts--2">
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Recent activity') }}</h2>
                            <p class="farm-panel__desc">{{ __('Latest platform events') }}</p>
                        </div>
                    </header>
                    @if (empty($recentActivity))
                        <p class="dash-empty">{{ __('No activity yet.') }}</p>
                    @else
                        <ul class="farm-activity">
                            @foreach ($recentActivity as $item)
                                <li class="farm-activity__item">
                                    <div class="farm-activity__icon" aria-hidden="true">
                                        @include('layouts.partials.dashboard-nav-icon', ['icon' => $item['icon']])
                                    </div>
                                    <div class="farm-activity__body">
                                        <span class="farm-activity__title">{{ $item['title'] }}</span>
                                        <span class="farm-activity__meta">{{ $item['module'] }} · {{ $item['meta'] }}</span>
                                        <time class="farm-activity__time" datetime="{{ $item['at']->toIso8601String() }}">{{ $item['at']->diffForHumans() }}</time>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </article>

                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Contact inbox') }}</h2>
                            <p class="farm-panel__desc">{{ __('Latest messages') }}</p>
                        </div>
                        <a href="{{ route('central.contact-messages.index') }}" class="admin-inbox-link">
                            @if (($stats['contact_new'] ?? 0) > 0)
                                {{ number_format($stats['contact_new']) }} {{ __('new') }}
                            @else
                                {{ __('View all') }}
                            @endif
                        </a>
                    </header>
                    @if ($recentContacts->isEmpty())
                        <p class="dash-empty">{{ __('No messages yet.') }}</p>
                    @else
                        <div class="dash-table-wrap">
                            <table class="health-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('From') }}</th>
                                        <th>{{ __('Subject') }}</th>
                                        <th>{{ __('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentContacts as $message)
                                        <tr>
                                            <td>
                                                <div class="health-table__primary">
                                                    @include('modules.health.partials.table-icon', ['icon' => 'mail', 'tone' => 'muted'])
                                                    <span class="health-table__title">{{ $message->name }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="health-table__value">{{ Str::limit($message->subject, 48) }}</span>
                                            </td>
                                            <td>
                                                <span class="health-table__meta">{{ $message->created_at?->format('M j, Y') }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </article>
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
            const milkEl = document.getElementById('admin-chart-milk-yield');
            if (milkEl && milkYield.labels?.length) {
                new Chart(milkEl, {
                    type: 'line',
                    data: {
                        labels: milkYield.labels,
                        datasets: [{
                            label: 'Liters',
                            data: milkYield.values,
                            borderColor: brand.lime,
                            backgroundColor: brand.lime,
                            borderWidth: 2,
                            pointRadius: 4,
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
            const soldEl = document.getElementById('admin-chart-animals-sold');
            if (soldEl && animalsSold.labels?.length) {
                new Chart(soldEl, {
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
            const groupsEl = document.getElementById('admin-chart-groups');
            if (groupsEl && groups.labels?.length && groups.values?.some((value) => value > 0)) {
                new Chart(groupsEl, {
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
