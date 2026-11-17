@extends('layouts.dashboard')

@section('title', __('Activity report'))

@section('content')
    @php
        $pill = [
            'sales' => ['#e8f5e9', '#1b5e20'], 'expenses' => ['#fdecea', '#b3261e'], 'milk' => ['#e3f2fd', '#0d47a1'],
            'eggs' => ['#fff4cc', '#8a6100'], 'health' => ['#f3e5f5', '#6a1b9a'], 'breeding' => ['#fce4ec', '#ad1457'],
            'feeding' => ['#efebe9', '#4e342e'], 'animals' => ['#e0f2f1', '#00695c'],
        ];
        $modKey = array_flip(\App\Services\ActivityReportService::MODULES);
    @endphp
    <style>
        .ar-page { --ar-line: #d8e3d9; --ar-green: #1b5e20; font-size: 15px; }
        .ar-banner { background: linear-gradient(90deg, #14532d, #2e7d32); border-bottom: 3px solid #f2b705; border-radius: 8px; color: #fff; padding: .6rem .9rem; margin-bottom: .75rem; display: flex; justify-content: space-between; align-items: center; gap: .75rem; flex-wrap: wrap; }
        .ar-banner h2 { margin: 0; font-size: 22px; color: #fff; }
        .ar-banner small { color: #c8e6cf; font-size: 13px; }
        .ar-filters { display: flex; flex-wrap: wrap; gap: .5rem .75rem; align-items: flex-end; padding: .65rem .8rem; background: #eef6ef; border: 1px solid var(--ar-line); border-left: 4px solid #2e7d32; border-radius: 8px; margin-bottom: .75rem; }
        .ar-filters label { display: block; font-size: 13px; color: #3f6b49; margin-bottom: 2px; font-weight: 600; }
        .ar-filters input[type=date], .ar-filters select { padding: 6px 8px; font-size: 15px; }
        .ar-mods { display: flex; flex-wrap: wrap; gap: .3rem .5rem; flex: 1 1 100%; }
        .ar-mods label { display: inline-flex; gap: .3rem; align-items: center; margin: 0; font-size: 14px; font-weight: 600; padding: 4px 11px; border-radius: 999px; }
        .ar-table { width: 100%; border-collapse: collapse; font-size: 14.5px; }
        .ar-table th { text-align: left; padding: 8px 10px; background: #1b5e20; color: #fff; font-size: 13px; text-transform: uppercase; letter-spacing: .04em; }
        .ar-table td { padding: 7px 10px; border-bottom: 1px solid var(--ar-line); vertical-align: top; }
        .ar-table tbody tr:nth-child(even) td { background: #f4f9f4; }
        .ar-table .num { text-align: right; white-space: nowrap; font-weight: 600; }
        .ar-pill { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 13px; font-weight: 700; white-space: nowrap; }
        .ar-muted { color: #6b7280; }
        .ar-h { font-size: 14px; text-transform: uppercase; letter-spacing: .06em; margin: .9rem 0 .35rem; color: #14532d; border-left: 3px solid #f2b705; padding-left: 8px; font-weight: 700; }
    </style>

    <div class="farm-dash farms-page workspace-reports-page ar-page">
        <div class="ar-banner">
            <h2>{{ __('Activity report') }}</h2>
            <small>{{ __('Everything that happened in a period, across all modules.') }}</small>
        </div>
        @include('modules.partials.flash')

        <form method="GET" action="{{ route('workspace.reports.index') }}" class="ar-filters">
            <div><label for="from">{{ __('From') }}</label><input type="date" name="from" id="from" value="{{ $filters['from'] }}"></div>
            <div><label for="to">{{ __('To') }}</label><input type="date" name="to" id="to" value="{{ $filters['to'] }}"></div>
            <div>
                <label for="farm_id">{{ __('Farm') }}</label>
                <select name="farm_id" id="farm_id">
                    <option value="">{{ __('All farms') }}</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" @selected($filters['farm_id'] === $farm->id)>{{ $farm->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="dash-btn-save">{{ __('Apply') }}</button>
            <a href="{{ route('workspace.reports.index') }}" class="dash-btn-cancel">{{ __('Reset') }}</a>
            <button type="submit" formaction="{{ route('workspace.reports.pdf') }}" class="dash-btn-save">{{ __('Export PDF') }}</button>
            <div class="ar-mods">
                @foreach ($moduleOptions as $key => $label)
                    @php [$cbg, $cfg] = $pill[$key] ?? ['#eee', '#333']; @endphp
                    <label style="background: {{ $cbg }}; color: {{ $cfg }};"><input type="checkbox" name="modules[]" value="{{ $key }}" @checked(in_array($key, $filters['modules'], true))> {{ __($label) }}</label>
                @endforeach
            </div>
            @if ($errors->any())
                <p style="color:#b91c1c; flex: 1 1 100%; margin: 0;">{{ $errors->first() }}</p>
            @endif
        </form>

        @if ($summary->isEmpty())
            <p class="dash-empty">{{ __('No activity found for these filters.') }}</p>
        @else
            <div class="ar-h">{{ __('Summary') }} · {{ number_format($totalRows) }} {{ __('activities') }}</div>
            <table class="ar-table ar-sumtable">
                <thead>
                    <tr><th>{{ __('Module') }}</th><th>{{ __('Activity') }}</th><th class="num">{{ __('Count') }}</th><th class="num">{{ __('Total') }}</th></tr>
                </thead>
                <tbody>
                    @foreach ($summary->groupBy('module') as $module => $items)
                        @php [$gbg, $gfg] = $pill[$modKey[$module] ?? ''] ?? ['#eee', '#333']; @endphp
                        @foreach ($items as $row)
                            <tr>
                                @if ($loop->first)
                                    <td rowspan="{{ $items->count() }}" style="background: {{ $gbg }}; color: {{ $gfg }}; font-weight: 800; text-transform: uppercase; font-size: 13px; border-bottom: 2px solid #fff; width: 22%;">
                                        {{ __($module) }}<div style="font-weight: 600; font-size: 13px; opacity: .85;">{{ number_format($items->sum('count')) }} {{ __('activities') }}</div>
                                    </td>
                                @endif
                                <td>{{ __($row['type']) }}</td>
                                <td class="num" style="width: 14%;">{{ number_format($row['count']) }}</td>
                                <td class="num" style="width: 22%; color: #6b7280;">{{ $row['total'] !== null ? number_format($row['total'], $row['unit'] === 'L' ? 1 : 0).' '.$row['unit'] : '' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>

            <div class="ar-h" style="margin-top: 1.1rem;">{{ __('Timeline') }}</div>
            <table class="ar-table">
                <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Activity') }}</th><th>{{ __('Details') }}</th><th>{{ __('Farm') }}</th><th class="num">{{ __('Amount') }}</th></tr></thead>
                <tbody>
                    @foreach ($rows as $r)
                        @php [$bg, $fg] = $pill[$r['module']] ?? ['#eee', '#333']; @endphp
                        <tr>
                            <td>{{ $r['at']->format('Y-m-d') }}</td>
                            <td><span class="ar-pill" style="background: {{ $bg }}; color: {{ $fg }};">{{ __($r['type']) }}</span></td>
                            <td>{{ $r['description'] }}</td>
                            <td class="ar-muted">{{ $r['farm'] }}</td>
                            <td class="num" style="color: {{ $r['module'] === 'expenses' ? '#b3261e' : ($r['module'] === 'sales' ? '#1b7a34' : '#111') }};">{{ $r['amount'] !== null ? number_format($r['amount'], $r['unit'] === 'L' ? 1 : 0).' '.($r['unit'] === 'currency' ? ($r['currency'] ?: 'RWF') : $r['unit']) : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($totalRows > $rows->count())
                <p class="ar-muted" style="margin-top: .4rem;">{{ __('Showing the first :n activities. The PDF contains all of them.', ['n' => $rows->count()]) }}</p>
            @endif
            @if ($truncated)
                <p style="color:#b45309; margin-top:.3rem;">{{ __('Some activity types have more than :n records; narrow the period to see everything.', ['n' => number_format(\App\Services\ActivityReportService::SOURCE_LIMIT)]) }}</p>
            @endif
        @endif
    </div>
@endsection
