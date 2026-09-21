@extends('layouts.health-module')

@section('title', __('Health — Overview'))

@section('health-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Health'),
            'createRoute' => 'health.records.create',
            'createRouteParams' => ['section' => 'overview'],
            'createLabel' => '+ '. __('Log health record'),
        ])
        @include('modules.partials.flash')

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <a href="{{ route('health.vaccinations') }}" class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'shield'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Vaccinations') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['vaccinations']) }}</div>
                    </div>
                </a>
                <a href="{{ route('health.treatments') }}" class="farm-kpi farm-kpi--health">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Treatments') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['treatments']) }}</div>
                    </div>
                </a>
                <a href="{{ route('health.disease') }}" class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'certificate'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Disease') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['disease']) }}</div>
                    </div>
                </a>
                <a href="{{ route('health.vet-visits') }}" class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'employee'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Vet visits') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['vet_visits']) }}</div>
                    </div>
                </a>
                <a href="{{ route('health.mortality') }}" class="farm-kpi farm-kpi--expense">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Mortality') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['mortality']) }}</div>
                    </div>
                </a>
                <div class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Healthy') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['healthy']) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--sales">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'shield'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Needs attention') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['needs_attention']) }}</div>
                    </div>
                </div>
                <a href="{{ route('health.timeline') }}" class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'chart'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Follow-ups') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['followups']) }}</div>
                    </div>
                </a>
            </div>
        </section>

        <section class="farm-dash__section" aria-label="{{ __('Health charts') }}">
            <div class="farm-dash__charts farm-dash__charts--3">
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Health activity') }}</h2>
                            <p class="farm-panel__desc">
                                {{ $charts['meta']['periodLabel'] }}
                                @if ($charts['meta']['recordsTotal'] > 0)
                                    · {{ number_format($charts['meta']['recordsTotal']) }} {{ __('records') }}
                                @endif
                            </p>
                        </div>
                    </header>
                    @if ($charts['meta']['recordsTotal'] === 0)
                        <p class="dash-empty">{{ __('No health records in this period yet.') }}</p>
                    @else
                        <div class="farm-panel__chart farm-panel__chart--sm">
                            <canvas id="health-records-month-chart" aria-label="{{ __('Health records by month') }}"></canvas>
                        </div>
                    @endif
                </article>

                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Herd health') }}</h2>
                            <p class="farm-panel__desc">
                                @if ($charts['meta']['animalsTotal'] > 0)
                                    {{ number_format($charts['meta']['animalsTotal']) }} {{ __('animals') }}
                                @else
                                    {{ __('Current status') }}
                                @endif
                            </p>
                        </div>
                    </header>
                    @if ($charts['meta']['animalsTotal'] === 0)
                        <p class="dash-empty">{{ __('No animals registered yet.') }}</p>
                    @else
                        <div class="farm-panel__chart farm-panel__chart--sm health-page__chart--donut">
                            <canvas id="health-status-chart" aria-label="{{ __('Animals by health status') }}"></canvas>
                        </div>
                    @endif
                </article>

                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('By type') }}</h2>
                            <p class="farm-panel__desc">
                                @if ($charts['meta']['typesTotal'] > 0)
                                    {{ number_format($charts['meta']['typesTotal']) }} {{ __('total') }}
                                @else
                                    {{ __('Top categories') }}
                                @endif
                            </p>
                        </div>
                    </header>
                    @if ($charts['meta']['typesTotal'] === 0)
                        <p class="dash-empty">{{ __('No record types to show yet.') }}</p>
                    @else
                        <div class="farm-panel__chart farm-panel__chart--sm">
                            <canvas id="health-records-type-chart" aria-label="{{ __('Health records by type') }}"></canvas>
                        </div>
                    @endif
                </article>
            </div>
        </section>

        <section class="farm-panel">
            <header class="farm-panel__head">
                <div>
                    <h2 class="farm-panel__title">{{ __('Recent activity') }}</h2>
                    @if ($recentRecords->isNotEmpty())
                        <p class="farm-panel__desc">{{ $recentRecords->count() }} {{ __('records') }}</p>
                    @endif
                </div>
            </header>
            @if ($recentRecords->isEmpty())
                <p class="dash-empty">{{ __('No health records yet.') }}</p>
            @else
                <ul class="farm-activity">
                    @foreach ($recentRecords as $record)
                        <li class="farm-activity__item farm-activity__item--plain farm-activity__item--split">
                            <div class="farm-activity__body">
                                <span class="farm-activity__title">{{ $record->record_type }}</span>
                                <span class="farm-activity__meta">{{ $record->stockLabel() }} · {{ $record->recorded_on->format('M j, Y') }}</span>
                            </div>
                            <span class="employees-table__pill employees-table__pill--muted">{{ $record->health_status }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        @if ($charts['meta']['recordsTotal'] > 0 || $charts['meta']['animalsTotal'] > 0 || $charts['meta']['typesTotal'] > 0)
            <script type="application/json" id="health-overview-chart-data">@json($charts)</script>
        @endif
    </div>
@endsection

@push('scripts')
    @if ($charts['meta']['recordsTotal'] > 0 || $charts['meta']['animalsTotal'] > 0 || $charts['meta']['typesTotal'] > 0)
        @vite(['resources/js/health-overview-charts.js'])
    @endif
@endpush
