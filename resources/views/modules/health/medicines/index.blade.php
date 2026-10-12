@extends('layouts.health-module')

@section('title', __('Health — Medicines'))

@section('health-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Medicines'),
            'createRoute' => 'health.medicines.create',
            'createLabel' => '+ '.__('Add medicine'),
        ])
        @include('modules.partials.flash')

        @if (($alerts['low_stock'] ?? 0) > 0 || ($alerts['expired'] ?? 0) > 0)
            <div class="dash-panel" style="margin-bottom: 1rem;">
                <div class="dash-form-grid">
                    @if (($alerts['low_stock'] ?? 0) > 0)
                        <div class="farm-kpi farm-kpi--receivable">
                            <div class="farm-kpi__body">
                                <div class="farm-kpi__label">{{ __('Low stock') }}</div>
                                <div class="farm-kpi__value">{{ number_format($alerts['low_stock']) }}</div>
                            </div>
                        </div>
                    @endif
                    @if (($alerts['expired'] ?? 0) > 0)
                        <div class="farm-kpi farm-kpi--expense">
                            <div class="farm-kpi__body">
                                <div class="farm-kpi__label">{{ __('Expired medicines') }}</div>
                                <div class="farm-kpi__value">{{ number_format($alerts['expired']) }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if ($medicines->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                </div>
                <p class="dash-empty">{{ __('No medicines yet.') }}</p>
                <a href="{{ route('health.medicines.create') }}" class="dash-btn-save">{{ __('Add medicine') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Medicine') }}</th>
                                <th>{{ __('Stock') }}</th>
                                <th>{{ __('Expiry') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($medicines as $medicine)
                                @php
                                    $isExpired = $medicine->isExpired();
                                    $isLow = $medicine->isLowStock();
                                    $statusTone = $isExpired ? 'bad' : ($isLow ? 'warn' : ($medicine->is_active ? 'ok' : 'muted'));
                                    $statusLabel = $isExpired
                                        ? __('Expired')
                                        : ($isLow ? __('Low stock') : ($medicine->is_active ? __('In stock') : __('Inactive')));
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'health', 'tone' => $statusTone])
                                            <div class="health-table__stack">
                                                <a href="{{ route('health.medicines.show', $medicine) }}" class="health-table__title">{{ $medicine->name }}</a>
                                                <span class="health-table__meta">
                                                    {{ $medicine->category ?? __('Uncategorized') }}
                                                    @if ($medicine->supplier)
                                                        <span aria-hidden="true">·</span> {{ $medicine->supplier }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="health-table__stack">
                                            <span class="health-table__value">{{ $medicine->stockLabel() }}</span>
                                            <span class="health-table__meta">
                                                {{ __('Reorder') }}: {{ $medicine->reorder_level ?? '—' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $medicine->expiry_date?->format('M j, Y') ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $statusTone }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $medicine,
                                            'editRoute' => 'health.medicines.edit',
                                            'destroyRoute' => 'health.medicines.destroy',
                                            'deleteConfirm' => __('Delete this medicine?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $medicines->links() }}</div>
        @endif
    </div>
@endsection
