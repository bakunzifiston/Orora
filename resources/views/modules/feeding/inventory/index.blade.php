@extends('layouts.feeding-module')

@section('title', __('Feeding — Inventory'))

@section('feeding-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Inventory'),
            'createRoute' => 'feeding.inventory.create',
            'createLabel' => '+ '.__('Add inventory'),
        ])
        @include('modules.partials.flash')

        @if ($inventories->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'box'])
                </div>
                <p class="dash-empty">{{ __('No inventory items yet.') }}</p>
                <a href="{{ route('feeding.inventory.create') }}" class="dash-btn-save">{{ __('Add inventory') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Feed type') }}</th>
                                <th>{{ __('Farm') }}</th>
                                <th>{{ __('Quantity in stock') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inventories as $inventory)
                                @php
                                    $isLow = $inventory->isLowStock();
                                    $statusTone = $isLow ? 'warn' : 'ok';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'box', 'tone' => $statusTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $inventory->feedType->name }}</span>
                                                <span class="health-table__meta">
                                                    {{ $inventory->latestPurchase?->expense?->vendor?->name
                                                        ? __('Last bought from: :name', ['name' => $inventory->latestPurchase->expense->vendor->name])
                                                        : ($inventory->feedType->category ?: $inventory->unit) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $inventory->farm->name }}</span>
                                    </td>
                                    <td>
                                        <div class="health-table__stack">
                                            <span class="health-table__value">{{ $inventory->quantity_on_hand }} {{ $inventory->unit }}</span>
                                            <span class="health-table__meta">
                                                {{ __('Reorder at') }}: {{ $inventory->reorder_level !== null ? $inventory->reorder_level.' '.$inventory->unit : '—' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $statusTone }}">
                                            {{ $isLow ? __('Low stock') : __('In stock') }}
                                        </span>
                                    </td>
                                    <td class="health-table__actions">
                                        <a href="{{ route('feeding.inventory.edit', $inventory) }}#record-stock" class="dash-farm-card__btn" style="margin-bottom: 0.35rem; display: inline-block;">+ {{ __('Add stock') }}</a>
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $inventory,
                                            'editRoute' => 'feeding.inventory.edit',
                                            'destroyRoute' => 'feeding.inventory.destroy',
                                            'deleteConfirm' => __('Delete this inventory item?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $inventories->links() }}</div>
        @endif
    </div>
@endsection
