@php
    $fmt = fn ($v, $unit = null) => number_format((float) $v, $unit === 'L' ? 1 : 0);
    $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.');
    $amountText = function ($r) use ($fmt) {
        if ($r['amount'] === null) return '';
        $u = $r['unit'] === 'currency' ? ($r['currency'] ?: 'RWF') : $r['unit'];
        return $fmt($r['amount'], $r['unit']).' '.$u;
    };
    $pill = [
        'sales' => ['#e8f5e9', '#1b5e20'], 'expenses' => ['#fdecea', '#b3261e'], 'milk' => ['#e3f2fd', '#0d47a1'],
        'eggs' => ['#fff4cc', '#8a6100'], 'health' => ['#f3e5f5', '#6a1b9a'], 'breeding' => ['#fce4ec', '#ad1457'],
        'feeding' => ['#efebe9', '#4e342e'], 'animals' => ['#e0f2f1', '#00695c'],
    ];
    $modKey = array_flip(\App\Services\ActivityReportService::MODULES);
    $payColor = ['paid' => '#1b7a34', 'partial' => '#b45309', 'unpaid' => '#b3261e', 'overdue' => '#b3261e'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('Activity report') }}</title>
    <style>
        @page { margin: 24px 26px 34px 26px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #111; margin: 0; line-height: 1.25; }
        table { width: 100%; border-collapse: collapse; }
        .banner { background: #14532d; padding: 10px 14px; border-bottom: 3px solid #f2b705; }
        .top td { padding: 0; vertical-align: middle; }
        h1 { font-size: 20px; margin: 0; color: #fff; letter-spacing: .3px; }
        .right { text-align: right; color: #c8e6cf; font-size: 9px; }
        .sub { margin: 8px 0 6px; padding: 6px 10px; background: #eef6ef; border-left: 3px solid #2e7d32; color: #1f3a27; }
        h2 { font-size: 11px; text-transform: uppercase; letter-spacing: .8px; margin: 11px 0 4px; color: #14532d; border-left: 3px solid #f2b705; padding-left: 6px; }
        table.t { table-layout: fixed; }
        table.t th { text-align: left; padding: 4px 5px; background: #1b5e20; color: #fff; font-size: 8.5px; text-transform: uppercase; letter-spacing: .3px; }
        table.t td { padding: 3px 5px; border-bottom: .5px solid #d8e3d9; vertical-align: top; word-wrap: break-word; }
        table.t tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
        .num { text-align: right !important; white-space: nowrap; }
        .m { color: #6b7280; }
        .b { font-weight: bold; }
        table.sum > tbody > tr > td, table.sum > tr > td { width: 25%; vertical-align: top; padding: 0 10px 0 0; }
        table.sum table td { width: auto; }
        .sg { font-weight: bold; text-transform: uppercase; font-size: 9px; margin: 5px 0 2px; padding: 2px 6px; }
        tr.alt td { background: #f4f9f4; }
        .pill { padding: 1px 6px; font-size: 8.5px; font-weight: bold; white-space: nowrap; }
        .sr td { padding: 0.5px 0; border: 0; }
        .tot td { background: #e8f5e9; border-top: 1.5px solid #1b5e20; font-weight: bold; color: #14532d; padding: 3px 4px; }
        .note { margin-top: 5px; color: #92400e; }
    </style>
</head>
<body>
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont('DejaVu Sans');
            $pdf->page_text(26, $pdf->get_height() - 22, 'Orora — {{ __('Activity report') }}', $font, 8, [0.4, 0.4, 0.4]);
            $pdf->page_text($pdf->get_width() - 90, $pdf->get_height() - 22, '{{ __('Page') }} {PAGE_NUM} / {PAGE_COUNT}', $font, 8, [0.4, 0.4, 0.4]);
        }
    </script>

    <div class="banner"><table class="top">
        <tr>
            <td><h1>{{ __('Activity report') }}</h1></td>
            <td class="right">{{ __('Generated') }} {{ $generatedAt->format('Y-m-d H:i') }}@if ($generatedBy) · {{ $generatedBy }}@endif</td>
        </tr>
    </table></div>
    <div class="sub">
        <b>{{ __('Period') }}:</b> {{ $filters['from'] }} → {{ $filters['to'] }} &nbsp;|&nbsp;
        <b>{{ __('Farm') }}:</b> {{ $farmName }} &nbsp;|&nbsp;
        <b>{{ __('Included') }}:</b> {{ $moduleLabels }} &nbsp;|&nbsp;
        <b>{{ __('Activities') }}:</b> {{ number_format($summary->sum('count')) }}
    </div>

    <h2>{{ __('Summary') }}</h2>
    @if ($summary->isEmpty())
        <div class="m">{{ __('No activity found for these filters.') }}</div>
    @else
        <table class="t sumt">
            <thead>
                <tr>
                    <th style="width:28%">{{ __('Module') }}</th><th style="width:34%">{{ __('Activity') }}</th>
                    <th class="num" style="width:13%">{{ __('Count') }}</th><th class="num" style="width:25%">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($summary->groupBy('module') as $module => $items)
                    @php [$gbg, $gfg] = $pill[$modKey[$module] ?? ''] ?? ['#eee', '#333']; @endphp
                    @foreach ($items as $row)
                        <tr>
                            @if ($loop->first)
                                <td rowspan="{{ $items->count() }}" style="background: {{ $gbg }}; color: {{ $gfg }}; font-weight: bold; text-transform: uppercase; font-size: 8.5px; border-bottom: 1.5px solid #fff;">{{ __($module) }}<br><span style="font-weight: normal;">{{ number_format($items->sum('count')) }}</span></td>
                            @endif
                            <td>{{ __($row['type']) }}</td>
                            <td class="num b">{{ number_format($row['count']) }}</td>
                            <td class="num m">{{ $row['total'] !== null ? $fmt($row['total'], $row['unit']).' '.$row['unit'] : '' }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($sales->isNotEmpty())
        <h2>{{ __('Sales details') }}</h2>
        <table class="t">
            
            <thead>
                <tr>
                    <th style="width:6%">{{ __('Date') }}</th><th style="width:14%">{{ __('Sale') }}</th><th style="width:14%">{{ __('Customer') }}</th><th style="width:11%">{{ __('Farm') }}</th><th style="width:19%">{{ __('Items') }}</th>
                    <th class="num" style="width:6%">{{ __('Qty') }}</th><th style="width:9%">{{ __('Payment') }}</th><th class="num" style="width:8%">{{ __('Total') }}</th><th class="num" style="width:7%">{{ __('Paid') }}</th><th class="num" style="width:7%">{{ __('Balance') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sales as $sale)
                    @php
                        $paid = (float) $sale->paid_total;
                        $qtyLabel = $sale->items->groupBy('unit')->map(fn ($g, $u) => $qty($g->sum('quantity')).' '.$u)->implode(', ');
                        $items = $sale->items->map(fn ($i) => $i->description.' ('.$qty($i->quantity).' '.$i->unit.($i->unit_price ? ' @ '.$fmt($i->unit_price) : '').')')->implode('; ');
                    @endphp
                    <tr class="{{ $loop->even ? 'alt' : '' }}">
                        <td>{{ $sale->sale_date->format('y-m-d') }}</td>
                        <td class="b">{{ $sale->sale_number }}</td>
                        <td>{{ $sale->customer?->display_name ?? '—' }}</td>
                        <td class="m">{{ $sale->farm?->name ?? '—' }}</td>
                        <td class="m">{{ $items ?: '—' }}</td>
                        <td class="num">{{ $qtyLabel ?: '—' }}</td>
                        <td style="color: {{ $payColor[$sale->payment_status] ?? '#333' }}; font-weight: bold;">{{ config('modules.sale_payment_status_labels.'.$sale->payment_status, ucfirst((string) $sale->payment_status)) }}@if ($sale->sale_status === 'draft') <span class="m">({{ __('open') }})</span>@endif</td>
                        <td class="num b">{{ $fmt($sale->total_amount) }}</td>
                        <td class="num" style="color: #1b7a34;">{{ $fmt($paid) }}</td>
                        @php $bal = max(0, (float) $sale->total_amount - $paid); @endphp
                        <td class="num" style="color: {{ $bal > 0 ? '#b3261e' : '#6b7280' }}; {{ $bal > 0 ? 'font-weight: bold;' : '' }}">{{ $fmt($bal) }}</td>
                    </tr>
                @endforeach
                @foreach ($sales->groupBy(fn ($s) => $s->currency ?: 'RWF') as $currency => $group)
                    @php $t = (float) $group->sum('total_amount'); $p = (float) $group->sum(fn ($s) => (float) $s->paid_total); @endphp
                    <tr class="tot">
                        <td colspan="7">{{ $currency }} · {{ $group->count() }} {{ __('sales') }}</td>
                        <td class="num">{{ $fmt($t) }}</td><td class="num">{{ $fmt($p) }}</td><td class="num">{{ $fmt(max(0, $t - $p)) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($rows->isNotEmpty())
        <h2>{{ __('Timeline') }}</h2>
        <table class="t">
            
            <thead><tr><th style="width:6%">{{ __('Date') }}</th><th style="width:13%">{{ __('Activity') }}</th><th style="width:46%">{{ __('Details') }}</th><th style="width:17%">{{ __('Farm') }}</th><th class="num" style="width:18%">{{ __('Amount') }}</th></tr></thead>
            <tbody>
                @foreach ($rows as $r)
                    @php [$bg, $fg] = $pill[$r['module']] ?? ['#eee', '#333']; @endphp
                    <tr class="{{ $loop->even ? 'alt' : '' }}">
                        <td>{{ $r['at']->format('y-m-d') }}</td>
                        <td><span class="pill" style="background: {{ $bg }}; color: {{ $fg }};">{{ __($r['type']) }}</span></td>
                        <td>{{ $r['description'] }}</td>
                        <td class="m">{{ $r['farm'] }}</td>
                        <td class="num b" style="color: {{ $r['module'] === 'expenses' ? '#b3261e' : ($r['module'] === 'sales' ? '#1b7a34' : '#111') }};">{{ $amountText($r) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($truncated)
        <div class="note">{{ __('The list is capped. Narrow the period or the modules to see everything.') }}</div>
    @endif
</body>
</html>
