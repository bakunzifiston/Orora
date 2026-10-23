@extends('layouts.sales-module')

@section('title', __('Sales — Transactions'))

@section('sales-content')
    <div class="farm-dash farms-page sales-page">
        @include('modules.partials.header', [
            'title' => __('Transactions'),
            'createRoute' => 'sales.transactions.create',
            'createLabel' => '+ '. __('New sale'),
        ])
        @include('modules.partials.flash')

        @php
            $filtersActive = filled($filterType) || filled($filterFarmId) || filled($filterStatus);
        @endphp

        <form method="GET" action="{{ route('sales.transactions') }}" class="dash-ops-toolbar farms-page__toolbar">
            <div class="dash-ops-toolbar__controls farms-page__filters">
                <div class="dash-ops-field">
                    <label for="filter_type">{{ __('Type') }}</label>
                    <select name="type" id="filter_type" onchange="this.form.submit()">
                        <option value="">{{ __('All types') }}</option>
                        @foreach ($saleTypes as $value)
                            <option value="{{ $value }}" @selected($filterType === $value)>{{ __(config('modules.sale_type_labels.'.$value, $value)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-ops-field">
                    <label for="filter_farm">{{ __('Farm') }}</label>
                    <select name="farm_id" id="filter_farm" onchange="this.form.submit()">
                        <option value="">{{ __('All farms') }}</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" @selected((string) $filterFarmId === (string) $farm->id)>{{ $farm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-ops-field">
                    <label for="filter_status">{{ __('Status') }}</label>
                    <select name="status" id="filter_status" onchange="this.form.submit()">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (config('modules.sale_statuses') as $status)
                            <option value="{{ $status }}" @selected($filterStatus === $status)>{{ config('modules.sale_status_labels.'.$status, ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="dash-btn-save dash-ops-apply">{{ __('Apply') }}</button>
                @if ($filtersActive)
                    <a href="{{ route('sales.transactions') }}" class="dash-btn-cancel">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>

        @if ($transactions->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                </div>
                @if ($filtersActive)
                    <p class="dash-empty">{{ __('No sales match your filters.') }}</p>
                    <a href="{{ route('sales.transactions') }}" class="dash-btn-cancel">{{ __('Clear filters') }}</a>
                @else
                    <p class="dash-empty">{{ __('No sales recorded yet.') }}</p>
                    <a href="{{ route('sales.transactions.create') }}" class="dash-btn-save">{{ __('New sale') }}</a>
                @endif
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Sale') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Quantity') }}</th>
                                <th>{{ __('Total') }}</th>
                                <th>{{ __('Payment') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $sale)
                                @php
                                    $statusTone = match ($sale->sale_status) {
                                        'completed' => 'ok',
                                        'confirmed', 'draft' => 'warn',
                                        'cancelled' => 'bad',
                                        default => 'muted',
                                    };
                                    $paymentTone = match ($sale->payment_status) {
                                        'paid' => 'ok',
                                        'partial' => 'warn',
                                        'unpaid' => 'bad',
                                        default => 'muted',
                                    };
                                    $canDelete = in_array($sale->sale_status, ['draft', 'cancelled'], true);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'sale', 'tone' => $statusTone])
                                            <div class="health-table__stack">
                                                <a href="{{ route('sales.transactions.show', $sale) }}" class="health-table__title health-table__title--link">{{ $sale->sale_number }}</a>
                                                <span class="health-table__meta">{{ $sale->sale_date->format('M j, Y') }}@if ($sale->farm) · {{ $sale->farm->name }}@endif</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $typeIcon = match ($sale->sale_type) {
                                                'animal_sale' => 'animal',
                                                'meat_sale' => 'sale',
                                                'milk_sale' => 'milk',
                                                'egg_sale' => 'box',
                                                default => 'sale',
                                            };
                                        @endphp
                                        <span class="sale-type sale-type--{{ $sale->sale_type }}">
                                            @include('layouts.partials.dashboard-nav-icon', ['icon' => $typeIcon])
                                            {{ $sale->typeLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $sale->customer?->display_name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        @php
                                            // Total per unit, e.g. "3 head" or "120 L · 4 kg" for mixed sales.
                                            $quantities = $sale->items
                                                ->groupBy(fn ($item) => $item->unit ?: '')
                                                ->map(fn ($items, $unit) => rtrim(rtrim(number_format((float) $items->sum('quantity'), 2), '0'), '.').($unit !== '' ? ' '.$unit : ''))
                                                ->values();
                                        @endphp
                                        @if ($quantities->isEmpty())
                                            <span class="health-table__meta">{{ __('No items') }}</span>
                                        @else
                                            <div class="health-table__stack">
                                                <span class="health-table__value">{{ $quantities->first() }}</span>
                                                @if ($quantities->count() > 1)
                                                    <span class="health-table__meta">{{ $quantities->slice(1)->implode(' · ') }}</span>
                                                @endif
                                                @if ($sale->items->count() > 1)
                                                    <span class="health-table__meta">{{ trans_choice('{2,*} :count lines', $sale->items->count(), ['count' => $sale->items->count()]) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="health-table__value">
                                            {{ number_format($sale->total_amount, 0) }}
                                            <span class="health-table__meta">{{ $sale->currency }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $paymentTone }}">{{ $sale->paymentStatusLabel() }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $statusTone }}">{{ $sale->statusLabel() }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        <div class="health-table__action-btns">
                                            <a href="{{ route('sales.transactions.show', $sale) }}" class="dash-farm-card__btn">{{ __('View') }}</a>
                                            @if ($canDelete)
                                                <form method="POST" action="{{ route('sales.transactions.destroy', $sale) }}" onsubmit="return confirm(@js(__('Delete this sale permanently?')));">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dash-farm-card__btn dash-farm-card__btn--danger">{{ __('Delete') }}</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $transactions->links() }}</div>
        @endif
    </div>
@endsection

@push('scripts')
    <style>
        .sale-type { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.78rem; font-weight: 700; white-space: nowrap; background: #f3f4f6; color: #374151; }
        .sale-type svg { width: 14px; height: 14px; }
        .sale-type--animal_sale { background: rgba(164, 212, 0, 0.18); color: #3f5200; }
        .sale-type--meat_sale { background: #fdf3f2; color: #9f1d1d; }
        .sale-type--milk_sale { background: #eff6ff; color: #1e40af; }
        .sale-type--egg_sale { background: #fff7e6; color: #8a5300; }
    </style>
@endpush
