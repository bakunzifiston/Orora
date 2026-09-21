@extends('layouts.finance-module')

@section('title', __('Finance — Transactions'))

@section('finance-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Transactions'),
            'secondaryLinks' => [
                [
                    'route' => 'finance.reports.profit_loss',
                    'params' => request()->only(['from', 'to', 'farm_id', 'livestock_id']),
                    'label' => __('P&L'),
                    'class' => 'dash-farm-card__btn',
                ],
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

        @if ($transactions->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'finance'])
                </div>
                <p class="dash-empty">{{ __('No transactions in this period.') }}</p>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Entry') }}</th>
                                <th>{{ __('Farm') }}</th>
                                <th>{{ __('Source') }}</th>
                                <th>{{ __('Net') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $entry)
                                @php
                                    $typeTone = match (true) {
                                        (bool) $entry->is_reversal => 'bad',
                                        str_contains(strtolower((string) $entry->source_module), 'sale') => 'ok',
                                        str_contains(strtolower((string) $entry->source_module), 'expense') => 'warn',
                                        default => 'muted',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'finance', 'tone' => $typeTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">
                                                    {{ $entry->description }}
                                                    @if ($entry->is_reversal)
                                                        <span class="health-table__meta">({{ __('reversal') }})</span>
                                                    @endif
                                                </span>
                                                <span class="health-table__meta">
                                                    {{ $entry->transaction_date->format('M j, Y') }}
                                                    <span aria-hidden="true">·</span>
                                                    {{ $entry->transaction_code }}
                                                </span>
                                                @if ($entry->lines->isNotEmpty())
                                                    <span class="health-table__meta">
                                                        @foreach ($entry->lines->take(2) as $line)
                                                            {{ $line->account->account_code }} {{ ucfirst($line->entry_type) }} {{ number_format($line->amount, 0) }}@if (! $loop->last), @endif
                                                        @endforeach
                                                        @if ($entry->lines->count() > 2)
                                                            …
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $entry->farm?->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $typeTone }}">
                                            {{ $entry->source_module }}.{{ $entry->source_type }}
                                        </span>
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
            </div>
            <div class="dash-pagination">{{ $transactions->links() }}</div>
        @endif
    </div>
@endsection
