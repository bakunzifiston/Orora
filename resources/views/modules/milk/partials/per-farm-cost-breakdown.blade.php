@php
    $title = $title ?? 'Cost per litre by farm';
    $combined = $combined ?? null;
    $perFarm = $perFarm ?? [];
@endphp

<div class="dash-panel" style="margin-bottom: 1.25rem;">
    <div class="dash-panel-title">{{ $title }}</div>
    <p style="font-size: 0.8125rem; color: #6b7280; margin: 0 0 1rem;">
        Each farm is calculated separately. The combined row is a <strong>litre-weighted average</strong>
        (allocated expenses ÷ total litres), not a simple average of per-farm rates.
    </p>
    <div class="dash-table-wrap">
        <table class="dash-table">
            <thead>
                <tr>
                    <th>Farm</th>
                    <th style="text-align:right;">Expenses</th>
                    <th style="text-align:right;">Litres</th>
                    <th style="text-align:right;">Animals</th>
                    <th style="text-align:right;">Cost / litre</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($perFarm as $row)
                    <tr>
                        <td><strong>{{ $row['farm_name'] }}</strong></td>
                        <td style="text-align:right;">{{ number_format($row['total_expense'], 0) }} {{ $row['currency'] }}</td>
                        <td style="text-align:right;">{{ number_format($row['total_litres'], 0) }} L</td>
                        <td style="text-align:right;">{{ $row['producing_animals'] }} / {{ $row['total_animals'] }}</td>
                        <td style="text-align:right;">
                            @if ($row['has_data'])
                                {{ number_format($row['cost_per_litre'], 0) }} {{ $row['currency'] }} / L
                            @else
                                <span style="color:#9ca3af;">—</span>
                                @if ($row['reason'])
                                    <div style="font-size:0.7rem;color:#9ca3af;">{{ $row['reason'] }}</div>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
                @if ($combined)
                    <tr style="background: rgba(164, 212, 0, 0.08); font-weight: 600;">
                        <td>Combined</td>
                        <td style="text-align:right;">{{ number_format($combined['total_expense'], 0) }} {{ $combined['currency'] }}</td>
                        <td style="text-align:right;">{{ number_format($combined['total_litres'], 0) }} L</td>
                        <td style="text-align:right;">{{ $combined['producing_animals'] }} / {{ $combined['total_animals'] }}</td>
                        <td style="text-align:right;">
                            @if ($combined['has_data'])
                                {{ number_format($combined['cost_per_litre'], 0) }} {{ $combined['currency'] }} / L
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
