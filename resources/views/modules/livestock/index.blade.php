@extends('layouts.dashboard')

@section('title', __('Livestock'))

@section('content')
    <div class="farm-dash farms-page livestock-page">
        @include('modules.partials.header', [
            'title' => __('Livestock'),
            'createRoute' => 'livestock.create',
            'createLabel' => '+ '. __('Add livestock'),
        ])
        @include('modules.partials.flash')

        <form method="GET" action="{{ route('livestock.index') }}" class="dash-ops-toolbar farms-page__toolbar" id="livestock-filters-form">
            <div class="dash-ops-toolbar__controls farms-page__filters">
                <div class="dash-ops-field farms-page__search">
                    <label for="filter_q">{{ __('Search') }}</label>
                    <input
                        type="search"
                        name="q"
                        id="filter_q"
                        value="{{ $search }}"
                        placeholder="{{ __('Group name, farm, breed…') }}"
                        autocomplete="off"
                    >
                </div>
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
                    <label for="filter_status">{{ __('Status') }}</label>
                    <select name="status" id="filter_status" onchange="this.form.submit()">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (config('modules.record_statuses') as $option)
                            <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="dash-btn-save dash-ops-apply">{{ __('Apply') }}</button>
                @if ($filtersActive)
                    <a href="{{ route('livestock.index') }}" class="dash-btn-cancel">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'livestock'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Total groups') }}</div>
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
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'feeding'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Total head count') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['head_count']) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--sales">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Animals registered') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['animals']) }}</div>
                    </div>
                </div>
            </div>
        </section>

        @if ($livestock->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'livestock'])
                </div>
                @if ($filtersActive)
                    <p class="dash-empty">{{ __('No livestock groups match your search or filters.') }}</p>
                    <a href="{{ route('livestock.index') }}" class="dash-btn-cancel">{{ __('Clear filters') }}</a>
                @else
                    <p class="dash-empty">{{ __('No livestock groups yet.') }}</p>
                    <a href="{{ route('livestock.create') }}" class="dash-btn-save">{{ __('Add livestock group') }}</a>
                @endif
            </div>
        @else
            <div class="dash-entity-grid">
                @foreach ($livestock as $group)
                    @include('modules.livestock._livestock-card', ['group' => $group])
                @endforeach
            </div>
            <div class="dash-pagination">{{ $livestock->links() }}</div>
        @endif
    </div>
@endsection
