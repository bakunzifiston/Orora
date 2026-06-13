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

    <div class="dash-health-stats dash-health-overview__stats">
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

    <section class="dash-health-overview" aria-label="Health charts">
        <div class="dash-panel dash-health-overview__trend">
            <div class="dash-health-chart-head">
                <div>
                    <div class="dash-panel-title">Health activity</div>
                    <p class="dash-health-chart-subtitle">{{ $charts['meta']['periodLabel'] }}</p>
                </div>
                @if ($charts['meta']['recordsTotal'] > 0)
                    <span class="dash-health-chart-kpi">{{ number_format($charts['meta']['recordsTotal']) }} records</span>
                @endif
            </div>
            @if ($charts['meta']['recordsTotal'] === 0)
                <p class="dash-empty dash-health-chart-empty">No health records in this period yet.</p>
            @else
                <div class="dash-health-chart-canvas dash-health-chart-canvas--trend">
                    <canvas id="health-records-month-chart" aria-label="Health records by month"></canvas>
                </div>
            @endif
        </div>

        <div class="dash-health-overview__split">
            <div class="dash-panel">
                <div class="dash-health-chart-head">
                    <div>
                        <div class="dash-panel-title">Herd health status</div>
                        <p class="dash-health-chart-subtitle">Current animal breakdown</p>
                    </div>
                    @if ($charts['meta']['animalsTotal'] > 0)
                        <span class="dash-health-chart-kpi">{{ number_format($charts['meta']['animalsTotal']) }} animals</span>
                    @endif
                </div>
                @if ($charts['meta']['animalsTotal'] === 0)
                    <p class="dash-empty dash-health-chart-empty">No animals registered yet.</p>
                @else
                    <div class="dash-health-chart-canvas dash-health-chart-canvas--donut">
                        <canvas id="health-status-chart" aria-label="Animals by health status"></canvas>
                    </div>
                @endif
            </div>

            <div class="dash-panel">
                <div class="dash-health-chart-head">
                    <div>
                        <div class="dash-panel-title">Records by type</div>
                        <p class="dash-health-chart-subtitle">Top event categories</p>
                    </div>
                    @if ($charts['meta']['typesTotal'] > 0)
                        <span class="dash-health-chart-kpi">{{ number_format($charts['meta']['typesTotal']) }} total</span>
                    @endif
                </div>
                @if ($charts['meta']['typesTotal'] === 0)
                    <p class="dash-empty dash-health-chart-empty">No record types to show yet.</p>
                @else
                    <div class="dash-health-chart-canvas dash-health-chart-canvas--bars">
                        <canvas id="health-records-type-chart" aria-label="Health records by type"></canvas>
                    </div>
                @endif
            </div>
        </div>
    </section>

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
                            <span class="dash-health-activity__date">{{ $record->recorded_on->format('M j, Y') }}</span>
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
