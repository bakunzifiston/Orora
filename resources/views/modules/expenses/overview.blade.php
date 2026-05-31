@extends('layouts.expenses-module')

@section('title', 'Expenses — Overview')

@section('expense-content')
    @include('modules.partials.header', [
        'title' => 'Expenses overview',
        'subtitle' => 'Feed, health, farm operations, and general costs this month.',
        'createRoute' => 'expenses.records.create',
        'createLabel' => '+ Add expense',
    ])
    @include('modules.partials.flash')

    <div class="dash-health-stats">
        <div class="dash-stat-card">
            <div class="dash-stat-label">Total this month</div>
            <div class="dash-stat-value">{{ number_format($stats['month_total'], 0) }} RWF</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Records</div>
            <div class="dash-stat-value accent">{{ number_format($stats['record_count']) }}</div>
        </div>
        <a href="{{ route('expenses.records', ['group' => 'feed']) }}" class="dash-stat-card" style="text-decoration: none; color: inherit;">
            <div class="dash-stat-label">Feed expenses</div>
            <div class="dash-stat-value">{{ number_format($stats['feed'], 0) }} RWF</div>
        </a>
        <a href="{{ route('expenses.records', ['group' => 'health']) }}" class="dash-stat-card" style="text-decoration: none; color: inherit;">
            <div class="dash-stat-label">Health expenses</div>
            <div class="dash-stat-value">{{ number_format($stats['health'], 0) }} RWF</div>
        </a>
        <a href="{{ route('expenses.records', ['group' => 'farm_operations']) }}" class="dash-stat-card" style="text-decoration: none; color: inherit;">
            <div class="dash-stat-label">Farm operations</div>
            <div class="dash-stat-value">{{ number_format($stats['farm_operations'], 0) }} RWF</div>
        </a>
        <a href="{{ route('expenses.records', ['group' => 'general']) }}" class="dash-stat-card" style="text-decoration: none; color: inherit;">
            <div class="dash-stat-label">General / other</div>
            <div class="dash-stat-value">{{ number_format($stats['general'], 0) }} RWF</div>
        </a>
    </div>

    <div class="dash-health-grid">
        <div class="dash-panel">
            <div class="dash-panel-title">Recent expenses</div>
            @if ($recentExpenses->isEmpty())
                <p class="dash-empty">No expenses logged yet.</p>
            @else
                <ul class="dash-health-activity">
                    @foreach ($recentExpenses as $expense)
                        <li>
                            <div>
                                <strong>{{ $expense->category->name }}</strong>
                                <span style="color: #808080;">{{ $expense->expense_date->format('M j') }}</span>
                            </div>
                            <span>{{ number_format($expense->amount, 0) }} {{ $expense->currency }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="dash-panel">
            <div class="dash-panel-title">Top categories (this month)</div>
            @if ($topCategories->isEmpty())
                <p class="dash-empty">No data yet.</p>
            @else
                <ul class="dash-health-activity">
                    @foreach ($topCategories as $row)
                        <li>
                            <strong>{{ $row->name }}</strong>
                            <span>{{ number_format($row->total, 0) }} RWF</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
