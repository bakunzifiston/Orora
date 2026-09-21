@extends('layouts.expenses-module')

@section('title', __('Expenses — Overview'))

@section('expense-content')
    <div class="farm-dash farms-page expenses-page">
        @include('modules.partials.header', [
            'title' => __('Expenses'),
            'createRoute' => 'expenses.records.create',
            'createLabel' => '+ '. __('Add expense'),
        ])
        @include('modules.partials.flash')

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <div class="farm-kpi farm-kpi--expense">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'expense'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Total this month') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['month_total'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'chart'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Records') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['record_count']) }}</div>
                    </div>
                </div>
                <a href="{{ route('expenses.records', ['group' => 'feed']) }}" class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'feeding'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Feed') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['feed'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </a>
                <a href="{{ route('expenses.records', ['group' => 'health']) }}" class="farm-kpi farm-kpi--health">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Health') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['health'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </a>
                <a href="{{ route('expenses.records', ['group' => 'farm_operations']) }}" class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'farm'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Farm operations') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['farm_operations'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </a>
                <a href="{{ route('expenses.records', ['group' => 'general']) }}" class="farm-kpi farm-kpi--sales">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('General') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['general'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </a>
            </div>
        </section>

        <div class="farm-dash__charts farm-dash__charts--2">
            <section class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Recent expenses') }}</h2>
                    </div>
                </header>
                <div class="farm-panel__body">
                    @if ($recentExpenses->isEmpty())
                        <p class="dash-empty">{{ __('No expenses logged yet.') }}</p>
                    @else
                        <ul class="farm-activity">
                            @foreach ($recentExpenses as $expense)
                                <li class="farm-activity__item farm-activity__item--plain farm-activity__item--split">
                                    <div class="farm-activity__body">
                                        <span class="farm-activity__title">{{ $expense->category->name }}</span>
                                        <span class="farm-activity__meta">{{ $expense->expense_date->format('M j, Y') }}</span>
                                    </div>
                                    <span class="farm-activity__count">
                                        {{ number_format($expense->amount, 0) }} {{ $expense->currency }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>

            <section class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Top categories') }}</h2>
                    </div>
                </header>
                <div class="farm-panel__body">
                    @if ($topCategories->isEmpty())
                        <p class="dash-empty">{{ __('No data yet.') }}</p>
                    @else
                        <ul class="farm-activity">
                            @foreach ($topCategories as $row)
                                <li class="farm-activity__item farm-activity__item--plain farm-activity__item--split">
                                    <div class="farm-activity__body">
                                        <span class="farm-activity__title">{{ $row->name }}</span>
                                    </div>
                                    <span class="farm-activity__count">{{ number_format($row->total, 0) }} RWF</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
