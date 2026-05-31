@extends('layouts.milk-module')

@section('title', 'Milk — Overview')

@section('milk-content')
    @include('modules.partials.header', [
        'title' => 'Milk overview',
        'subtitle' => 'Production from completed milking sessions.',
        'createRoute' => 'milk.sessions.create',
        'createLabel' => '+ Open session',
    ])
    @include('modules.partials.flash')

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Today (liters)</div>
                <div class="dash-stat-value">{{ number_format($stats['today_total'], 2) }} L</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'milk'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Sessions today</div>
                <div class="dash-stat-value accent">{{ number_format($stats['today_sessions']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'chart'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Animals milked today</div>
                <div class="dash-stat-value">{{ number_format($stats['animals_milked_today']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'animal'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">This month (liters)</div>
                <div class="dash-stat-value">{{ number_format($stats['month_total'], 2) }} L</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'milk'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Sessions this month</div>
                <div class="dash-stat-value">{{ number_format($stats['month_sessions']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'movement'])
        </div>
    </div>

    <div class="dash-health-grid">
        <div class="dash-panel">
            <div class="dash-panel-title">Yield per animal (last {{ $charts['meta']['compareDays'] }} days)</div>
            @if (empty($charts['animalsCompare']))
                <p class="dash-empty">No completed sessions yet.</p>
            @else
                <table class="dash-table">
                    <thead><tr><th>Animal</th><th style="text-align:right;">Liters</th></tr></thead>
                    <tbody>
                        @foreach ($charts['animalsCompare'] as $row)
                            <tr>
                                <td><strong>{{ $row['tag'] }}</strong>@if($row['name'])<div style="font-size:0.75rem;color:#808080;">{{ $row['name'] }}</div>@endif</td>
                                <td style="text-align:right;">{{ number_format($row['liters'], 2) }} L</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <div class="dash-panel">
            <div class="dash-panel-title">Yield per herd / group</div>
            @if (empty($charts['herdsCompare']))
                <p class="dash-empty">No herd data yet.</p>
            @else
                <table class="dash-table">
                    <thead><tr><th>Herd</th><th style="text-align:right;">Liters</th></tr></thead>
                    <tbody>
                        @foreach ($charts['herdsCompare'] as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td style="text-align:right;">{{ number_format($row['liters'], 2) }} L</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="dash-health-grid" style="margin-top: 1.25rem;">
        <div class="dash-panel">
            <div class="dash-panel-title">Recent sessions</div>
            @if ($recentSessions->isEmpty())
                <p class="dash-empty">No sessions yet. <a href="{{ route('milk.sessions.create') }}">Open a session</a>.</p>
            @else
                <ul class="dash-health-activity">
                    @foreach ($recentSessions as $session)
                        <li>
                            <div>
                                <a href="{{ route('milk.sessions.edit', $session) }}"><strong>{{ $session->session_code }}</strong></a>
                                <span style="color: #808080;">{{ $session->session_date->format('M j') }} · {{ $session->shiftLabel() }}</span>
                            </div>
                            <span>{{ number_format($session->total_yield_liters, 2) }} L</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="dash-panel">
            <div class="dash-panel-title">Top producers (this month)</div>
            @if ($topProducers->isEmpty())
                <p class="dash-empty">No production data yet.</p>
            @else
                <ul class="dash-health-activity">
                    @foreach ($topProducers as $row)
                        <li>
                            <div><strong>{{ $row->tag_number }}</strong>@if($row->name)<span style="color:#808080;">{{ $row->name }}</span>@endif</div>
                            <span>{{ number_format($row->total, 2) }} L</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    @if ($byShift->isNotEmpty())
        <div class="dash-panel" style="margin-top: 1rem;">
            <div class="dash-panel-title">Today by shift</div>
            <ul class="dash-health-activity" style="padding: 0 1.25rem 1rem;">
                @foreach ($byShift as $shift => $total)
                    <li>
                        <strong>{{ config('modules.milk_session_shift_labels')[$shift] ?? ucfirst($shift) }}</strong>
                        <span>{{ number_format($total, 2) }} L</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
