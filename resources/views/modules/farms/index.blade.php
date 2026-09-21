@extends('layouts.dashboard')

@section('title', __('Farms'))

@section('content')
    <div class="farm-dash farms-page">
        @include('modules.partials.header', [
            'title' => __('Farms'),
            'createRoute' => 'farms.create',
            'createLabel' => '+ '. __('Register farm'),
        ])
        @include('modules.partials.flash')

        <form method="GET" action="{{ route('farms.index') }}" class="dash-ops-toolbar farms-page__toolbar" id="farms-filters-form">
            <div class="dash-ops-toolbar__controls farms-page__filters">
                <div class="dash-ops-field farms-page__search">
                    <label for="filter_q">{{ __('Search') }}</label>
                    <input
                        type="search"
                        name="q"
                        id="filter_q"
                        value="{{ $search }}"
                        placeholder="{{ __('Name, owner, reg. no, location…') }}"
                        autocomplete="off"
                    >
                </div>
                <div class="dash-ops-field">
                    <label for="filter_status">{{ __('Status') }}</label>
                    <select name="status" id="filter_status" onchange="this.form.submit()">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (config('modules.farm_statuses') as $option)
                            <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-ops-field">
                    <label for="filter_species">{{ __('Species') }}</label>
                    <select name="species" id="filter_species" onchange="this.form.submit()">
                        <option value="">{{ __('All species') }}</option>
                        @foreach (config('modules.species') as $option)
                            <option value="{{ $option }}" @selected($species === $option)>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-ops-field">
                    <label for="filter_district">{{ __('District') }}</label>
                    <select name="district" id="filter_district" onchange="this.form.submit()">
                        <option value="">{{ __('All districts') }}</option>
                        @foreach ($districts as $option)
                            <option value="{{ $option }}" @selected($district === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="dash-btn-save dash-ops-apply">{{ __('Apply') }}</button>
                @if ($filtersActive)
                    <a href="{{ route('farms.index') }}" class="dash-btn-cancel">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <div class="farm-kpi farm-kpi--farms">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'farm'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Total farms') }}</div>
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
                <div class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'movement'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Total area') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['total_hectares'], 1) }}
                            <span class="farm-kpi__unit">ha</span>
                        </div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'livestock'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Livestock groups') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['livestock_groups']) }}</div>
                    </div>
                </div>
            </div>
        </section>

        @if ($farms->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'farm'])
                </div>
                @if ($filtersActive)
                    <p class="dash-empty">{{ __('No farms match your search or filters.') }}</p>
                    <a href="{{ route('farms.index') }}" class="dash-btn-cancel">{{ __('Clear filters') }}</a>
                @else
                    <p class="dash-empty">{{ __('No farms registered yet.') }}</p>
                    <a href="{{ route('farms.create') }}" class="dash-btn-save">{{ __('Register your first farm') }}</a>
                @endif
            </div>
        @else
            <div class="dash-entity-grid">
                @foreach ($farms as $farm)
                    @include('modules.farms._farm-card', ['farm' => $farm])
                @endforeach
            </div>
            <div class="dash-pagination">{{ $farms->links() }}</div>
        @endif
    </div>
@endsection
