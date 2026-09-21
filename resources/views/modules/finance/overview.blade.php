@extends('layouts.finance-module')

@section('title', __('Finance — Overview'))

@section('finance-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Finance'),
            'secondaryLinks' => [
                [
                    'route' => 'finance.transactions',
                    'params' => request()->only(['from', 'to', 'farm_id', 'livestock_id']),
                    'label' => __('Transactions'),
                    'class' => 'dash-farm-card__btn',
                ],
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
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <a href="{{ route('finance.reports.profit_loss', request()->only(['from', 'to', 'farm_id', 'livestock_id'])) }}" class="farm-kpi farm-kpi--revenue">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Revenue') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['revenue'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </a>
                <a href="{{ route('finance.reports.profit_loss', request()->only(['from', 'to', 'farm_id', 'livestock_id'])) }}" class="farm-kpi farm-kpi--expense">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'expense'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Expenses') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['expenses'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </a>
                <div class="farm-kpi farm-kpi--profit {{ $stats['net_income'] < 0 ? 'farm-kpi--negative' : '' }}">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'chart'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Net income') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['net_income'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('finance.reports.cash_flow', request()->only(['from', 'to', 'farm_id', 'livestock_id'])) }}" class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'finance'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Cash change') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['cash_change'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </a>
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'customer'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Receivable') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['accounts_receivable'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('finance.transactions', request()->only(['from', 'to', 'farm_id', 'livestock_id'])) }}" class="farm-kpi farm-kpi--sales">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'finance'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Ledger entries') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['transaction_count']) }}</div>
                    </div>
                </a>
            </div>
        </section>

        <section class="farm-panel">
            <header class="farm-panel__head">
                <div>
                    <h2 class="farm-panel__title">{{ __('Recent ledger entries') }}</h2>
                    <p class="farm-panel__desc">{{ __('Auto-posted from completed sales and paid expenses') }}</p>
                </div>
            </header>

            @if ($recent->isEmpty())
                <div class="dash-panel dash-entity-empty" style="border: 0; box-shadow: none;">
                    <div class="dash-entity-empty__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'finance'])
                    </div>
                    <p class="dash-empty">{{ __('No finance entries yet.') }}</p>
                </div>
            @else
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Entry') }}</th>
                                <th>{{ __('Farm') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recent as $entry)
                                @php
                                    $typeTone = match (strtolower((string) $entry->transaction_type)) {
                                        'income', 'revenue', 'receipt' => 'ok',
                                        'expense', 'payment' => 'warn',
                                        'reversal' => 'bad',
                                        default => 'muted',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'finance', 'tone' => $typeTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $entry->description }}</span>
                                                <span class="health-table__meta">
                                                    {{ $entry->transaction_date->format('M j, Y') }}
                                                    <span aria-hidden="true">·</span>
                                                    {{ $entry->transaction_code }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $entry->farm?->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $typeTone }}">{{ ucfirst($entry->transaction_type) }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">
                                            {{ number_format($entry->net_amount, 0) }}
                                            <span class="health-table__meta">{{ $entry->currency }}</span>
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
