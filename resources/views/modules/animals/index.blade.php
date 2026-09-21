@extends('layouts.dashboard')

@section('title', __('Animals'))

@section('content')
    <div class="farm-dash farms-page animals-page">
        @include('modules.partials.header', [
            'title' => __('Animals'),
            'createRoute' => 'animals.create',
            'createLabel' => '+ '. __('Register animal'),
            'secondaryLinks' => [
                [
                    'route' => 'animals.export',
                    'params' => request()->query(),
                    'label' => __('Export CSV'),
                    'class' => 'dash-farm-card__btn',
                ],
                [
                    'route' => 'animals.import',
                    'label' => __('Import'),
                    'class' => 'dash-farm-card__btn',
                ],
            ],
        ])
        @include('modules.partials.flash')

        <form method="GET" action="{{ route('animals.index') }}" class="dash-ops-toolbar farms-page__toolbar" id="animals-filters-form">
            <div class="dash-ops-toolbar__controls farms-page__filters">
                <div class="dash-ops-field farms-page__search">
                    <label for="filter_q">{{ __('Search') }}</label>
                    <input
                        type="search"
                        name="q"
                        id="filter_q"
                        value="{{ $search }}"
                        placeholder="{{ __('Tag, name, farm, group…') }}"
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
                    <label for="filter_livestock">{{ __('Group') }}</label>
                    <select name="livestock_id" id="filter_livestock" onchange="this.form.submit()">
                        <option value="">{{ __('All groups') }}</option>
                        @foreach ($livestockGroups as $group)
                            <option value="{{ $group->id }}" @selected($livestockId == $group->id)>
                                {{ $group->name }}@if (! $farmId) — {{ $group->farm?->name }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-ops-field">
                    <label for="filter_gender">{{ __('Gender') }}</label>
                    <select name="gender" id="filter_gender" onchange="this.form.submit()">
                        <option value="">{{ __('All') }}</option>
                        @foreach (config('modules.animal_genders') as $value => $label)
                            <option value="{{ $value }}" @selected($gender === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-ops-field">
                    <label for="filter_health">{{ __('Health') }}</label>
                    <select name="health_status" id="filter_health" onchange="this.form.submit()">
                        <option value="">{{ __('All') }}</option>
                        @foreach (config('modules.health_statuses') as $option)
                            <option value="{{ $option }}" @selected($healthStatus === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-ops-field">
                    <label for="filter_lifecycle">{{ __('Lifecycle') }}</label>
                    <select name="lifecycle_status" id="filter_lifecycle" onchange="this.form.submit()">
                        <option value="">{{ __('All') }}</option>
                        @foreach (config('modules.lifecycle_statuses') as $option)
                            <option value="{{ $option }}" @selected($lifecycleStatus === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="dash-btn-save dash-ops-apply">{{ __('Apply') }}</button>
                @if ($filtersActive)
                    <a href="{{ route('animals.index') }}" class="dash-btn-cancel">{{ __('Clear') }}</a>
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
                        <div class="farm-kpi__label">{{ __('Total animals') }}</div>
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
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Female') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['female']) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Male') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['male']) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--sales">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'milk'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Lactating') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['lactating']) }}</div>
                    </div>
                </div>

                @foreach ($moduleKpis as $kpi)
                    @php
                        $kpiRoute = ! empty($kpi['route']) && Route::has($kpi['route'])
                            ? route($kpi['route'])
                            : null;
                    @endphp
                    @if ($kpiRoute)
                        <a href="{{ $kpiRoute }}" class="farm-kpi farm-kpi--{{ $kpi['key'] }}">
                            <div class="farm-kpi__icon" aria-hidden="true">
                                @include('layouts.partials.dashboard-nav-icon', ['icon' => $kpi['icon']])
                            </div>
                            <div class="farm-kpi__body">
                                <div class="farm-kpi__label">{{ __($kpi['label']) }}</div>
                                <div class="farm-kpi__value">{{ $kpi['value'] }}</div>
                            </div>
                        </a>
                    @else
                        <div class="farm-kpi farm-kpi--{{ $kpi['key'] }}">
                            <div class="farm-kpi__icon" aria-hidden="true">
                                @include('layouts.partials.dashboard-nav-icon', ['icon' => $kpi['icon']])
                            </div>
                            <div class="farm-kpi__body">
                                <div class="farm-kpi__label">{{ __($kpi['label']) }}</div>
                                <div class="farm-kpi__value">{{ $kpi['value'] }}</div>
                            </div>
                        </div>
                    @endif
                @endforeach

                @foreach ($livestockGroups as $group)
                    <a
                        href="{{ route('animals.index', array_filter(['farm_id' => $farmId, 'livestock_id' => $group->id])) }}"
                        class="farm-kpi farm-kpi--stock {{ $livestockId == $group->id ? 'farm-kpi--selected' : '' }}"
                    >
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'livestock'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ $group->name }}</div>
                            <div class="farm-kpi__value">{{ number_format($group->animals_count) }}</div>
                            @if (! $farmId && $group->farm)
                                <div class="farm-kpi__hint">{{ $group->farm->name }}</div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        @if ($animals->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                </div>
                @if ($filtersActive)
                    <p class="dash-empty">{{ __('No animals match your search or filters.') }}</p>
                    <a href="{{ route('animals.index') }}" class="dash-btn-cancel">{{ __('Clear filters') }}</a>
                @else
                    <p class="dash-empty">{{ __('No animals registered yet.') }}</p>
                    <a href="{{ route('animals.create') }}" class="dash-btn-save">{{ __('Register an animal') }}</a>
                @endif
            </div>
        @else
            <div class="dash-panel animals-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="animals-table">
                        <thead>
                            <tr>
                                <th>{{ __('Animal') }}</th>
                                <th>{{ __('Location') }}</th>
                                <th>{{ __('Health') }}</th>
                                <th>{{ __('Production') }}</th>
                                <th>{{ __('Age') }}</th>
                                <th class="animals-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($animals as $animal)
                                @php
                                    $healthTone = match ($animal->health_status) {
                                        'Healthy' => 'ok',
                                        'Pregnant' => 'warn',
                                        'Sick', 'Under treatment', 'Quarantined' => 'bad',
                                        default => 'muted',
                                    };
                                    $initials = collect(preg_split('/\s+/', trim((string) $animal->name)) ?: [])
                                        ->filter()
                                        ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
                                        ->take(2)
                                        ->join('') ?: strtoupper(substr((string) $animal->tag_number, 0, 2));
                                @endphp
                                <tr>
                                    <td>
                                        <div class="animals-table__animal">
                                            @if ($animal->photo_url)
                                                <img src="{{ $animal->photo_url }}" alt="" class="animals-table__avatar animals-table__avatar--photo">
                                            @else
                                                <span class="animals-table__avatar" aria-hidden="true">{{ $initials }}</span>
                                            @endif
                                            <div class="animals-table__animal-text">
                                                <a href="{{ route('animals.show', $animal) }}" class="animals-table__tag">{{ $animal->tag_number }}</a>
                                                <span class="animals-table__meta">
                                                    {{ $animal->name }}
                                                    <span aria-hidden="true">·</span>
                                                    {{ $animal->gender_label }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="animals-table__stack">
                                            @if ($animal->farm)
                                                <a href="{{ route('farms.show', $animal->farm) }}" class="animals-table__main-link">{{ $animal->farm->name }}</a>
                                            @else
                                                <span class="animals-table__main-link">—</span>
                                            @endif
                                            <span class="animals-table__meta">
                                                @if ($animal->livestock)
                                                    <a href="{{ route('livestock.show', $animal->livestock) }}">{{ $animal->livestock->name }}</a>
                                                @else
                                                    {{ __('No group') }}
                                                @endif
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="animals-table__stack">
                                            <span class="animals-table__pill animals-table__pill--{{ $healthTone }}">{{ $animal->health_status }}</span>
                                            <span class="animals-table__meta">{{ $animal->lifecycle_status }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="animals-table__value">{{ $animal->production_status ?: '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="animals-table__value">{{ $animal->age_label ?: '—' }}</span>
                                    </td>
                                    <td class="animals-table__actions">
                                        <div class="animals-table__action-btns">
                                            <a href="{{ route('animals.show', $animal) }}" class="dash-btn-save dash-btn-save--sm">{{ __('View') }}</a>
                                            <a href="{{ route('animals.edit', $animal) }}" class="dash-farm-card__btn">{{ __('Edit') }}</a>
                                            <form method="POST" action="{{ route('animals.destroy', $animal) }}" onsubmit="return confirm(@js(__('Delete this animal?')));">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dash-farm-card__btn dash-farm-card__btn--danger">{{ __('Delete') }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $animals->links() }}</div>
        @endif
    </div>
@endsection
