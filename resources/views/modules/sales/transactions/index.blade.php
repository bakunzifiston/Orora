@extends('layouts.sales-module')

@section('title', 'Sales — Transactions')

@section('sales-content')
    @include('modules.partials.header', [
        'title' => 'Sale transactions',
        'subtitle' => 'Animal, meat, and milk sales.',
        'createRoute' => 'sales.transactions.create',
        'createLabel' => '+ New sale',
    ])
    @include('modules.partials.flash')

    <form method="GET" action="{{ route('sales.transactions') }}" class="dash-index-toolbar" style="margin-bottom: 1rem;">
        <div class="dash-form-grid" style="align-items: end;">
            <div class="dash-form-field">
                <label for="filter_type">Type</label>
                <select name="type" id="filter_type">
                    <option value="">All types</option>
                    @foreach (config('modules.sale_type_labels') as $value => $label)
                        <option value="{{ $value }}" @selected($filterType === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="filter_farm">Farm</label>
                <select name="farm_id" id="filter_farm">
                    <option value="">All farms</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" @selected((string) $filterFarmId === (string) $farm->id)>{{ $farm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="filter_status">Status</label>
                <select name="status" id="filter_status">
                    <option value="">All statuses</option>
                    @foreach (config('modules.sale_statuses') as $status)
                        <option value="{{ $status }}" @selected($filterStatus === $status)>{{ config('modules.sale_status_labels.'.$status, ucfirst($status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <button type="submit" class="dash-btn-save">Filter</button>
            </div>
        </div>
    </form>

    <div class="dash-panel">
        @if ($transactions->isEmpty())
            <p class="dash-empty">No sales match your filters.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Sale #</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Farm</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $sale)
                            <tr>
                                <td><strong>{{ $sale->sale_number }}</strong></td>
                                <td>{{ $sale->typeLabel() }}</td>
                                <td>{{ $sale->sale_date->format('M j, Y') }}</td>
                                <td>{{ $sale->farm?->name ?? '—' }}</td>
                                <td>{{ $sale->customer?->display_name ?? '—' }}</td>
                                <td>{{ number_format($sale->total_amount, 0) }} {{ $sale->currency }}</td>
                                <td>{{ ucfirst($sale->payment_status) }}</td>
                                <td>{{ $sale->statusLabel() }}</td>
                                <td><a href="{{ route('sales.transactions.show', $sale) }}">Open</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $transactions->links() }}</div>
        @endif
    </div>
@endsection
