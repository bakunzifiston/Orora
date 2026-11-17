@extends('layouts.sales-module')

@section('title', __('Sales — Reports'))

@section('sales-content')
    <div class="farm-dash farms-page sales-page">
        @include('modules.partials.header', ['title' => __('Sales report')])
        @include('modules.partials.flash')

        <form method="GET" action="{{ route('sales.reports') }}" class="dash-panel" style="margin-bottom: 1.25rem;">
            <div class="dash-form-grid">
                <div class="dash-form-field">
                    <label for="from">{{ __('From') }}</label>
                    <input type="date" name="from" id="from" value="{{ $filters['from'] ?? '' }}">
                </div>
                <div class="dash-form-field">
                    <label for="to">{{ __('To') }}</label>
                    <input type="date" name="to" id="to" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="dash-form-field">
                    <label for="farm_id">{{ __('Farm') }}</label>
                    <select name="farm_id" id="farm_id">
                        <option value="">{{ __('All farms') }}</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" @selected((string) ($filters['farm_id'] ?? '') === (string) $farm->id)>{{ $farm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="customer_id">{{ __('Customer') }}</label>
                    <select name="customer_id" id="customer_id">
                        <option value="">{{ __('All customers') }}</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected((string) ($filters['customer_id'] ?? '') === (string) $customer->id)>{{ $customer->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="type">{{ __('Type') }}</label>
                    <select name="type" id="type">
                        <option value="">{{ __('All types') }}</option>
                        @foreach ($saleTypes as $value)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ __(config('modules.sale_type_labels.'.$value, $value)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="status">{{ __('Sale status') }}</label>
                    <select name="status" id="status">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (config('modules.sale_statuses') as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ config('modules.sale_status_labels.'.$status, ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="payment_status">{{ __('Payment status') }}</label>
                    <select name="payment_status" id="payment_status">
                        <option value="">{{ __('All payment statuses') }}</option>
                        @foreach (config('modules.sale_payment_statuses') as $ps)
                            <option value="{{ $ps }}" @selected(($filters['payment_status'] ?? '') === $ps)>{{ config('modules.sale_payment_status_labels.'.$ps, ucfirst($ps)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if ($errors->any())
                <p class="dash-error" style="color:#b91c1c;">{{ $errors->first() }}</p>
            @endif
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 1rem;">
                <button type="submit" class="dash-btn-save">{{ __('Apply filters') }}</button>
                <a href="{{ route('sales.reports') }}" class="dash-btn-cancel">{{ __('Clear') }}</a>
                <button type="submit" formaction="{{ route('sales.reports.pdf') }}" class="dash-btn-save">{{ __('Export PDF') }}</button>
            </div>
        </form>

        @forelse ($summary as $currency => $row)
            <div class="dash-health-stats" style="margin-bottom: 1rem;">
                <div class="dash-stat-card"><div><div class="dash-stat-label">{{ __('Sales') }}</div><div class="dash-stat-value">{{ number_format($row['count']) }}</div></div></div>
                <div class="dash-stat-card"><div><div class="dash-stat-label">{{ __('Total') }} ({{ $currency }})</div><div class="dash-stat-value">{{ number_format($row['total'], 0) }}</div></div></div>
                <div class="dash-stat-card"><div><div class="dash-stat-label">{{ __('Paid') }} ({{ $currency }})</div><div class="dash-stat-value accent">{{ number_format($row['paid'], 0) }}</div></div></div>
                <div class="dash-stat-card"><div><div class="dash-stat-label">{{ __('Balance') }} ({{ $currency }})</div><div class="dash-stat-value">{{ number_format($row['balance'], 0) }}</div></div></div>
            </div>
        @empty
            <div class="dash-panel"><p class="dash-empty">{{ __('No sales match your filters.') }}</p></div>
        @endforelse

        @if ($preview->isNotEmpty())
            <div class="dash-panel">
                <div class="dash-panel-title">{{ __('Preview') }}</div>
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th><th>{{ __('Sale') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Type') }}</th>
                            <th>{{ __('Status') }}</th><th>{{ __('Payment') }}</th><th>{{ __('Total') }}</th><th>{{ __('Paid') }}</th><th>{{ __('Balance') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($preview as $sale)
                            <tr>
                                <td>{{ $sale->sale_date->format('M j, Y') }}</td>
                                <td><a href="{{ route('sales.transactions.show', $sale) }}">{{ $sale->sale_number }}</a></td>
                                <td>{{ $sale->customer?->display_name ?? '—' }}</td>
                                <td>{{ $sale->typeLabel() }}</td>
                                <td>{{ $sale->statusLabel() }}</td>
                                <td>{{ config('modules.sale_payment_status_labels.'.$sale->payment_status, ucfirst((string) $sale->payment_status)) }}</td>
                                <td>{{ number_format($sale->total_amount, 0) }} {{ $sale->currency }}</td>
                                <td>{{ number_format((float) $sale->paid_total, 0) }}</td>
                                <td>{{ number_format(max(0, (float) $sale->total_amount - (float) $sale->paid_total), 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($previewTruncated)
                    <p class="dash-empty" style="margin-top: .5rem;">{{ __('Showing the first 50 sales. The PDF contains all matching sales.') }}</p>
                @endif
            </div>
        @endif
    </div>
@endsection
