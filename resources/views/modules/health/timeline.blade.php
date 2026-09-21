@extends('layouts.health-module')

@section('title', __('Health — Timeline'))

@section('health-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Timeline'),
            'createRoute' => 'health.records.create',
            'createRouteParams' => ['section' => 'timeline'],
            'createLabel' => '+ '.__('Log health record'),
        ])
        @include('modules.partials.flash')

        @if ($healthRecords->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                </div>
                <p class="dash-empty">{{ __('No health events yet.') }}</p>
                <a href="{{ route('health.records.create', ['section' => 'timeline']) }}" class="dash-btn-save">{{ __('Log a record') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Event') }}</th>
                                <th>{{ __('Animal') }}</th>
                                <th>{{ __('Farm') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($healthRecords as $record)
                                @php
                                    $statusTone = match ($record->health_status) {
                                        'Healthy', 'Recovered' => 'ok',
                                        'Recovering', 'Under treatment', 'Pregnant' => 'warn',
                                        'Sick', 'Quarantined', 'Deceased' => 'bad',
                                        default => 'muted',
                                    };
                                    $eventIcon = match ($record->record_type) {
                                        'Vaccination' => 'shield',
                                        'Treatment', 'Deworming' => 'health',
                                        'Vet visit' => 'employee',
                                        'Illness', 'Injury', 'Quarantine' => 'certificate',
                                        'Mortality' => 'animal',
                                        default => 'health',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => $eventIcon, 'tone' => $statusTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $record->record_type }}</span>
                                                <span class="health-table__meta">
                                                    {{ $record->recorded_on->format('M j, Y') }}
                                                    @if ($record->title)
                                                        <span aria-hidden="true">·</span> {{ $record->title }}
                                                    @elseif ($record->treatment || $record->medication)
                                                        <span aria-hidden="true">·</span>
                                                        {{ $record->treatment ?: $record->medication }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $record->stockLabel() }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $record->farm->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $statusTone }}">{{ $record->health_status }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $record,
                                            'editRoute' => 'health.records.edit',
                                            'destroyRoute' => 'health.records.destroy',
                                            'section' => 'timeline',
                                            'deleteConfirm' => __('Delete this health record?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $healthRecords->links() }}</div>
        @endif
    </div>
@endsection
