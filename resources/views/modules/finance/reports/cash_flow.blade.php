@extends('layouts.finance-module')

@section('title', __('Finance — Cash flow'))

@section('finance-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Cash flow'),
            'secondaryLinks' => [
                [
                    'route' => 'finance.reports.profit_loss',
                    'params' => request()->only(['from', 'to', 'farm_id', 'livestock_id']),
                    'label' => __('P&L'),
                    'class' => 'dash-farm-card__btn',
                ],
            ],
        ])
        @include('modules.partials.flash')
        @include('modules.finance.partials.filters')

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--3">
                <div class="farm-kpi farm-kpi--{{ $report['net_cash_change'] >= 0 ? 'profit' : 'expense' }} {{ $report['net_cash_change'] < 0 ? 'farm-kpi--negative' : '' }}">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'finance'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Net cash change') }}</div>
                        <div class="farm-kpi__value">
                            {{ ($report['net_cash_change'] >= 0 ? '+' : '').number_format($report['net_cash_change'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if ($report['movements']->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'finance'])
                </div>
                <p class="dash-empty">{{ __('No cash movements in this period.') }}</p>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Movement') }}</th>
                                <th>{{ __('Account') }}</th>
                                <th>{{ __('Change') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($report['movements'] as $row)
                                @php
                                    $tone = $row['amount'] >= 0 ? 'ok' : 'warn';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'finance', 'tone' => $tone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $row['description'] }}</span>
                                                <span class="health-table__meta">
                                                    {{ \Carbon\Carbon::parse($row['date'])->format('M j, Y') }}
                                                    <span aria-hidden="true">·</span>
                                                    {{ $row['code'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $row['account'] }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $tone }}">
                                            {{ ($row['amount'] >= 0 ? '+' : '').number_format($row['amount'], 0) }} RWF
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
