@extends('layouts.dashboard')

@section('title', __('Farms'))

@section('content')
    @include('modules.partials.header', [
        'title' => __('Farms'),
        'subtitle' => __('Registered farms with Rwanda location and owner details.'),
        'createRoute' => 'farms.create',
        'createLabel' => '+ '. __('Register farm'),
    ])
    @include('modules.partials.flash')

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Total farms') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['total']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'farm'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Active') }}</div>
                <div class="dash-stat-value accent">{{ number_format($stats['active']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'health'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Total area') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['total_hectares'], 1) }} ha</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'movement'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Livestock groups') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['livestock_groups']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'livestock'])
        </div>
    </div>

    @if ($farms->isEmpty())
        <div class="dash-panel dash-entity-empty">
            <div class="dash-entity-empty__icon" aria-hidden="true">
                @include('layouts.partials.dashboard-nav-icon', ['icon' => 'farm'])
            </div>
            <p class="dash-empty">{{ __('No farms registered yet.') }}</p>
            <a href="{{ route('farms.create') }}" class="dash-btn-save">{{ __('Register your first farm') }}</a>
        </div>
    @else
        <div class="dash-entity-grid">
            @foreach ($farms as $farm)
                @include('modules.farms._farm-card', ['farm' => $farm])
            @endforeach
        </div>
        <div class="dash-pagination">{{ $farms->links() }}</div>
    @endif
@endsection
