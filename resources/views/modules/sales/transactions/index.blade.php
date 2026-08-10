@extends('layouts.sales-module')

@section('title', __('Sales — Transactions'))

@section('sales-content')
    @include('modules.partials.header', [
        'title' => __('Sale transactions'),
        'subtitle' => __('Animal, meat, and milk sales.'),
        'createRoute' => 'sales.transactions.create',
        'createLabel' => '+ '. __('New sale'),
    ])
    @include('modules.partials.flash')

    <form method="GET" action="{{ route('sales.transactions') }}" class="dash-index-toolbar" style="margin-bottom: 1rem;">
        <div class="dash-form-grid" style="align-items: end;">
            <div class="dash-form-field">
                <label for="filter_type">{{ __('Type') }}</label>
                <select name="type" id="filter_type">
                    <option value="">{{ __('All types') }}</option>
                    @foreach (config('modules.sale_type_labels') as $value => $typeLabel)
                        <option value="{{ $value }}" @selected($filterType === $value)>{{ $typeLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="filter_farm">{{ __('Farm') }}</label>
                <select name="farm_id" id="filter_farm">
                    <option value="">{{ __('All farms') }}</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" @selected((string) $filterFarmId === (string) $farm->id)>{{ $farm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="filter_status">{{ __('Status') }}</label>
                <select name="status" id="filter_status">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (config('modules.sale_statuses') as $status)
                        <option value="{{ $status }}" @selected($filterStatus === $status)>{{ config('modules.sale_status_labels.'.$status, ucfirst($status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <button type="submit" class="dash-btn-save">{{ __('Filter') }}</button>
            </div>
        </div>
    </form>

    <div class="dash-panel">
        @if ($transactions->isEmpty())
            <p class="dash-empty">{{ __('No sales match your filters.') }}</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>{{ __('Sale #') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Farm') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Total') }}</th>
                            <th>{{ __('Payment') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $sale)
                            <tr>
                                <td>
                                    <a href="{{ route('sales.transactions.show', $sale) }}"><strong>{{ $sale->sale_number }}</strong></a>
                                </td>
                                <td>{{ $sale->typeLabel() }}</td>
                                <td>{{ $sale->sale_date->format('M j, Y') }}</td>
                                <td>{{ $sale->farm?->name ?? '—' }}</td>
                                <td>{{ $sale->customer?->display_name ?? '—' }}</td>
                                <td>{{ number_format($sale->total_amount, 0) }} {{ $sale->currency }}</td>
                                <td>@include('modules.sales.partials.payment-status-badge', ['sale' => $sale])</td>
                                <td>@include('modules.sales.partials.sale-status-badge', ['sale' => $sale])</td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $sale,
                                        'showRoute' => 'sales.transactions.show',
                                        'destroyRoute' => 'sales.transactions.destroy',
                                        'canDelete' => in_array($sale->sale_status, ['draft', 'cancelled'], true),
                                        'deleteConfirm' => __('Delete this sale permanently?'),
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $transactions->links() }}</div>
        @endif
    </div>
@endsection
