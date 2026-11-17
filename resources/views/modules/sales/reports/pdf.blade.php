<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('Sales report') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #1a2e1a; margin: 0; padding: 22px 26px; }
        h1 { color: #1b5e20; font-size: 17px; margin: 0 0 2px; }
        .meta { color: #5f6368; font-size: 8.5px; margin-bottom: 10px; }
        .header { border-bottom: 2px solid #1b5e20; padding-bottom: 8px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        table.filters td { border: none; padding: 1px 12px 1px 0; font-size: 9px; }
        table.filters .k { color: #5f6368; }
        table.data { margin-top: 8px; }
        table.data th, table.data td { border: 1px solid #dde8dd; padding: 3px 5px; text-align: left; vertical-align: top; }
        table.data th { background: #f1f8e9; color: #1b5e20; }
        .num { text-align: right !important; white-space: nowrap; }
        h2 { color: #1b5e20; font-size: 10.5px; text-transform: uppercase; margin: 14px 0 4px; }
        tr { page-break-inside: avoid; }
        .note { color: #b45309; font-size: 9px; margin-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('Sales report') }}</h1>
        <div class="meta">
            {{ __('Generated') }} {{ $generatedAt->format('M j, Y H:i') }}@if ($generatedBy) · {{ $generatedBy }}@endif
        </div>
        <table class="filters">
            @foreach (array_chunk($filterLabels, 3, true) as $chunk)
                <tr>
                    @foreach ($chunk as $label => $value)
                        <td><span class="k">{{ $label }}:</span> <strong>{{ $value }}</strong></td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    </div>

    <h2>{{ __('Summary') }}</h2>
    <table class="data">
        <thead><tr><th>{{ __('Currency') }}</th><th class="num">{{ __('Sales') }}</th><th class="num">{{ __('Total') }}</th><th class="num">{{ __('Paid') }}</th><th class="num">{{ __('Balance') }}</th></tr></thead>
        <tbody>
            @forelse ($summary as $currency => $row)
                <tr>
                    <td>{{ $currency }}</td>
                    <td class="num">{{ number_format($row['count']) }}</td>
                    <td class="num">{{ number_format($row['total'], 0) }}</td>
                    <td class="num">{{ number_format($row['paid'], 0) }}</td>
                    <td class="num">{{ number_format($row['balance'], 0) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">{{ __('No sales match these filters.') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($sales->isNotEmpty())
        <h2>{{ __('Sales') }}</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th><th>{{ __('Sale') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Farm') }}</th><th>{{ __('Type') }}</th>
                    <th>{{ __('Status') }}</th><th>{{ __('Payment') }}</th><th class="num">{{ __('Total') }}</th><th class="num">{{ __('Paid') }}</th><th class="num">{{ __('Balance') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sales as $sale)
                    <tr>
                        <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                        <td>{{ $sale->sale_number }}</td>
                        <td>{{ $sale->customer?->display_name ?? '—' }}</td>
                        <td>{{ $sale->farm?->name ?? '—' }}</td>
                        <td>{{ $sale->typeLabel() }}</td>
                        <td>{{ $sale->statusLabel() }}</td>
                        <td>{{ config('modules.sale_payment_status_labels.'.$sale->payment_status, ucfirst((string) $sale->payment_status)) }}</td>
                        <td class="num">{{ number_format($sale->total_amount, 0) }} {{ $sale->currency }}</td>
                        <td class="num">{{ number_format((float) $sale->paid_total, 0) }}</td>
                        <td class="num">{{ number_format($reports->rowBalance($sale), 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($truncated)
        <p class="note">{{ __('Only the first :n sales are shown. Narrow the filters to see the rest.', ['n' => number_format($sales->count())]) }}</p>
    @endif
</body>
</html>
