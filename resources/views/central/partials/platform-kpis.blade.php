@php
    $stats = $stats ?? [];
    $hideAccountKpis = (bool) ($hideAccountKpis ?? false);
@endphp

<div class="dash-stats admin-kpis">
    @unless ($hideAccountKpis)
        <div class="dash-stat-card dash-ops-kpi">
            <div>
                <div class="dash-stat-label">Accounts pending</div>
                <div class="dash-stat-value">{{ number_format($stats['accounts_without_farm'] ?? 0) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'customer', 'label' => 'Accounts without farm'])
        </div>
    @endunless
    <div class="dash-stat-card dash-ops-kpi">
        <div>
            <div class="dash-stat-label">Farms</div>
            <div class="dash-stat-value accent">{{ number_format($stats['farms'] ?? 0) }}</div>
        </div>
        @include('modules.partials.stat-icon', ['icon' => 'farm', 'label' => 'Farms'])
    </div>
    <div class="dash-stat-card dash-ops-kpi">
        <div>
            <div class="dash-stat-label">Livestock groups</div>
            <div class="dash-stat-value">{{ number_format($stats['livestock_groups'] ?? 0) }}</div>
        </div>
        @include('modules.partials.stat-icon', ['icon' => 'livestock', 'label' => 'Livestock groups'])
    </div>
    <div class="dash-stat-card dash-ops-kpi">
        <div>
            <div class="dash-stat-label">Animals</div>
            <div class="dash-stat-value accent">{{ number_format($stats['animals'] ?? 0) }}</div>
        </div>
        @include('modules.partials.stat-icon', ['icon' => 'animal', 'label' => 'Animals'])
    </div>
    <div class="dash-stat-card dash-ops-kpi">
        <div>
            <div class="dash-stat-label">Milk yield</div>
            <div class="dash-stat-value accent">{{ number_format($stats['liter_yield'] ?? 0, 0) }}<span class="dash-home-stat__suffix"> L</span></div>
        </div>
        @include('modules.partials.stat-icon', ['icon' => 'milk', 'label' => 'Milk yield'])
    </div>
    <div class="dash-stat-card dash-ops-kpi">
        <div>
            <div class="dash-stat-label">Milk sold</div>
            <div class="dash-stat-value">{{ number_format($stats['liters_sold'] ?? 0, 0) }}<span class="dash-home-stat__suffix"> L</span></div>
        </div>
        @include('modules.partials.stat-icon', ['icon' => 'sale', 'label' => 'Milk sold'])
    </div>
</div>
