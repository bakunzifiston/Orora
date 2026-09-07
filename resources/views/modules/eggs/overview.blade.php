@extends('layouts.eggs-module')

@section('title', 'Eggs')

@section('eggs-content')
    @include('modules.partials.header', [
        'title' => __('Eggs'),
        'subtitle' => __('Daily collections, lay rate, and cracked eggs by flock.'),
        'createRoute' => 'eggs.collections.create',
        'createLabel' => '+ '. __('Record collection'),
    ])
    @include('modules.partials.flash')

    <form method="GET" class="dash-form-grid" style="margin-bottom: 1rem;">
        <div class="dash-form-field">
            <label for="farm_id">{{ __('Farm') }}</label>
            <select name="farm_id" id="farm_id" onchange="this.form.submit()">
                <option value="">{{ __('All poultry farms') }}</option>
                @foreach ($farms as $farm)
                    <option value="{{ $farm->id }}" @selected((string) $farmId === (string) $farm->id)>{{ $farm->name }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="dash-health-stats">
        <div class="dash-stat-card">
            <div class="dash-stat-label">{{ __('Eggs (30 days)') }}</div>
            <div class="dash-stat-value">{{ number_format($stats['eggs']) }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">{{ __('Saleable') }}</div>
            <div class="dash-stat-value accent">{{ number_format($stats['saleable']) }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">{{ __('Lay rate') }}</div>
            <div class="dash-stat-value">{{ $stats['lay_rate'] }}%</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">{{ __('Birds on hand') }}</div>
            <div class="dash-stat-value">{{ number_format($stats['birds']) }}</div>
        </div>
    </div>

    <div class="dash-panel" style="margin-top: 1.25rem;">
        <div class="dash-panel-title">{{ __('Recent collections') }}</div>
        @if ($recent->isEmpty())
            <p class="dash-empty">{{ __('No egg collections yet.') }}</p>
        @else
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Flock') }}</th>
                        <th>{{ __('Eggs') }}</th>
                        <th>{{ __('Cracked') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recent as $row)
                        <tr>
                            <td>{{ $row->collected_on->format('M j, Y') }}</td>
                            <td>{{ $row->flock?->name }}</td>
                            <td>{{ number_format($row->eggs_count) }}</td>
                            <td>{{ number_format($row->cracked_count) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
