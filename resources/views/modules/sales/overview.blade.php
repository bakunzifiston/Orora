@extends('layouts.sales-module')

@section('title', 'Sales — Overview')

@section('sales-content')
    @include('modules.partials.header', [
        'title' => __('Sales overview'),
        'subtitle' => __('Revenue, balances, and recent transactions for :label.', ['label' => $filters['label']]),
        'createRoute' => 'sales.transactions.create',
        'createLabel' => '+ '. __('New sale'),
    ])
    @include('modules.partials.flash')

    <form method="GET" action="{{ route('sales.overview') }}" class="dash-ops-toolbar" style="margin-bottom: 1rem;">
        <div class="dash-ops-toolbar__controls">
            <div class="dash-ops-field">
                <label for="sales_filter_period">{{ __('Period') }}</label>
                <select name="period" id="sales_filter_period">
                    <option value="this_month" @selected($filters['period'] === 'this_month')>{{ __('This month') }}</option>
                    <option value="last_month" @selected($filters['period'] === 'last_month')>{{ __('Last month') }}</option>
                    <option value="this_quarter" @selected($filters['period'] === 'this_quarter')>{{ __('This quarter') }}</option>
                    <option value="this_year" @selected($filters['period'] === 'this_year')>{{ __('This year') }}</option>
                    <option value="custom" @selected($filters['period'] === 'custom')>{{ __('Custom range') }}</option>
                </select>
            </div>
            <div class="dash-ops-field dash-ops-field--dates @if($filters['period'] !== 'custom') dash-ops-field--muted @endif">
                <label>{{ __('Date range') }}</label>
                <div class="dash-ops-dates">
                    <input type="date" name="from" value="{{ $filters['from'] }}" aria-label="{{ __('From date') }}">
                    <span class="dash-ops-dates__sep">→</span>
                    <input type="date" name="to" value="{{ $filters['to'] }}" aria-label="{{ __('To date') }}">
                </div>
            </div>
            <button type="submit" class="dash-btn-save dash-ops-apply">{{ __('Apply') }}</button>
        </div>
    </form>

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Revenue') }} ({{ $filters['label'] }})</div>
                <div class="dash-stat-value">{{ number_format($stats['period_total'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'sale'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Transactions') }}</div>
                <div class="dash-stat-value accent">{{ number_format($stats['transaction_count']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'chart'])
        </div>
        <a href="{{ route('sales.transactions', ['status' => 'draft']) }}" class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Outstanding balance') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['unpaid_balance'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'expense'])
        </a>
        <a href="{{ route('sales.transactions', ['type' => 'animal_sale', 'from' => $filters['from'], 'to' => $filters['to']]) }}" class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Animal sales') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['animal'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'animal'])
        </a>
        <a href="{{ route('sales.transactions', ['type' => 'meat_sale', 'from' => $filters['from'], 'to' => $filters['to']]) }}" class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Meat sales') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['meat'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'livestock'])
        </a>
        <a href="{{ route('sales.transactions', ['type' => 'milk_sale', 'from' => $filters['from'], 'to' => $filters['to']]) }}" class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Milk sales') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['milk'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'milk'])
        </a>
    </div>

    <div class="dash-panel">
        <div class="dash-panel-title">{{ __('Recent transactions') }}</div>
        @if ($recent->isEmpty())
            <p class="dash-empty">{{ __('No sales recorded yet.') }} <a href="{{ route('sales.transactions.create') }}">{{ __('Create a sale') }}</a>.</p>
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
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recent as $sale)
                            <tr>
                                <td>
                                    <a href="{{ route('sales.transactions.show', $sale) }}"><strong>{{ $sale->sale_number }}</strong></a>
                                </td>
                                <td>{{ $sale->typeLabel() }}</td>
                                <td>{{ $sale->sale_date->format('M j, Y') }}</td>
                                <td>{{ $sale->farm?->name ?? '—' }}</td>
                                <td>{{ $sale->customer?->display_name ?? '—' }}</td>
                                <td>{{ number_format($sale->total_amount, 0) }} {{ $sale->currency }}</td>
                                <td>@include('modules.sales.partials.sale-status-badge', ['sale' => $sale])</td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $sale,
                                        'showRoute' => 'sales.transactions.show',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
