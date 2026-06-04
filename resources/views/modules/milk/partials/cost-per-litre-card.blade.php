@php
    $label = $label ?? 'Cost per litre';
    $period = $period ?? '';
    $cost = $cost ?? [];
    $compareDelta = $compareDelta ?? null;
    $showAllocation = $showAllocation ?? false;
@endphp

<div class="dash-stat-card dash-stat-card--cost" style="position: relative;">
    <details class="dash-cost-details">
        <summary class="dash-cost-details__summary">
            <div>
                <div class="dash-stat-label">{{ $label }}@if($period) <span style="font-weight:400;color:#6b7280;">{{ $period }}</span>@endif</div>
                @if (! empty($cost['farm_name']))
                    <p class="dash-cost-meta" style="margin-top:0.15rem;">{{ $cost['farm_name'] }}</p>
                @endif
                @if ($cost['has_data'] ?? false)
                    <div class="dash-stat-value">{{ number_format($cost['cost_per_litre'], 0) }} {{ $cost['currency'] }} / L</div>
                @else
                    <div class="dash-stat-value">— {{ $cost['currency'] ?? 'RWF' }} / L</div>
                @endif

                @if (! ($cost['has_data'] ?? false))
                    <p class="dash-cost-meta">{{ $cost['reason'] ?? 'No data' }}</p>
                @elseif ($showAllocation)
                    <p class="dash-cost-meta">
                        {{ number_format($cost['allocated_expense'], 0) }} {{ $cost['currency'] }} allocated ·
                        {{ number_format($cost['total_litres'], 0) }} L produced
                    </p>
                @else
                    <p class="dash-cost-meta">
                        {{ $cost['producing_animals'] }} of {{ $cost['total_animals'] }} animals producing
                    </p>
                @endif

                @if ($compareDelta !== null)
                    <p class="dash-cost-meta dash-cost-meta--delta {{ $compareDelta > 0 ? 'dash-cost-meta--up' : ($compareDelta < 0 ? 'dash-cost-meta--down' : '') }}">
                        @if ($compareDelta > 0)
                            ↑ +{{ number_format(abs($compareDelta), 0) }} RWF vs {{ $compareLabel ?? 'previous' }}
                        @elseif ($compareDelta < 0)
                            ↓ {{ number_format($compareDelta, 0) }} RWF vs {{ $compareLabel ?? 'previous' }}
                        @else
                            — unchanged vs {{ $compareLabel ?? 'previous' }}
                        @endif
                    </p>
                @endif
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'expense'])
        </summary>

        @if ($cost['has_data'] ?? false)
            <div class="dash-cost-breakdown">
                <div class="dash-cost-breakdown__title">How this is calculated</div>
                <dl class="dash-cost-breakdown__list">
                    <div><dt>Total farm expenses</dt><dd>{{ number_format($cost['total_expense'], 0) }} {{ $cost['currency'] }}</dd></div>
                    <div><dt>Producing animals</dt><dd>{{ $cost['producing_animals'] }} of {{ $cost['total_animals'] }}</dd></div>
                    <div><dt>Producing ratio</dt><dd>{{ number_format($cost['producing_ratio'], 1) }}%</dd></div>
                    <div><dt>Allocated to milk</dt><dd>{{ number_format($cost['allocated_expense'], 0) }} {{ $cost['currency'] }}</dd></div>
                    <div><dt>Litres produced</dt><dd>{{ number_format($cost['total_litres'], 2) }} L</dd></div>
                </dl>
                @if ($cost['is_combined'] ?? false)
                    <p class="dash-cost-breakdown__formula">
                        Weighted average across farms:<br>
                        {{ number_format($cost['allocated_expense'], 0) }} {{ $cost['currency'] }} allocated
                        ÷ {{ number_format($cost['total_litres'], 2) }} L
                        = <strong>{{ number_format($cost['cost_per_litre'], 0) }} {{ $cost['currency'] }} / L</strong>
                    </p>
                @else
                    <p class="dash-cost-breakdown__formula">
                        ({{ number_format($cost['total_expense'], 0) }} × {{ $cost['producing_animals'] }}/{{ max($cost['total_animals'], $cost['producing_animals']) }})
                        ÷ {{ number_format($cost['total_litres'], 2) }}
                        = <strong>{{ number_format($cost['cost_per_litre'], 0) }} {{ $cost['currency'] }} / L</strong>
                    </p>
                @endif
            </div>
        @endif
    </details>
</div>
