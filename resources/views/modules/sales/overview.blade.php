@extends('layouts.sales-module')

@section('title', __('Sales — Overview'))

@section('sales-content')
    <div class="farm-dash farms-page sales-page">
        @include('modules.partials.header', [
            'title' => __('Sales'),
            'createRoute' => 'sales.transactions.create',
            'createLabel' => '+ '. __('New sale'),
        ])
        @include('modules.partials.flash')

        <form method="GET" action="{{ route('sales.overview') }}" class="dash-ops-toolbar farms-page__toolbar">
            <div class="dash-ops-toolbar__controls farms-page__filters">
                <div class="dash-ops-field">
                    <label for="sales_filter_period">{{ __('Period') }}</label>
                    <select name="period" id="sales_filter_period" onchange="this.form.submit()">
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

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <div class="farm-kpi farm-kpi--sales">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Revenue') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['period_total'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'chart'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Transactions') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['transaction_count']) }}</div>
                    </div>
                </div>
                <a href="{{ route('sales.transactions', ['status' => 'draft']) }}" class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'expense'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Outstanding') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['unpaid_balance'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </a>
                <a href="{{ route('sales.transactions', ['type' => 'animal_sale', 'from' => $filters['from'], 'to' => $filters['to']]) }}" class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Animal sales') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['animal'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </a>
                <a href="{{ route('sales.transactions', ['type' => 'meat_sale', 'from' => $filters['from'], 'to' => $filters['to']]) }}" class="farm-kpi farm-kpi--health">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'livestock'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Meat sales') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['meat'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </a>
                <a href="{{ route('sales.transactions', ['type' => 'milk_sale', 'from' => $filters['from'], 'to' => $filters['to']]) }}" class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'milk'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Milk sales') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['milk'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </a>
                <a href="{{ route('sales.transactions', ['type' => 'egg_sale', 'from' => $filters['from'], 'to' => $filters['to']]) }}" class="farm-kpi farm-kpi--expense">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'milk'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Egg sales') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['eggs'] ?? 0, 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </a>
            </div>
        </section>

        <section class="farm-panel">
            <header class="farm-panel__head">
                <div>
                    <h2 class="farm-panel__title">{{ __('Recent transactions') }}</h2>
                    @if ($recent->isNotEmpty())
                        <p class="farm-panel__desc">{{ $recent->count() }} {{ __('sales') }}</p>
                    @endif
                </div>
            </header>
            @if ($recent->isEmpty())
                <p class="dash-empty">{{ __('No sales recorded yet.') }}</p>
            @else
                <ul class="farm-activity">
                    @foreach ($recent as $sale)
                        @php
                            $statusTone = match ($sale->sale_status) {
                                'completed' => 'ok',
                                'confirmed', 'draft' => 'warn',
                                'cancelled' => 'bad',
                                default => 'muted',
                            };
                        @endphp
                        <li class="farm-activity__item farm-activity__item--plain farm-activity__item--split">
                            <div class="farm-activity__body">
                                <a href="{{ route('sales.transactions.show', $sale) }}" class="farm-activity__title">{{ $sale->sale_number }}</a>
                                <span class="farm-activity__meta">
                                    {{ $sale->typeLabel() }}
                                    · {{ $sale->sale_date->format('M j, Y') }}
                                    @if ($sale->customer)
                                        · {{ $sale->customer->display_name }}
                                    @endif
                                </span>
                            </div>
                            <span class="health-table__pill health-table__pill--{{ $statusTone }}">
                                {{ number_format($sale->total_amount, 0) }} {{ $sale->currency }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
