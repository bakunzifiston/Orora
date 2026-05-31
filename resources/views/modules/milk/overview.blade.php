@extends('layouts.milk-module')

@section('title', 'Milk — Overview')

@section('milk-content')
    @include('modules.partials.header', [
        'title' => 'Milk overview',
        'subtitle' => 'Daily and monthly production from your herd.',
        'createRoute' => 'milk.records.create',
        'createLabel' => '+ Record milk',
    ])
    @include('modules.partials.flash')

    <div class="dash-health-stats">
        <div class="dash-stat-card">
            <div class="dash-stat-label">Today (liters)</div>
            <div class="dash-stat-value">{{ number_format($stats['today_total'], 2) }} L</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Records today</div>
            <div class="dash-stat-value accent">{{ number_format($stats['today_count']) }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Animals milked today</div>
            <div class="dash-stat-value">{{ number_format($stats['animals_milked_today']) }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">This month (liters)</div>
            <div class="dash-stat-value">{{ number_format($stats['month_total'], 2) }} L</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Records this month</div>
            <div class="dash-stat-value">{{ number_format($stats['month_count']) }}</div>
        </div>
    </div>

    <div class="dash-health-grid">
        <div class="dash-panel">
            <div class="dash-panel-title">Yield per animal (last {{ $charts['meta']['compareDays'] }} days)</div>
            @if (empty($charts['animalsCompare']))
                <p class="dash-empty">No milk records yet.</p>
            @else
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Animal</th>
                            <th style="text-align: right;">Liters</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($charts['animalsCompare'] as $row)
                            <tr>
                                <td>
                                    <strong>{{ $row['tag'] }}</strong>
                                    @if (! empty($row['name']))
                                        <div style="font-size: 0.75rem; color: #808080;">{{ $row['name'] }}</div>
                                    @endif
                                </td>
                                <td style="text-align: right;">{{ number_format($row['liters'], 2) }} L</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="dash-panel">
            <div class="dash-panel-title">Yield per herd / group</div>
            @if (empty($charts['herdsCompare']))
                <p class="dash-empty">No herd information yet.</p>
            @else
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Herd / group</th>
                            <th style="text-align: right;">Liters</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($charts['herdsCompare'] as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td style="text-align: right;">{{ number_format($row['liters'], 2) }} L</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="dash-panel" style="margin-top: 1.25rem;">
        <div class="dash-panel-title">Recent milk records</div>
        @if ($recentRecords->isEmpty())
            <p class="dash-empty">No milk records yet. <a href="{{ route('milk.records.create') }}">Record milk</a>.</p>
        @else
            <ul class="dash-health-activity">
                @foreach ($recentRecords as $record)
                    <li>
                        <div>
                            <strong>{{ $record->animal->tag_number }}</strong>
                            @if ($record->animal->name)
                                <span style="color: #808080;">{{ $record->animal->name }}</span>
                            @endif
                            <span style="color: #808080;">{{ $record->recorded_on->format('M j, Y') }}</span>
                        </div>
                        <span>{{ $record->quantity }} {{ $record->unit }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if ($bySession->isNotEmpty())
        <div class="dash-panel" style="margin-top: 1rem;">
            <div class="dash-panel-title">Today by session</div>
            <ul class="dash-health-activity" style="padding: 0 1.25rem 1rem;">
                @foreach ($bySession as $session => $total)
                    <li>
                        <strong>{{ $session ?: 'Unspecified' }}</strong>
                        <span>{{ number_format($total, 2) }} L</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
