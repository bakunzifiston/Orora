@extends('layouts.health-module')

@section('title', __('Health — Disease'))

@section('health-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Disease'),
            'createRoute' => 'health.disease.create',
            'createLabel' => '+ '.__('Add disease record'),
        ])
        @include('modules.partials.flash')

        @if (empty($diseaseReady))
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'certificate'])
                </div>
                <p class="dash-empty">{{ __('Disease records are not set up on this server yet.') }}</p>
            </div>
        @elseif ($diseaseRecords->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'certificate'])
                </div>
                <p class="dash-empty">{{ __('No disease records yet.') }}</p>
                <a href="{{ route('health.disease.create') }}" class="dash-btn-save">{{ __('Add disease record') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Disease') }}</th>
                                <th>{{ __('Animal') }}</th>
                                <th>{{ __('Severity') }}</th>
                                <th>{{ __('Recovery') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($diseaseRecords as $record)
                                @php
                                    $severityTone = match (strtolower((string) $record->severity_level)) {
                                        'mild', 'low' => 'ok',
                                        'moderate', 'medium' => 'warn',
                                        'severe', 'critical', 'high' => 'bad',
                                        default => 'muted',
                                    };
                                    $recoveryTone = match (strtolower((string) $record->recovery_status)) {
                                        'recovered', 'resolved' => 'ok',
                                        'recovering', 'under_treatment', 'ongoing' => 'warn',
                                        'deceased', 'critical' => 'bad',
                                        default => 'muted',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'certificate', 'tone' => $severityTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $record->disease_name }}</span>
                                                <span class="health-table__meta">
                                                    {{ $record->disease_code }}
                                                    <span aria-hidden="true">·</span>
                                                    {{ $record->diagnosis_date->format('M j, Y') }}
                                                    @if ($record->farm)
                                                        <span aria-hidden="true">·</span>
                                                        {{ $record->farm->name }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $record->stockLabel() }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $severityTone }}">{{ $record->severityLabel() }}</span>
                                    </td>
                                    <td>
                                        <div class="health-table__stack">
                                            <span class="health-table__pill health-table__pill--{{ $recoveryTone }}">{{ $record->recoveryLabel() }}</span>
                                            @if ($record->contagious_status || $record->quarantine_required)
                                                <span class="health-table__meta">
                                                    {{ $record->contagiousLabel() }}
                                                    @if ($record->quarantine_required)
                                                        <span aria-hidden="true">·</span> {{ __('Quarantine') }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $record,
                                            'editRoute' => 'health.disease.edit',
                                            'destroyRoute' => 'health.disease.destroy',
                                            'deleteConfirm' => __('Delete this disease record?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $diseaseRecords->links() }}</div>
        @endif
    </div>
@endsection
