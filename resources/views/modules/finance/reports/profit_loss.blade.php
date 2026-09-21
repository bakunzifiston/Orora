@extends('layouts.finance-module')

@section('title', __('Finance — P&L'))

@section('finance-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Profit & loss'),
            'secondaryLinks' => [
                [
                    'route' => 'finance.reports.cash_flow',
                    'params' => request()->only(['from', 'to', 'farm_id', 'livestock_id']),
                    'label' => __('Cash flow'),
                    'class' => 'dash-farm-card__btn',
                ],
            ],
        ])
        @include('modules.partials.flash')
        @include('modules.finance.partials.filters')

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--3">
                <div class="farm-kpi farm-kpi--revenue">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Total income') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($report['total_income'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--expense">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'expense'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Total expenses') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($report['total_expenses'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--profit {{ $report['net_income'] < 0 ? 'farm-kpi--negative' : '' }}">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'chart'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Net income') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($report['net_income'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="farm-dash__charts farm-dash__charts--2">
            <section class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Income') }}</h2>
                    </div>
                </header>
                @if ($report['income']->isEmpty())
                    <p class="dash-empty">{{ __('No income in this period.') }}</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="health-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Account') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($report['income'] as $row)
                                    <tr>
                                        <td>
                                            <div class="health-table__primary">
                                                @include('modules.health.partials.table-icon', ['icon' => 'sale', 'tone' => 'ok'])
                                                <div class="health-table__stack">
                                                    <span class="health-table__title">{{ $row['account_name'] }}</span>
                                                    <span class="health-table__meta">{{ $row['account_code'] }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="health-table__value">{{ number_format($row['amount'], 0) }} <span class="health-table__meta">RWF</span></span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Expenses') }}</h2>
                    </div>
                </header>
                @if ($report['expenses']->isEmpty())
                    <p class="dash-empty">{{ __('No expenses in this period.') }}</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="health-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Account') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($report['expenses'] as $row)
                                    <tr>
                                        <td>
                                            <div class="health-table__primary">
                                                @include('modules.health.partials.table-icon', ['icon' => 'expense', 'tone' => 'warn'])
                                                <div class="health-table__stack">
                                                    <span class="health-table__title">{{ $row['account_name'] }}</span>
                                                    <span class="health-table__meta">{{ $row['account_code'] }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="health-table__value">{{ number_format($row['amount'], 0) }} <span class="health-table__meta">RWF</span></span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
