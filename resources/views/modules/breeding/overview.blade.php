@extends('layouts.breeding-module')

@section('title', 'Breeding — Overview')

@section('breeding-content')
    @include('modules.partials.header', [
        'title' => 'Breeding overview',
        'subtitle' => 'Track matings, pregnancy checks, and calving.',
        'createRoute' => 'breeding.records.create',
        'createLabel' => '+ Record breeding',
    ])
    @include('modules.partials.flash')

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Active breedings</div>
                <div class="dash-stat-value">{{ $stats['active_breedings'] }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'breeding'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Confirmed pregnant</div>
                <div class="dash-stat-value accent">{{ $stats['confirmed_pregnant'] }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'health'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Due this month</div>
                <div class="dash-stat-value">{{ $stats['due_this_month'] }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'certificate'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Births this month</div>
                <div class="dash-stat-value">{{ $stats['births_this_month'] }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'animal'])
        </div>
        <a href="{{ route('breeding.records', ['pregnancy_check_due' => 1]) }}" class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Pregnancy checks due</div>
                <div class="dash-stat-value @if(($stats['pregnancy_checks_due'] ?? 0) > 0) alert @endif">{{ $stats['pregnancy_checks_due'] ?? 0 }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'health'])
        </a>
    </div>

    <div class="dash-panel" id="pregnancy-check-due" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Pregnancy checks due ({{ config('modules.breeding_pregnancy_check_due_days', 35) }}+ days after breeding)</div>
        @if ($pregnancyChecksDue->isEmpty())
            <p class="dash-empty">No pregnancy checks due right now.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Female</th>
                            <th>Bred on</th>
                            <th>Due on</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pregnancyChecksDue as $record)
                            <tr>
                                <td><a href="{{ route('breeding.records.edit', $record) }}">{{ $record->breeding_code }}</a></td>
                                <td>{{ $record->femaleAnimal->tag_number }}</td>
                                <td>{{ $record->breeding_date->format('M j, Y') }}</td>
                                <td>{{ $record->pregnancy_check_due_on?->format('M j, Y') ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('breeding.checks.create', ['breeding_record_id' => $record->id]) }}" class="dash-btn-save" style="padding: 0.35rem 0.75rem; font-size: 0.8125rem;">Record check</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p style="margin: 0.75rem 0 0; font-size: 0.8125rem;">
                <a href="{{ route('breeding.records', ['pregnancy_check_due' => 1]) }}">View all due breedings →</a>
            </p>
        @endif
    </div>

    <div class="dash-form-grid" style="grid-template-columns: 1fr 1fr; gap: 1.25rem;">
        <div class="dash-panel">
            <div class="dash-panel-title">Upcoming calvings</div>
            @if ($upcomingCalvings->isEmpty())
                <p class="dash-empty">No confirmed pregnancies with expected dates.</p>
            @else
                <div class="dash-table-wrap">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Female</th>
                                <th>Expected</th>
                                <th>Farm</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($upcomingCalvings as $record)
                                <tr>
                                    <td>
                                        <a href="{{ route('breeding.records.edit', $record) }}">{{ $record->femaleAnimal->tag_number }}</a>
                                    </td>
                                    <td>{{ $record->expected_calving_date?->format('M j, Y') ?? '—' }}</td>
                                    <td>{{ $record->farm->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="dash-panel">
            <div class="dash-panel-title">Recent breedings</div>
            @if ($recentBreedings->isEmpty())
                <p class="dash-empty">No breeding records yet.</p>
            @else
                <div class="dash-table-wrap">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Female</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentBreedings as $record)
                                <tr>
                                    <td><a href="{{ route('breeding.records.edit', $record) }}">{{ $record->breeding_code }}</a></td>
                                    <td>{{ $record->femaleAnimal->tag_number }}</td>
                                    <td>{{ $record->breeding_date->format('M j, Y') }}</td>
                                    <td>{{ $record->statusLabel() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
