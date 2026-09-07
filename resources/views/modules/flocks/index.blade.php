@extends('layouts.dashboard')

@section('title', __('Flocks'))

@section('content')
    @include('modules.partials.header', [
        'title' => __('Flocks'),
        'subtitle' => __('Poultry batches placed by farm, with current bird counts.'),
        'createRoute' => 'flocks.create',
        'createLabel' => '+ '. __('Place flock'),
    ])
    @include('modules.partials.flash')

    @component('modules.partials.index-toolbar')
        @slot('stats')
            <div class="dash-health-stats">
                <div class="dash-stat-card">
                    <div>
                        <div class="dash-stat-label">{{ __('Flocks') }}</div>
                        <div class="dash-stat-value">{{ number_format($stats['total']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'animal'])
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
                        <div class="dash-stat-label">{{ __('Birds on hand') }}</div>
                        <div class="dash-stat-value">{{ number_format($stats['birds']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'livestock'])
                </div>
                <div class="dash-stat-card">
                    <div>
                        <div class="dash-stat-label">{{ __('Placed') }}</div>
                        <div class="dash-stat-value">{{ number_format($stats['placed']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'box'])
                </div>
            </div>
        @endslot
        @slot('filters')
            <form method="GET" class="dash-form-grid">
                <div class="dash-form-field">
                    <label for="farm_id">{{ __('Farm') }}</label>
                    <select name="farm_id" id="farm_id" onchange="this.form.submit()">
                        <option value="">{{ __('All farms') }}</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" @selected(request('farm_id') == $farm->id)>{{ $farm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="production_type">{{ __('Production type') }}</label>
                    <select name="production_type" id="production_type" onchange="this.form.submit()">
                        <option value="">{{ __('All types') }}</option>
                        @foreach (config('modules.flock_production_types') as $key => $label)
                            <option value="{{ $key }}" @selected(request('production_type') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        @endslot
    @endcomponent

    @if ($flocks->isEmpty())
        <div class="dash-panel dash-entity-empty">
            <div class="dash-entity-empty__icon" aria-hidden="true">
                @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
            </div>
            <p class="dash-empty">{{ __('No flocks yet.') }}</p>
            <a href="{{ route('flocks.create') }}" class="dash-btn-save">{{ __('Place flock') }}</a>
        </div>
    @else
        <div class="dash-entity-grid">
            @foreach ($flocks as $flock)
                @include('modules.flocks._flock-card', ['flock' => $flock])
            @endforeach
        </div>
        <div class="dash-pagination">{{ $flocks->links() }}</div>
    @endif
@endsection
