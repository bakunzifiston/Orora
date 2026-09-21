@extends('layouts.feeding-module')

@section('title', __('Feeding — Schedules'))

@section('feeding-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Schedules'),
            'createRoute' => 'feeding.schedules.create',
            'createLabel' => '+ '.__('Add schedule'),
        ])
        @include('modules.partials.flash')

        @if ($schedules->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'certificate'])
                </div>
                <p class="dash-empty">{{ __('No schedules yet.') }}</p>
                <a href="{{ route('feeding.schedules.create') }}" class="dash-btn-save">{{ __('Add schedule') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Schedule') }}</th>
                                <th>{{ __('Farm') }}</th>
                                <th>{{ __('Next due') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($schedules as $schedule)
                                @php
                                    $statusTone = match (strtolower((string) $schedule->status)) {
                                        'active' => 'ok',
                                        'paused', 'pending' => 'warn',
                                        'completed', 'cancelled', 'inactive' => 'muted',
                                        default => 'muted',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'certificate', 'tone' => $statusTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $schedule->feedType->name }}</span>
                                                <span class="health-table__meta">
                                                    {{ $schedule->quantity }} {{ $schedule->unit }}
                                                    <span aria-hidden="true">·</span>
                                                    {{ ucfirst($schedule->frequency) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $schedule->farm->name }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $schedule->next_due_date?->format('M j, Y') ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $statusTone }}">{{ ucfirst($schedule->status) }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $schedule,
                                            'editRoute' => 'feeding.schedules.edit',
                                            'destroyRoute' => 'feeding.schedules.destroy',
                                            'deleteConfirm' => __('Delete this schedule?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $schedules->links() }}</div>
        @endif
    </div>
@endsection
