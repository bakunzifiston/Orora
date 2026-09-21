@extends('layouts.breeding-module')

@section('title', __('Breeding — Birth records'))

@section('breeding-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Birth records'),
            'createRoute' => 'breeding.births.create',
            'createLabel' => '+ '. __('Record birth'),
        ])
        @include('modules.partials.flash')

        @if ($births->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                </div>
                <p class="dash-empty">{{ __('No births recorded yet.') }}</p>
                <a href="{{ route('breeding.births.create') }}" class="dash-btn-save">{{ __('Record birth') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Birth') }}</th>
                                <th>{{ __('Mother') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Offspring') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($births as $birth)
                                @php
                                    $tone = ($birth->stillborn_offspring ?? 0) > 0 ? 'warn' : 'ok';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'animal', 'tone' => $tone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $birth->birth_code }}</span>
                                                <span class="health-table__meta">{{ $birth->birth_date->format('M j, Y') }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $birth->motherAnimal->tag_number }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--muted">{{ ucfirst($birth->birth_type) }}</span>
                                    </td>
                                    <td>
                                        <div class="health-table__stack">
                                            <span class="health-table__value">{{ $birth->alive_offspring }} {{ __('alive') }}</span>
                                            @if (($birth->stillborn_offspring ?? 0) > 0)
                                                <span class="health-table__meta">{{ $birth->stillborn_offspring }} {{ __('stillborn') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="health-table__actions">
                                        <div class="health-table__action-btns">
                                            <a href="{{ route('breeding.births.edit', $birth) }}" class="dash-farm-card__btn">{{ __('Edit') }}</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $births->links() }}</div>
        @endif
    </div>
@endsection
