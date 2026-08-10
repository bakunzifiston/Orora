@extends('layouts.health-module')

@section('title', __('Health — Overview'))

@section('health-content')
    @include('modules.partials.header', [
        'title' => __('Health overview'),
        'createRoute' => 'health.records.create',
        'createRouteParams' => ['section' => 'overview'],
        'createLabel' => '+ '. __('Log health record'),
    ])
    @include('modules.partials.flash')

    <div class="dash-stats health-overview__kpis" aria-label="{{ __('Health sections') }}">
        <a href="{{ route('health.vaccinations') }}" class="dash-stat-card dash-ops-kpi">
            <div>
                <div class="dash-stat-label">{{ __('Vaccinations') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['vaccinations']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'shield'])
        </a>
        <a href="{{ route('health.treatments') }}" class="dash-stat-card dash-ops-kpi">
            <div>
                <div class="dash-stat-label">{{ __('Treatments') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['treatments']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'health'])
        </a>
        <a href="{{ route('health.disease') }}" class="dash-stat-card dash-ops-kpi">
            <div>
                <div class="dash-stat-label">{{ __('Disease') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['disease']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'certificate'])
        </a>
        <a href="{{ route('health.vet-visits') }}" class="dash-stat-card dash-ops-kpi">
            <div>
                <div class="dash-stat-label">{{ __('Vet visits') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['vet_visits']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'employee'])
        </a>
        <a href="{{ route('health.mortality') }}" class="dash-stat-card dash-ops-kpi">
            <div>
                <div class="dash-stat-label">{{ __('Mortality') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['mortality']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'animal'])
        </a>
    </div>

    <div class="dash-stats health-overview__status" aria-label="{{ __('Herd status') }}">
        <div class="dash-stat-card dash-ops-kpi">
            <div>
                <div class="dash-stat-label">{{ __('Healthy') }}</div>
                <div class="dash-stat-value accent">{{ number_format($stats['healthy']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'health'])
        </div>
        <div class="dash-stat-card dash-ops-kpi">
            <div>
                <div class="dash-stat-label">{{ __('Needs attention') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['needs_attention']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'shield'])
        </div>
        <a href="{{ route('health.timeline') }}" class="dash-stat-card dash-ops-kpi">
            <div>
                <div class="dash-stat-label">{{ __('Follow-ups') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['followups']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'chart'])
        </a>
    </div>

    <section class="dash-ops-charts-3 health-overview__charts" aria-label="{{ __('Health charts') }}">
        <div class="dash-panel">
            <div class="health-overview__head">
                <h2 class="dash-panel-title">{{ __('Health activity') }}</h2>
                <span class="health-overview__meta">
                    {{ $charts['meta']['periodLabel'] }}
                    @if ($charts['meta']['recordsTotal'] > 0)
                        · {{ number_format($charts['meta']['recordsTotal']) }} {{ __('records') }}
                    @endif
                </span>
            </div>
            @if ($charts['meta']['recordsTotal'] === 0)
                <p class="dash-empty">{{ __('No health records in this period yet.') }}</p>
            @else
                <div class="health-overview__chart"><canvas id="health-records-month-chart" aria-label="{{ __('Health records by month') }}"></canvas></div>
            @endif
        </div>

        <div class="dash-panel">
            <div class="health-overview__head">
                <h2 class="dash-panel-title">{{ __('Herd health') }}</h2>
                <span class="health-overview__meta">
                    @if ($charts['meta']['animalsTotal'] > 0)
                        {{ number_format($charts['meta']['animalsTotal']) }} {{ __('animals') }}
                    @else
                        {{ __('Current status') }}
                    @endif
                </span>
            </div>
            @if ($charts['meta']['animalsTotal'] === 0)
                <p class="dash-empty">{{ __('No animals registered yet.') }}</p>
            @else
                <div class="health-overview__chart health-overview__chart--donut"><canvas id="health-status-chart" aria-label="{{ __('Animals by health status') }}"></canvas></div>
            @endif
        </div>

        <div class="dash-panel">
            <div class="health-overview__head">
                <h2 class="dash-panel-title">{{ __('By type') }}</h2>
                <span class="health-overview__meta">
                    @if ($charts['meta']['typesTotal'] > 0)
                        {{ number_format($charts['meta']['typesTotal']) }} {{ __('total') }}
                    @else
                        {{ __('Top categories') }}
                    @endif
                </span>
            </div>
            @if ($charts['meta']['typesTotal'] === 0)
                <p class="dash-empty">{{ __('No record types to show yet.') }}</p>
            @else
                <div class="health-overview__chart"><canvas id="health-records-type-chart" aria-label="{{ __('Health records by type') }}"></canvas></div>
            @endif
        </div>
    </section>

    <div class="dash-panel">
        <div class="health-overview__head">
            <h2 class="dash-panel-title">{{ __('Recent activity') }}</h2>
            @if ($recentRecords->isNotEmpty())
                <span class="health-overview__meta">{{ $recentRecords->count() }} {{ __('records') }}</span>
            @endif
        </div>
        @if ($recentRecords->isEmpty())
            <p class="dash-empty">{{ __('No health records yet.') }}</p>
        @else
            <ul class="dash-health-activity">
                @foreach ($recentRecords as $record)
                    <li>
                        <div>
                            <strong>{{ $record->record_type }}</strong>
                            <span class="dash-health-activity__meta">{{ $record->animal->tag_number }} · {{ $record->recorded_on->format('M j, Y') }}</span>
                        </div>
                        <span class="dash-badge">{{ $record->health_status }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if ($charts['meta']['recordsTotal'] > 0 || $charts['meta']['animalsTotal'] > 0 || $charts['meta']['typesTotal'] > 0)
        <script type="application/json" id="health-overview-chart-data">@json($charts)</script>
    @endif
@endsection

@push('scripts')
    @if ($charts['meta']['recordsTotal'] > 0 || $charts['meta']['animalsTotal'] > 0 || $charts['meta']['typesTotal'] > 0)
        @vite(['resources/js/health-overview-charts.js'])
    @endif
@endpush
