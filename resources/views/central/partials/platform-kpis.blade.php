@php
    $stats = $stats ?? [];
    $hideAccountKpis = (bool) ($hideAccountKpis ?? false);
@endphp

<div class="dash-stats">
    @unless ($hideAccountKpis)
        <div class="dash-stat-card dash-ops-kpi">
        <div>
            <div class="dash-stat-label">Accounts (no farm yet)</div>
            <div class="dash-stat-value">{{ number_format($stats['accounts_without_farm'] ?? 0) }}</div>
            <p class="dash-field-hint" style="margin: 0.2rem 0 0;">Signed up in period, no farm yet</p>
        </div>
        @include('modules.partials.stat-icon', ['icon' => 'customer', 'label' => 'Accounts without farm'])
    </div>
    <div class="dash-stat-card dash-ops-kpi">
        <div>
            <div class="dash-stat-label">Users (with farm)</div>
            <div class="dash-stat-value accent">{{ number_format($stats['users_with_farm'] ?? 0) }}</div>
            <p class="dash-field-hint" style="margin: 0.2rem 0 0;">Signed up in period with at least one farm</p>
        </div>
        @include('modules.partials.stat-icon', ['icon' => 'employee', 'label' => 'Users with farm'])
    </div>
    @endunless
    <div class="dash-stat-card dash-ops-kpi">
        <div>
            <div class="dash-stat-label">Farms registered</div>
            <div class="dash-stat-value accent">{{ number_format($stats['farms'] ?? 0) }}</div>
            <p class="dash-field-hint" style="margin: 0.2rem 0 0;">Farms registered in period</p>
        </div>
        @include('modules.partials.stat-icon', ['icon' => 'farm', 'label' => 'Farms registered'])
    </div>
    <div class="dash-stat-card dash-ops-kpi">
        <div>
            <div class="dash-stat-label">Livestock groups</div>
            <div class="dash-stat-value">{{ number_format($stats['livestock_groups'] ?? 0) }}</div>
            <p class="dash-field-hint" style="margin: 0.2rem 0 0;">{{ number_format($stats['head_count'] ?? 0) }} head in groups added in period</p>
        </div>
        @include('modules.partials.stat-icon', ['icon' => 'livestock', 'label' => 'Livestock groups'])
    </div>
    <div class="dash-stat-card dash-ops-kpi">
        <div>
            <div class="dash-stat-label">Animals registered</div>
            <div class="dash-stat-value accent">{{ number_format($stats['animals'] ?? 0) }}</div>
            <p class="dash-field-hint" style="margin: 0.2rem 0 0;">Animals registered in period</p>
        </div>
        @include('modules.partials.stat-icon', ['icon' => 'animal', 'label' => 'Animals registered'])
    </div>
    <div class="dash-stat-card dash-ops-kpi">
        <div>
            <div class="dash-stat-label">Liter yield</div>
            <div class="dash-stat-value accent">{{ number_format($stats['liter_yield'] ?? 0, 0) }} <span class="dash-home-stat__suffix">L</span></div>
            <p class="dash-field-hint" style="margin: 0.2rem 0 0;">Completed milk sessions in period</p>
        </div>
        @include('modules.partials.stat-icon', ['icon' => 'milk', 'label' => 'Liter yield'])
    </div>
    <div class="dash-stat-card dash-ops-kpi">
        <div>
            <div class="dash-stat-label">Liters sold</div>
            <div class="dash-stat-value">{{ number_format($stats['liters_sold'] ?? 0, 0) }} <span class="dash-home-stat__suffix">L</span></div>
            <p class="dash-field-hint" style="margin: 0.2rem 0 0;">Completed milk sales in period</p>
        </div>
        @include('modules.partials.stat-icon', ['icon' => 'sale', 'label' => 'Liters sold'])
    </div>
</div>
