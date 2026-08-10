@extends('layouts.dashboard')

@section('title', $livestock->name)

@section('content')
    @php
        $statusClass = $livestock->status === 'active'
            ? 'dash-entity-card__badge--active'
            : 'dash-entity-card__badge--inactive';
    @endphp

    @include('modules.partials.header', [
        'title' => $livestock->name,
        'subtitle' => $livestock->farm->name,
        'backRoute' => 'livestock.index',
    ])
    @include('modules.partials.flash')

    <div style="margin: -0.75rem 0 1.25rem; display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
        <span class="dash-entity-card__badge {{ $statusClass }}">{{ ucfirst($livestock->status) }}</span>
        <a href="{{ route('livestock.edit', $livestock) }}" class="dash-btn-save">{{ __('Edit group') }}</a>
    </div>

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div class="dash-stat-label">{{ __('Head count') }}</div>
            <div class="dash-stat-value">{{ number_format($livestock->head_count) }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">{{ __('Animals registered') }}</div>
            <div class="dash-stat-value accent">{{ number_format($livestock->animals_count) }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">{{ __('Farm') }}</div>
            <div class="dash-stat-value" style="font-size: 1rem;">
                <a href="{{ route('farms.show', $livestock->farm) }}" style="color: inherit; text-decoration: none;">{{ $livestock->farm->name }}</a>
            </div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">{{ __('Breed') }}</div>
            <div class="dash-stat-value" style="font-size: 1rem;">{{ $livestock->breed ?: '—' }}</div>
        </div>
    </div>

    <div class="dash-health-grid">
        <div class="dash-panel">
            <div class="dash-panel-title">{{ __('Herd classification') }}</div>
            <dl class="dash-entity-detail">
                @include('modules.farms._detail-row', ['label' => __('Group name'), 'value' => $livestock->name])
                @include('modules.farms._detail-row', ['label' => __('Herd groups'), 'value' => $livestock->herd_groups_label !== '—' ? $livestock->herd_groups_label : null])
                @include('modules.farms._detail-row', ['label' => __('Livestock types'), 'value' => $livestock->livestock_types_label !== '—' ? $livestock->livestock_types_label : null])
                @include('modules.farms._detail-row', ['label' => __('Production'), 'value' => $livestock->production_purposes_label !== '—' ? $livestock->production_purposes_label : null])
                @include('modules.farms._detail-row', ['label' => __('Status'), 'value' => ucfirst($livestock->status)])
            </dl>
        </div>

        <div class="dash-panel">
            <div class="dash-panel-title">{{ __('Management') }}</div>
            <dl class="dash-entity-detail">
                @include('modules.farms._detail-row', ['label' => __('Farming methods'), 'value' => $livestock->farming_methods_label !== '—' ? $livestock->farming_methods_label : null])
                @include('modules.farms._detail-row', ['label' => __('Feeding methods'), 'value' => $livestock->feeding_methods_label !== '—' ? $livestock->feeding_methods_label : null])
                @include('modules.farms._detail-row', ['label' => __('Breed'), 'value' => $livestock->breed])
            </dl>
        </div>
    </div>

    @if ($livestock->notes)
        <div class="dash-panel" style="margin-top: 1rem;">
            <div class="dash-panel-title">{{ __('Notes') }}</div>
            <p style="margin: 0; font-size: 0.875rem; color: #374151; white-space: pre-wrap;">{{ $livestock->notes }}</p>
        </div>
    @endif
@endsection
