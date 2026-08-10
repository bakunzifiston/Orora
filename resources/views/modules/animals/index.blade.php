@extends('layouts.dashboard')

@section('title', __('Animals'))

@section('content')
    @include('modules.partials.header', [
        'title' => __('Animals'),
        'subtitle' => __('Individual animals with identity, physical profile, and status tracking.'),
        'createRoute' => 'animals.create',
        'createLabel' => '+ '. __('Register animal'),
        'secondaryLinks' => [
            [
                'route' => 'animals.export',
                'params' => request()->query(),
                'label' => __('Export CSV'),
            ],
            [
                'route' => 'animals.import',
                'label' => __('Import'),
            ],
        ],
    ])
    @include('modules.partials.flash')

    @component('modules.partials.index-toolbar', ['filtersWide' => true])
        @slot('stats')
            <div class="dash-health-stats">
                <div class="dash-stat-card">
                    <div>
                        <div class="dash-stat-label">{{ __('Total animals') }}</div>
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
                        <div class="dash-stat-label">{{ __('Female') }}</div>
                        <div class="dash-stat-value">{{ number_format($stats['female']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'animal'])
                </div>
                <div class="dash-stat-card">
                    <div>
                        <div class="dash-stat-label">{{ __('Lactating') }}</div>
                        <div class="dash-stat-value">{{ number_format($stats['lactating']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'milk'])
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
                    <label for="livestock_id">{{ __('Livestock group') }}</label>
                    <select name="livestock_id" id="livestock_id" onchange="this.form.submit()">
                        <option value="">{{ __('All groups') }}</option>
                        @foreach ($livestockGroups as $group)
                            <option value="{{ $group->id }}" @selected(request('livestock_id') == $group->id)>{{ $group->name }} — {{ $group->farm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="gender">{{ __('Gender') }}</label>
                    <select name="gender" id="gender" onchange="this.form.submit()">
                        <option value="">{{ __('All') }}</option>
                        @foreach (config('modules.animal_genders') as $value => $label)
                            <option value="{{ $value }}" @selected(request('gender') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="health_status">{{ __('Health') }}</label>
                    <select name="health_status" id="health_status" onchange="this.form.submit()">
                        <option value="">{{ __('All') }}</option>
                        @foreach (config('modules.health_statuses') as $status)
                            <option value="{{ $status }}" @selected(request('health_status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        @endslot
    @endcomponent

    @if ($animals->isEmpty())
        <div class="dash-panel dash-entity-empty">
            <div class="dash-entity-empty__icon" aria-hidden="true">
                @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
            </div>
            <p class="dash-empty">{{ __('No animals registered yet.') }}</p>
            <a href="{{ route('animals.create') }}" class="dash-btn-save">{{ __('Register an animal') }}</a>
        </div>
    @else
        <div class="dash-entity-grid">
            @foreach ($animals as $animal)
                @include('modules.animals._animal-card', ['animal' => $animal])
            @endforeach
        </div>
        <div class="dash-pagination">{{ $animals->links() }}</div>
    @endif
@endsection
