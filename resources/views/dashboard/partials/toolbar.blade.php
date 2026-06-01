@php
    $filters = $dashboard['filters'] ?? [];
@endphp

<form method="GET" action="{{ route('dashboard') }}" class="dash-ops-toolbar" id="dash-filters-form">
    <div class="dash-ops-toolbar__brand">
        <h1 class="dash-welcome" style="margin: 0;">Dashboard</h1>
        <p class="dash-home-subtitle" style="margin: 0.25rem 0 0;">{{ $filters['label'] ?? 'Operations overview' }}</p>
    </div>
    <div class="dash-ops-toolbar__controls">
        <div class="dash-ops-field">
            <label for="filter_farm">Farm</label>
            <select name="farm_id" id="filter_farm">
                <option value="">All farms</option>
                @foreach ($farms as $farm)
                    <option value="{{ $farm->id }}" @selected(($filters['farm_id'] ?? null) == $farm->id)>{{ $farm->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="dash-ops-field">
            <label for="filter_period">Period</label>
            <select name="period" id="filter_period">
                <option value="this_month" @selected(($filters['period'] ?? '') === 'this_month')>This month</option>
                <option value="last_month" @selected(($filters['period'] ?? '') === 'last_month')>Last month</option>
                <option value="this_quarter" @selected(($filters['period'] ?? '') === 'this_quarter')>This quarter</option>
                <option value="this_year" @selected(($filters['period'] ?? '') === 'this_year')>This year</option>
                <option value="custom" @selected(($filters['period'] ?? '') === 'custom')>Custom range</option>
            </select>
        </div>
        <div class="dash-ops-field dash-ops-field--dates @if(($filters['period'] ?? 'this_month') !== 'custom') dash-ops-field--muted @endif" id="dash-custom-dates">
            <label>Date range</label>
            <div class="dash-ops-dates">
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" aria-label="From date">
                <span class="dash-ops-dates__sep">→</span>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" aria-label="To date">
            </div>
        </div>
        <button type="submit" class="dash-btn-save dash-ops-apply">Apply</button>
    </div>
</form>
