@extends('layouts.finance-module')

@section('title', 'Finance — Overview')

@section('finance-content')
    @include('modules.partials.header', [
        'title' => 'Finance overview',
    ])
    @include('modules.partials.flash')

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <a href="{{ route('finance.reports.profit_loss', request()->only(['from', 'to', 'farm_id', 'livestock_id'])) }}" class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Revenue</div>
                <div class="dash-stat-value">{{ number_format($stats['revenue'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'sale'])
        </a>
        <a href="{{ route('finance.reports.profit_loss', request()->only(['from', 'to', 'farm_id', 'livestock_id'])) }}" class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Expenses</div>
                <div class="dash-stat-value">{{ number_format($stats['expenses'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'expense'])
        </a>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Net income</div>
                <div class="dash-stat-value accent">{{ number_format($stats['net_income'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'chart'])
        </div>
        <a href="{{ route('finance.reports.cash_flow', request()->only(['from', 'to', 'farm_id', 'livestock_id'])) }}" class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Cash change</div>
                <div class="dash-stat-value">{{ number_format($stats['cash_change'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'finance'])
        </a>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Accounts receivable</div>
                <div class="dash-stat-value">{{ number_format($stats['accounts_receivable'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'customer'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Ledger entries</div>
                <div class="dash-stat-value">{{ number_format($stats['transaction_count']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'finance'])
        </div>
    </div>

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <p style="margin: 0 0 1rem; color: #808080; font-size: 0.875rem;">Auto-posted from completed sales and paid expenses.</p>
        @include('modules.finance.partials.filters')
    </div>

    <div class="dash-panel">
        <div class="dash-panel-title">Recent ledger entries</div>
        @if ($recent->isEmpty())
            <p class="dash-empty">No finance entries yet. Complete a sale or mark an expense as paid. For existing data run: <code>php artisan tenants:run finance:backfill</code></p>
        @else
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Farm</th>
                        <th>Type</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recent as $entry)
                        <tr>
                            <td>{{ $entry->transaction_date->format('M j, Y') }}</td>
                            <td>{{ $entry->transaction_code }}</td>
                            <td>{{ $entry->description }}</td>
                            <td>{{ $entry->farm?->name ?? 'All / —' }}</td>
                            <td>{{ ucfirst($entry->transaction_type) }}</td>
                            <td>{{ number_format($entry->net_amount, 0) }} {{ $entry->currency }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
