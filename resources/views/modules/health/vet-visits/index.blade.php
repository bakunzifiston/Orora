@extends('layouts.health-module')

@section('title', __('Health — Vet visits'))

@section('health-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Vet visits'),
            'createRoute' => 'health.vet-visits.create',
            'createLabel' => '+ '.__('Add vet visit'),
        ])
        @include('modules.partials.flash')

        @if ($vetVisits->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'employee'])
                </div>
                <p class="dash-empty">{{ __('No vet visits logged yet.') }}</p>
                <a href="{{ route('health.vet-visits.create') }}" class="dash-btn-save">{{ __('Add vet visit') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Visit') }}</th>
                                <th>{{ __('Animal') }}</th>
                                <th>{{ __('Veterinarian') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vetVisits as $vetVisit)
                                @php
                                    $statusTone = match (strtolower((string) $vetVisit->status)) {
                                        'completed', 'done' => 'ok',
                                        'scheduled', 'pending', 'ongoing' => 'warn',
                                        'cancelled' => 'bad',
                                        default => 'muted',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'employee', 'tone' => $statusTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $vetVisit->disease_name }}</span>
                                                <span class="health-table__meta">
                                                    {{ $vetVisit->start_date->format('M j, Y') }}
                                                    @if ($vetVisit->medicine_name)
                                                        <span aria-hidden="true">·</span> {{ $vetVisit->medicine_name }}
                                                    @endif
                                                    @if ($vetVisit->follow_up_date)
                                                        <span aria-hidden="true">·</span>
                                                        {{ __('Follow-up') }} {{ $vetVisit->follow_up_date->format('M j') }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $vetVisit->stockLabel() }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $vetVisit->veterinarian_name ?: '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $statusTone }}">{{ $vetVisit->status }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $vetVisit,
                                            'editRoute' => 'health.vet-visits.edit',
                                            'destroyRoute' => 'health.vet-visits.destroy',
                                            'deleteConfirm' => __('Delete this vet visit?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $vetVisits->links() }}</div>
        @endif
    </div>
@endsection
