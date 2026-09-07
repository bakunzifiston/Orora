@extends('layouts.dashboard')

@section('title', $flock->name)

@section('content')
    @php
        $typeLabel = config('modules.flock_production_types.'.$flock->production_type, $flock->production_type);
        $sourceLabel = $flock->source
            ? (config('modules.flock_sources.'.$flock->source, $flock->source))
            : null;
        $badge = $flock->isActive() ? 'dash-entity-card__badge--active' : 'dash-entity-card__badge--inactive';
        $logsEggs = in_array($flock->production_type, ['layer', 'pullet', 'breeder'], true);
    @endphp

    @include('modules.partials.header', [
        'title' => $flock->name,
        'subtitle' => $flock->flock_code,
        'backRoute' => 'flocks.index',
    ])
    @include('modules.partials.flash')

    <div style="margin: -0.75rem 0 1.25rem; display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
        <span class="dash-entity-card__badge {{ $badge }}">{{ $flock->lifecycle_status }}</span>
        <a href="{{ route('flocks.edit', $flock) }}" class="dash-btn-save">{{ __('Edit flock') }}</a>
        @if ($logsEggs)
            <a href="{{ route('eggs.collections.create', ['farm_id' => $flock->farm_id, 'flock_id' => $flock->id]) }}" class="dash-btn-cancel">{{ __('Log eggs') }}</a>
        @endif
        <form method="POST" action="{{ route('flocks.destroy', $flock) }}" onsubmit="return confirm(@js(__('Remove this flock? This cannot be undone.')));" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button type="submit" class="dash-btn-cancel" style="color: #b91c1c; border-color: #fecaca;">{{ __('Delete') }}</button>
        </form>
    </div>

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Birds on hand') }}</div>
                <div class="dash-stat-value accent">{{ number_format($flock->current_count) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'livestock'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Placed') }}</div>
                <div class="dash-stat-value">{{ number_format($flock->placed_count) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'box'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Mortality') }}</div>
                <div class="dash-stat-value">{{ $flock->mortalityPercent() }}%</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'health'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Type') }}</div>
                <div class="dash-stat-value" style="font-size: 1rem;">{{ $typeLabel }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'animal'])
        </div>
    </div>

    <div class="dash-health-grid">
        <div class="dash-panel">
            <div class="dash-panel-title">{{ __('Placement') }}</div>
            <dl class="dash-farm-detail">
                @include('modules.farms._detail-row', ['label' => __('Farm'), 'value' => $flock->farm?->name])
                @include('modules.farms._detail-row', ['label' => __('House / group'), 'value' => $flock->livestock?->name])
                @include('modules.farms._detail-row', ['label' => __('Breed'), 'value' => $flock->breed])
                @include('modules.farms._detail-row', ['label' => __('Source'), 'value' => $sourceLabel])
                @include('modules.farms._detail-row', ['label' => __('Placed on'), 'value' => $flock->placed_on?->format('M j, Y')])
                @include('modules.farms._detail-row', ['label' => __('Expected end'), 'value' => $flock->expected_end_on?->format('M j, Y')])
                @include('modules.farms._detail-row', ['label' => __('House / pen'), 'value' => $flock->house_or_pen])
                @include('modules.farms._detail-row', ['label' => __('Males'), 'value' => $flock->male_count !== null ? number_format($flock->male_count) : null])
                @include('modules.farms._detail-row', ['label' => __('Females'), 'value' => $flock->female_count !== null ? number_format($flock->female_count) : null])
            </dl>
            @if (filled($flock->notes))
                <p class="dash-field-hint" style="margin-top: 1rem;">{{ $flock->notes }}</p>
            @endif
        </div>
        <div class="dash-panel">
            <div class="dash-panel-title">{{ __('Count ledger') }}</div>
            @if ($flock->events->isEmpty())
                <p class="dash-empty">{{ __('No events yet.') }}</p>
            @else
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Event') }}</th>
                            <th>{{ __('Qty') }}</th>
                            <th>{{ __('Balance') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($flock->events as $event)
                            <tr>
                                <td>{{ $event->occurred_on->format('M j, Y') }}</td>
                                <td>{{ config('modules.flock_event_types.'.$event->event_type, $event->event_type) }}</td>
                                <td>{{ $event->quantity }}</td>
                                <td>{{ $event->balance_after }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
