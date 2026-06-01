@extends('layouts.sales-module')

@section('title', 'Sales — Overview')

@section('sales-content')
    @include('modules.partials.header', [
        'title' => 'Sales overview',
        'subtitle' => 'Revenue, balances, and recent transactions this month.',
        'createRoute' => 'sales.transactions.create',
        'createLabel' => '+ New sale',
    ])
    @include('modules.partials.flash')

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Revenue this month</div>
                <div class="dash-stat-value">{{ number_format($stats['month_total'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'sale'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Transactions</div>
                <div class="dash-stat-value accent">{{ number_format($stats['transaction_count']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'chart'])
        </div>
        <a href="{{ route('sales.transactions', ['status' => 'draft']) }}" class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Outstanding balance</div>
                <div class="dash-stat-value">{{ number_format($stats['unpaid_balance'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'expense'])
        </a>
        <a href="{{ route('sales.transactions', ['type' => 'animal_sale']) }}" class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Animal sales</div>
                <div class="dash-stat-value">{{ number_format($stats['animal'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'animal'])
        </a>
        <a href="{{ route('sales.transactions', ['type' => 'meat_sale']) }}" class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Meat sales</div>
                <div class="dash-stat-value">{{ number_format($stats['meat'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'livestock'])
        </a>
        <a href="{{ route('sales.transactions', ['type' => 'milk_sale']) }}" class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Milk sales</div>
                <div class="dash-stat-value">{{ number_format($stats['milk'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'milk'])
        </a>
    </div>

    <div class="dash-panel">
        <div class="dash-panel-title">Recent transactions</div>
        @if ($recent->isEmpty())
            <p class="dash-empty">No sales recorded yet. <a href="{{ route('sales.transactions.create') }}">Create a sale</a>.</p>
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
