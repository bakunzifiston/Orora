@extends('layouts.health-module')

@section('title', __('Health — Mortality'))

@section('health-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Mortality'),
            'createRoute' => 'health.mortalities.create',
            'createLabel' => '+ '.__('Add mortality record'),
        ])
        @include('modules.partials.flash')

        @if ($deceasedAnimals->isNotEmpty())
            <section class="farm-panel health-page__side-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Deceased animals') }}</h2>
                        <p class="farm-panel__desc">{{ __('Marked deceased in the herd') }}</p>
                    </div>
                </header>
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Animal') }}</th>
                                <th>{{ __('Farm') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deceasedAnimals as $animal)
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'animal', 'tone' => 'muted'])
                                            <div class="health-table__stack">
                                                <a href="{{ route('animals.show', $animal) }}" class="health-table__title health-table__title--link">{{ $animal->tag_number }}</a>
                                                <span class="health-table__meta">{{ $animal->name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $animal->farm->name }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--muted">{{ $animal->lifecycle_status }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if ($mortalities->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                </div>
                <p class="dash-empty">{{ __('No mortality records logged yet.') }}</p>
                <a href="{{ route('health.mortalities.create') }}" class="dash-btn-save">{{ __('Add mortality record') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Animal') }}</th>
                                <th>{{ __('Cause') }}</th>
                                <th>{{ __('Reported by') }}</th>
                                <th>{{ __('Disposal') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mortalities as $mortality)
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'animal', 'tone' => 'bad'])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $mortality->stockLabel() }}</span>
                                                <span class="health-table__meta">
                                                    {{ $mortality->death_date->format('M j, Y') }}
                                                    @if ($mortality->flock_id)
                                                        <span aria-hidden="true">·</span>
                                                        {{ number_format($mortality->deaths_count) }} {{ __('birds') }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $mortality->cause_of_death ?: '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $mortality->reported_by ?: '—' }}</span>
                                    </td>
                                    <td>
                                        <div class="health-table__stack">
                                            <span class="health-table__value">{{ $mortality->disposal_method ?: '—' }}</span>
                                            <span class="health-table__meta">
                                                {{ $mortality->postmortem_done ? __('Postmortem done') : __('No postmortem') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $mortality,
                                            'editRoute' => 'health.mortalities.edit',
                                            'destroyRoute' => 'health.mortalities.destroy',
                                            'deleteConfirm' => __('Delete this mortality record?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $mortalities->links() }}</div>
        @endif
    </div>
@endsection
