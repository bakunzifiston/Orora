@extends('layouts.feeding-module')

@section('title', __('Feeding — Suppliers'))

@section('feeding-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Suppliers'),
            'createRoute' => 'feeding.suppliers.create',
            'createLabel' => '+ '.__('Add supplier'),
        ])
        @include('modules.partials.flash')

        @if ($suppliers->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'farm'])
                </div>
                <p class="dash-empty">{{ __('No suppliers yet.') }}</p>
                <a href="{{ route('feeding.suppliers.create') }}" class="dash-btn-save">{{ __('Add supplier') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Supplier') }}</th>
                                <th>{{ __('Contact') }}</th>
                                <th>{{ __('Feed types') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suppliers as $supplier)
                                @php
                                    $statusTone = $supplier->is_active ? 'ok' : 'muted';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'farm', 'tone' => $statusTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $supplier->name }}</span>
                                                @if ($supplier->phone)
                                                    <span class="health-table__meta">{{ $supplier->phone }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $supplier->contact_person ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ number_format($supplier->feed_types_count) }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $statusTone }}">
                                            {{ $supplier->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $supplier,
                                            'editRoute' => 'feeding.suppliers.edit',
                                            'destroyRoute' => 'feeding.suppliers.destroy',
                                            'deleteConfirm' => __('Delete this supplier?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $suppliers->links() }}</div>
        @endif
    </div>
@endsection
