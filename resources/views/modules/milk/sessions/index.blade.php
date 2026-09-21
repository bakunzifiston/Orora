@extends('layouts.milk-module')

@section('title', __('Milk — Sessions'))

@section('milk-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Milking sessions'),
            'createRoute' => 'milk.sessions.create',
            'createLabel' => '+ '.__('Open session'),
        ])
        @include('modules.partials.flash')

        @if ($sessions->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'milk'])
                </div>
                <p class="dash-empty">{{ __('No milking sessions yet.') }}</p>
                <a href="{{ route('milk.sessions.create') }}" class="dash-btn-save">{{ __('Open session') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Session') }}</th>
                                <th>{{ __('Farm / herd') }}</th>
                                <th>{{ __('Shift') }}</th>
                                <th>{{ __('Yield') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sessions as $session)
                                @php
                                    $statusTone = match (strtolower((string) $session->status)) {
                                        'completed' => 'ok',
                                        'open' => 'warn',
                                        'cancelled' => 'bad',
                                        default => 'muted',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'milk', 'tone' => $statusTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $session->session_code }}</span>
                                                <span class="health-table__meta">
                                                    {{ $session->session_date->format('M j, Y') }}
                                                    <span aria-hidden="true">·</span>
                                                    {{ $session->number_of_animals_milked }} {{ __('animals') }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="health-table__stack">
                                            <span class="health-table__value">{{ $session->farm->name }}</span>
                                            <span class="health-table__meta">{{ $session->livestock->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $session->shiftLabel() }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ number_format($session->total_yield_liters, 2) }} L</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $statusTone }}">{{ ucfirst($session->status) }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $session,
                                            'editRoute' => 'milk.sessions.edit',
                                            'destroyRoute' => 'milk.sessions.destroy',
                                            'deleteConfirm' => __('Delete this milking session?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $sessions->links() }}</div>
        @endif
    </div>
@endsection
