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

    <div class="dash-health-stats">
        <div class="dash-stat-card">
            <div class="dash-stat-label">Total animals</div>
            <div class="dash-stat-value">{{ number_format($stats['total_animals']) }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Healthy</div>
            <div class="dash-stat-value accent">{{ number_format($stats['healthy']) }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Needs attention</div>
            <div class="dash-stat-value">{{ number_format($stats['needs_attention']) }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Follow-ups (30 days)</div>
            <div class="dash-stat-value">{{ number_format($stats['upcoming_followups']) }}</div>
        </div>
    </div>

    <div class="dash-health-grid">
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

        <div class="dash-panel">
            <div class="dash-panel-title">Records by type</div>
            @if ($recordsByType->isEmpty())
                <p class="dash-empty">No breakdown available yet.</p>
            @else
                <ul class="dash-health-breakdown">
                    @foreach ($recordsByType as $type => $count)
                        <li><span>{{ $type }}</span><strong>{{ $count }}</strong></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
