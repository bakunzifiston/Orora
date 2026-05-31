@extends('layouts.finance-module')

@section('title', 'Finance — P&L')

@section('finance-content')
    @include('modules.partials.header', [
        'title' => 'Profit & loss',
        'subtitle' => 'Income and expenses from completed sales and paid expenses.',
    ])
    @include('modules.partials.flash')
    @include('modules.finance.partials.filters')

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div><div class="dash-stat-label">Total income</div><div class="dash-stat-value">{{ number_format($report['total_income'], 0) }} RWF</div></div>
            @include('modules.partials.stat-icon', ['icon' => 'sale'])
        </div>
        <div class="dash-stat-card">
            <div><div class="dash-stat-label">Total expenses</div><div class="dash-stat-value">{{ number_format($report['total_expenses'], 0) }} RWF</div></div>
            @include('modules.partials.stat-icon', ['icon' => 'expense'])
        </div>
        <div class="dash-stat-card">
            <div><div class="dash-stat-label">Net income</div><div class="dash-stat-value accent">{{ number_format($report['net_income'], 0) }} RWF</div></div>
            @include('modules.partials.stat-icon', ['icon' => 'chart'])
        </div>
    </div>

    <div class="dash-health-grid">
        <div class="dash-panel">
            <div class="dash-panel-title">Income</div>
            @if ($report['income']->isEmpty())
                <p class="dash-empty">No income in this period.</p>
            @else
                <table class="dash-table">
                    <thead><tr><th>Account</th><th>Amount</th></tr></thead>
                    <tbody>
                        @foreach ($report['income'] as $row)
                            <tr>
                                <td>{{ $row['account_code'] }} — {{ $row['account_name'] }}</td>
                                <td>{{ number_format($row['amount'], 0) }} RWF</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <div class="dash-panel">
            <div class="dash-panel-title">Expenses</div>
            @if ($report['expenses']->isEmpty())
                <p class="dash-empty">No expenses in this period.</p>
            @else
                <table class="dash-table">
                    <thead><tr><th>Account</th><th>Amount</th></tr></thead>
                    <tbody>
                        @foreach ($report['expenses'] as $row)
                            <tr>
                                <td>{{ $row['account_code'] }} — {{ $row['account_name'] }}</td>
                                <td>{{ number_format($row['amount'], 0) }} RWF</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
