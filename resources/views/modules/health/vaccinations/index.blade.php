@extends('layouts.health-module')

@section('title', __('Health — Vaccinations'))

@section('health-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Vaccinations'),
            'createRoute' => 'health.vaccinations.create',
            'createLabel' => '+ '.__('Add vaccination'),
        ])
        @include('modules.partials.flash')

        @if ($vaccinations->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'shield'])
                </div>
                <p class="dash-empty">{{ __('No vaccinations logged yet.') }}</p>
                <a href="{{ route('health.vaccinations.create') }}" class="dash-btn-save">{{ __('Add vaccination') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Vaccine') }}</th>
                                <th>{{ __('Animal') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Next due') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vaccinations as $vaccination)
                                @php
                                    $statusTone = match (strtolower((string) $vaccination->status)) {
                                        'completed', 'done', 'administered' => 'ok',
                                        'scheduled', 'pending', 'due' => 'warn',
                                        'missed', 'cancelled' => 'bad',
                                        default => 'muted',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'shield', 'tone' => $statusTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $vaccination->vaccine_name }}</span>
                                                <span class="health-table__meta">
                                                    {{ $vaccination->vaccination_date->format('M j, Y') }}
                                                    @if ($vaccination->vaccine_type)
                                                        <span aria-hidden="true">·</span> {{ $vaccination->vaccine_type }}
                                                    @endif
                                                    @if ($vaccination->batch_number)
                                                        <span aria-hidden="true">·</span> {{ $vaccination->batch_number }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $vaccination->stockLabel() }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $statusTone }}">{{ $vaccination->status }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $vaccination->next_due_date?->format('M j, Y') ?? '—' }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $vaccination,
                                            'editRoute' => 'health.vaccinations.edit',
                                            'destroyRoute' => 'health.vaccinations.destroy',
                                            'deleteConfirm' => __('Delete this vaccination?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $vaccinations->links() }}</div>
        @endif
    </div>
@endsection
