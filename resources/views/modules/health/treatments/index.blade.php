@extends('layouts.health-module')

@section('title', __('Health — Treatments'))

@section('health-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Treatments'),
            'createRoute' => 'health.treatments.create',
            'createLabel' => '+ '.__('Add treatment'),
        ])
        @include('modules.partials.flash')

        @if ($treatments->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                </div>
                <p class="dash-empty">{{ __('No treatments logged yet.') }}</p>
                <a href="{{ route('health.treatments.create') }}" class="dash-btn-save">{{ __('Add treatment') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Treatment') }}</th>
                                <th>{{ __('Animal') }}</th>
                                <th>{{ __('Medicine') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($treatments as $treatment)
                                @php
                                    $statusTone = match (strtolower((string) $treatment->status)) {
                                        'completed', 'recovered', 'done' => 'ok',
                                        'ongoing', 'active', 'in progress' => 'warn',
                                        'failed', 'stopped' => 'bad',
                                        default => 'muted',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'health', 'tone' => $statusTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $treatment->disease_name }}</span>
                                                <span class="health-table__meta">
                                                    {{ $treatment->start_date->format('M j, Y') }}
                                                    @if ($treatment->follow_up_date)
                                                        <span aria-hidden="true">·</span>
                                                        {{ __('Follow-up') }} {{ $treatment->follow_up_date->format('M j') }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $treatment->stockLabel() }}</span>
                                    </td>
                                    <td>
                                        <div class="health-table__stack">
                                            <span class="health-table__value">{{ $treatment->medicine_name }}</span>
                                            @if ($treatment->dosage)
                                                <span class="health-table__meta">{{ $treatment->dosage }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $statusTone }}">{{ $treatment->status }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $treatment,
                                            'editRoute' => 'health.treatments.edit',
                                            'destroyRoute' => 'health.treatments.destroy',
                                            'deleteConfirm' => __('Delete this treatment?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $treatments->links() }}</div>
        @endif
    </div>
@endsection
