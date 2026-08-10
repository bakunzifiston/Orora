@php
    $label = $label ?? __('Cost / L');
    $period = $period ?? '';
    $cost = $cost ?? [];
    $compareDelta = $compareDelta ?? null;
@endphp

<div class="dash-stat-card dash-ops-kpi dash-stat-card--cost">
    <details class="dash-cost-details">
        <summary class="dash-cost-details__summary">
            <div>
                <div class="dash-stat-label">{{ $label }}</div>
                @if ($cost['has_data'] ?? false)
                    <div class="dash-stat-value">{{ number_format($cost['cost_per_litre'], 0) }} <span class="dash-home-stat__suffix">{{ $cost['currency'] ?? 'RWF' }}</span></div>
                @else
                    <div class="dash-stat-value">—</div>
                @endif
                @if ($compareDelta !== null)
                    <p class="dash-cost-meta dash-cost-meta--delta {{ $compareDelta > 0 ? 'dash-cost-meta--up' : ($compareDelta < 0 ? 'dash-cost-meta--down' : '') }}">
                        @if ($compareDelta > 0)
                            +{{ number_format(abs($compareDelta), 0) }} vs {{ $compareLabel ?? __('previous') }}
                        @elseif ($compareDelta < 0)
                            {{ number_format($compareDelta, 0) }} vs {{ $compareLabel ?? __('previous') }}
                        @else
                            {{ __('Same vs') }} {{ $compareLabel ?? __('previous') }}
                        @endif
                    </p>
                @elseif (! ($cost['has_data'] ?? false))
                    <p class="dash-cost-meta">{{ $cost['reason'] ?? __('No data') }}</p>
                @endif
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'expense', 'label' => __('Cost per litre')])
        </summary>

        @if ($cost['has_data'] ?? false)
            <div class="dash-cost-breakdown">
                <div class="dash-cost-breakdown__title">{{ __('Breakdown') }}</div>
                <dl class="dash-cost-breakdown__list">
                    <div><dt>{{ __('Expenses') }}</dt><dd>{{ number_format($cost['total_expense'], 0) }} {{ $cost['currency'] }}</dd></div>
                    <div><dt>{{ __('Producing') }}</dt><dd>{{ $cost['producing_animals'] }} of {{ $cost['total_animals'] }}</dd></div>
                    <div><dt>{{ __('Allocated') }}</dt><dd>{{ number_format($cost['allocated_expense'], 0) }} {{ $cost['currency'] }}</dd></div>
                    <div><dt>{{ __('Litres') }}</dt><dd>{{ number_format($cost['total_litres'], 0) }} L</dd></div>
                </dl>
            </div>
        @endif
    </details>
</div>
