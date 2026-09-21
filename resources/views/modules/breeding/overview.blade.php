@extends('layouts.breeding-module')

@section('title', __('Breeding — Overview'))

@section('breeding-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Breeding'),
            'createRoute' => 'breeding.records.create',
            'createLabel' => '+ '. __('Record breeding'),
        ])
        @include('modules.partials.flash')

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <a href="{{ route('breeding.records') }}" class="farm-kpi farm-kpi--breeding">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'breeding'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Active breedings') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['active_breedings']) }}</div>
                    </div>
                </a>
                <a href="{{ route('breeding.records', ['status' => 'confirmed_pregnant']) }}" class="farm-kpi farm-kpi--health">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Confirmed pregnant') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['confirmed_pregnant']) }}</div>
                    </div>
                </a>
                <div class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'certificate'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Due this month') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['due_this_month']) }}</div>
                    </div>
                </div>
                <a href="{{ route('breeding.births') }}" class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Births this month') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['births_this_month']) }}</div>
                    </div>
                </a>
                <a href="{{ route('breeding.records', ['pregnancy_check_due' => 1]) }}" class="farm-kpi farm-kpi--sales">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'shield'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Pregnancy checks due') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['pregnancy_checks_due'] ?? 0) }}</div>
                    </div>
                </a>
            </div>
        </section>

        <section class="farm-panel" id="pregnancy-check-due">
            <header class="farm-panel__head">
                <div>
                    <h2 class="farm-panel__title">{{ __('Pregnancy checks due') }}</h2>
                    <p class="farm-panel__desc">
                        {{ config('modules.breeding_pregnancy_check_due_days', 35) }}+ {{ __('days after breeding') }}
                    </p>
                </div>
            </header>
            @if ($pregnancyChecksDue->isEmpty())
                <p class="dash-empty">{{ __('No pregnancy checks due right now.') }}</p>
            @else
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Breeding') }}</th>
                                <th>{{ __('Female') }}</th>
                                <th>{{ __('Bred on') }}</th>
                                <th>{{ __('Due on') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pregnancyChecksDue as $record)
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'breeding', 'tone' => 'warn'])
                                            <div class="health-table__stack">
                                                <a href="{{ route('breeding.records.edit', $record) }}" class="health-table__title health-table__title--link">{{ $record->breeding_code }}</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $record->femaleAnimal->tag_number }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $record->breeding_date->format('M j, Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--warn">{{ $record->pregnancy_check_due_on?->format('M j, Y') ?? '—' }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        <div class="health-table__action-btns">
                                            <a href="{{ route('breeding.checks.create', ['breeding_record_id' => $record->id]) }}" class="dash-btn-save dash-btn-save--sm">{{ __('Record check') }}</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="farm-panel__desc" style="margin-top: 0.75rem;">
                    <a href="{{ route('breeding.records', ['pregnancy_check_due' => 1]) }}">{{ __('View all due breedings') }} →</a>
                </p>
            @endif
        </section>

        <div class="farm-dash__charts farm-dash__charts--2">
            <section class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Upcoming calvings') }}</h2>
                    </div>
                </header>
                @if ($upcomingCalvings->isEmpty())
                    <p class="dash-empty">{{ __('No confirmed pregnancies with expected dates.') }}</p>
                @else
                    <ul class="farm-activity">
                        @foreach ($upcomingCalvings as $record)
                            <li class="farm-activity__item farm-activity__item--plain farm-activity__item--split">
                                <div class="farm-activity__body">
                                    <a href="{{ route('breeding.records.edit', $record) }}" class="farm-activity__title">{{ $record->femaleAnimal->tag_number }}</a>
                                    <span class="farm-activity__meta">{{ $record->farm->name }}</span>
                                </div>
                                <span class="farm-activity__count">{{ $record->expected_calving_date?->format('M j, Y') ?? '—' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Recent breedings') }}</h2>
                    </div>
                </header>
                @if ($recentBreedings->isEmpty())
                    <p class="dash-empty">{{ __('No breeding records yet.') }}</p>
                @else
                    <ul class="farm-activity">
                        @foreach ($recentBreedings as $record)
                            <li class="farm-activity__item farm-activity__item--plain farm-activity__item--split">
                                <div class="farm-activity__body">
                                    <a href="{{ route('breeding.records.edit', $record) }}" class="farm-activity__title">{{ $record->breeding_code }}</a>
                                    <span class="farm-activity__meta">{{ $record->femaleAnimal->tag_number }} · {{ $record->breeding_date->format('M j, Y') }}</span>
                                </div>
                                <span class="employees-table__pill employees-table__pill--muted">{{ $record->statusLabel() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
@endsection
