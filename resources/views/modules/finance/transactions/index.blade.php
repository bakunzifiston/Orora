@extends('layouts.finance-module')

@section('title', 'Finance — Transactions')

@section('finance-content')
    @include('modules.partials.header', [
        'title' => 'Finance transactions',
        'subtitle' => 'Double-entry ledger — auto-synced from sales and expenses.',
    ])
    @include('modules.partials.flash')
    @include('modules.finance.partials.filters')

    <div class="dash-panel">
        @if ($transactions->isEmpty())
            <p class="dash-empty">No transactions in this period.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Farm</th>
                            <th>Source</th>
                            <th>Debit / Credit</th>
                            <th>Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $entry)
                            <tr>
                                <td>{{ $entry->transaction_date->format('M j, Y') }}</td>
                                <td>{{ $entry->transaction_code }}</td>
                                <td>
                                    {{ $entry->description }}
                                    @if ($entry->is_reversal)
                                        <span style="color:#808080;">(reversal)</span>
                                    @endif
                                </td>
                                <td>{{ $entry->farm?->name ?? '—' }}</td>
                                <td>{{ $entry->source_module }}.{{ $entry->source_type }}</td>
                                <td style="font-size:0.8125rem;">
                                    @foreach ($entry->lines as $line)
                                        <div>{{ $line->account->account_code }} {{ ucfirst($line->entry_type) }} {{ number_format($line->amount, 0) }}</div>
                                    @endforeach
                                </td>
                                <td>{{ number_format($entry->net_amount, 0) }} {{ $entry->currency }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $transactions->links() }}</div>
        @endif
    </div>
@endsection
