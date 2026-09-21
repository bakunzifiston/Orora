@extends('layouts.dashboard')

@section('title', __('Movement'))

@section('content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Movement'),
            'createRoute' => 'movements.create',
            'createLabel' => '+ '.__('Record movement'),
        ])
        @include('modules.partials.flash')

        @if ($movements->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'movement'])
                </div>
                <p class="dash-empty">{{ __('No movement records yet.') }}</p>
                <a href="{{ route('movements.create') }}" class="dash-btn-save">{{ __('Record movement') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Movement') }}</th>
                                <th>{{ __('Animal') }}</th>
                                <th>{{ __('From') }}</th>
                                <th>{{ __('To') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movements as $movement)
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'movement', 'tone' => 'default'])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ ucfirst($movement->movement_type) }}</span>
                                                <span class="health-table__meta">{{ $movement->moved_on->format('M j, Y') }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $movement->stockLabel() }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $movement->fromFarm->name }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $movement->toFarm?->name ?? '—' }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $movement,
                                            'editRoute' => 'movements.edit',
                                            'destroyRoute' => 'movements.destroy',
                                            'deleteConfirm' => __('Delete this movement?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $movements->links() }}</div>
        @endif
    </div>
@endsection
