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
                                                    @if ($schedule->animal || $schedule->livestock || $schedule->flock)
                                                        <span aria-hidden="true">·</span>
                                                        {{ $schedule->animal?->tag_number ?? $schedule->livestock?->name ?? $schedule->flock?->name }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $schedule->farm->name }}</span>
                                    </td>
                                    <td>
                                        @php $due = app(\App\Services\Feeding\FeedingScheduleService::class)->dueState($schedule); @endphp
                                        <div class="health-table__stack">
                                            <span class="health-table__value">{{ $schedule->next_due_date?->format('M j, Y') ?? '—' }}{{ $schedule->scheduled_time ? ' · '.substr((string) $schedule->scheduled_time, 0, 5) : '' }}</span>
                                            @if ($due)
                                                <span>
                                                    @if ($due['state'] === 'overdue')
                                                        <span class="health-table__pill health-table__pill--bad">{{ $due['days'] <= 7
                                                            ? trans_choice('{1} Overdue by 1 day|[2,*] Overdue by :count days', $due['days'], ['count' => $due['days']])
                                                            : __('Not recorded since :date', ['date' => $schedule->next_due_date->format($schedule->next_due_date->isCurrentYear() ? 'M j' : 'M j, Y')]) }}</span>
                                                    @elseif ($due['state'] === 'today')
                                                        <span class="health-table__pill health-table__pill--warn">{{ __('Due today') }}</span>
                                                    @else
                                                        <span class="health-table__meta">{{ trans_choice('{1} In 1 day|[2,*] In :count days', $due['days'], ['count' => $due['days']]) }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $statusTone }}">{{ ucfirst($schedule->status) }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @if ($schedule->status === 'active')
                                            <a href="{{ route('feeding.records.create', ['schedule' => $schedule->id]) }}" class="dash-farm-card__btn" style="margin-bottom: 0.35rem; display: inline-block;">{{ __('Record feeding') }}</a>
                                        @endif
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
