@extends('layouts.finance-module')

@section('title', 'Finance — Cash flow')

@section('finance-content')
    @include('modules.partials.header', [
        'title' => 'Cash flow',
        'subtitle' => 'Movements on cash and bank accounts.',
    ])
    @include('modules.partials.flash')
    @include('modules.finance.partials.filters')

    <div class="dash-stat-card" style="margin-bottom: 1.25rem; max-width: 320px;">
        <div>
            <div class="dash-stat-label">Net cash change</div>
            <div class="dash-stat-value accent">{{ number_format($report['net_cash_change'], 0) }} RWF</div>
        </div>
        @include('modules.partials.stat-icon', ['icon' => 'finance'])
    </div>

    <div class="dash-panel">
        @if ($report['movements']->isEmpty())
            <p class="dash-empty">No cash movements in this period.</p>
        @else
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Account</th>
                        <th>Change</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['movements'] as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row['date'])->format('M j, Y') }}</td>
                            <td>{{ $row['code'] }}</td>
                            <td>{{ $row['description'] }}</td>
                            <td>{{ $row['account'] }}</td>
                            <td style="color: {{ $row['amount'] >= 0 ? 'inherit' : '#b45309' }};">
                                {{ $row['amount'] >= 0 ? '+' : '' }}{{ number_format($row['amount'], 0) }} RWF
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
