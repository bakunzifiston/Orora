@extends('layouts.milk-module')

@section('title', __('Milk — Storage'))

@section('milk-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Milk storage'),
            'createRoute' => 'milk.storage.create',
            'createLabel' => '+ '.__('Add container'),
        ])
        @include('modules.partials.flash')

        @if ($storageUnits->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'box'])
                </div>
                <p class="dash-empty">{{ __('No storage containers yet.') }}</p>
                <a href="{{ route('milk.storage.create') }}" class="dash-btn-save">{{ __('Add container') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Container') }}</th>
                                <th>{{ __('Farm') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Quantity') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($storageUnits as $unit)
                                @php
                                    $statusTone = match (strtolower((string) $unit->status)) {
                                        'available', 'in_use' => 'ok',
                                        'maintenance' => 'warn',
                                        'full' => 'muted',
                                        default => 'muted',
                                    };
                                    if ($unit->isLowCapacity() && $statusTone === 'ok') {
                                        $statusTone = 'warn';
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'box', 'tone' => $statusTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $unit->container_name }}</span>
                                                <span class="health-table__meta">{{ $unit->storage_code }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $unit->farm->name }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ ucfirst(str_replace('_', ' ', $unit->container_type)) }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">
                                            {{ number_format($unit->current_quantity_liters, 2) }} / {{ number_format($unit->capacity_liters, 2) }} L
                                        </span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $statusTone }}">{{ ucfirst(str_replace('_', ' ', $unit->status)) }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $unit,
                                            'editRoute' => 'milk.storage.edit',
                                            'destroyRoute' => 'milk.storage.destroy',
                                            'deleteConfirm' => __('Delete this storage container?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $storageUnits->links() }}</div>
        @endif
    </div>
@endsection
