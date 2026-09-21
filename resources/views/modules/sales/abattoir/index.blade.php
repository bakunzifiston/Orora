@extends('layouts.sales-module')

@section('title', __('Sales — Abattoir'))

@section('sales-content')
    <div class="farm-dash farms-page sales-page">
        @include('modules.partials.header', [
            'title' => __('Abattoir'),
            'createRoute' => 'sales.abattoir.create',
            'createLabel' => '+ '. __('New dispatch'),
        ])
        @include('modules.partials.flash')

        @if ($dispatches->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                </div>
                <p class="dash-empty">{{ __('No abattoir dispatches yet.') }}</p>
                <a href="{{ route('sales.abattoir.create') }}" class="dash-btn-save">{{ __('New dispatch') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Dispatch') }}</th>
                                <th>{{ __('Farm') }}</th>
                                <th>{{ __('Abattoir') }}</th>
                                <th>{{ __('Animals') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dispatches as $dispatch)
                                @php
                                    $statusTone = match (strtolower((string) $dispatch->dispatch_status)) {
                                        'completed', 'returned', 'closed' => 'ok',
                                        'in_transit', 'dispatched', 'pending' => 'warn',
                                        'cancelled' => 'bad',
                                        default => 'muted',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'animal', 'tone' => $statusTone])
                                            <div class="health-table__stack">
                                                <a href="{{ route('sales.abattoir.show', $dispatch) }}" class="health-table__title health-table__title--link">{{ $dispatch->dispatch_code }}</a>
                                                <span class="health-table__meta">{{ $dispatch->dispatch_date->format('M j, Y') }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $dispatch->farm?->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $dispatch->abattoir_name }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $dispatch->total_animals_dispatched }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $statusTone }}">{{ ucfirst($dispatch->dispatch_status) }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        <div class="health-table__action-btns">
                                            <a href="{{ route('sales.abattoir.show', $dispatch) }}" class="dash-btn-save dash-btn-save--sm">{{ __('View') }}</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $dispatches->links() }}</div>
        @endif
    </div>
@endsection
