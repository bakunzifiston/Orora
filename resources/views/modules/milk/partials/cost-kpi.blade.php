@if (($costCurrent['has_data'] ?? false) && (float) ($costCurrent['cost_per_litre'] ?? 0) != 0)
<div class="farm-kpi farm-kpi--expense">
    <div class="farm-kpi__icon" aria-hidden="true">
        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'expense'])
    </div>
    <div class="farm-kpi__body">
        <div class="farm-kpi__label">{{ __('Cost / L') }}</div>
        <div class="farm-kpi__value">
            @if ($costCurrent['has_data'] ?? false)
                {{ number_format($costCurrent['cost_per_litre'], 0) }}
                <span class="farm-kpi__unit">{{ $costCurrent['currency'] ?? 'RWF' }}</span>
            @else
                —
            @endif
        </div>
    </div>
</div>
@endif
