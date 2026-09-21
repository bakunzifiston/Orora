@extends('layouts.dashboard')

@section('title', __('Flocks'))

@section('content')
    <div class="farm-dash farms-page livestock-page">
        @include('modules.partials.header', [
            'title' => __('Flocks'),
            'createRoute' => 'flocks.create',
            'createLabel' => '+ '. __('Place flock'),
        ])
        @include('modules.partials.flash')

        <form method="GET" action="{{ route('flocks.index') }}" class="dash-ops-toolbar farms-page__toolbar" id="flocks-filters-form">
            <div class="dash-ops-toolbar__controls farms-page__filters">
                <div class="dash-ops-field">
                    <label for="filter_farm">{{ __('Farm') }}</label>
                    <select name="farm_id" id="filter_farm" onchange="this.form.submit()">
                        <option value="">{{ __('All farms') }}</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" @selected($farmId == $farm->id)>{{ $farm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-ops-field">
                    <label for="filter_type">{{ __('Production type') }}</label>
                    <select name="production_type" id="filter_type" onchange="this.form.submit()">
                        <option value="">{{ __('All types') }}</option>
                        @foreach (config('modules.flock_production_types') as $key => $label)
                            <option value="{{ $key }}" @selected($productionType === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="dash-btn-save dash-ops-apply">{{ __('Apply') }}</button>
                @if ($filtersActive)
                    <a href="{{ route('flocks.index') }}" class="dash-btn-cancel">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Flocks') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['total']) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Active') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['active']) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--feeding">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'livestock'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Birds on hand') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['birds']) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--sales">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'box'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Placed') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['placed']) }}</div>
                    </div>
                </div>
            </div>
        </section>

        @if ($flocks->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                </div>
                @if ($filtersActive)
                    <p class="dash-empty">{{ __('No flocks match your filters.') }}</p>
                    <a href="{{ route('flocks.index') }}" class="dash-btn-cancel">{{ __('Clear filters') }}</a>
                @else
                    <p class="dash-empty">{{ __('No flocks yet.') }}</p>
                    <a href="{{ route('flocks.create') }}" class="dash-btn-save">{{ __('Place flock') }}</a>
                @endif
            </div>
        @else
            <div class="dash-entity-grid">
                @foreach ($flocks as $flock)
                    @include('modules.flocks._flock-card', ['flock' => $flock])
                @endforeach
            </div>
            <div class="dash-pagination">{{ $flocks->links() }}</div>
        @endif
    </div>
@endsection
