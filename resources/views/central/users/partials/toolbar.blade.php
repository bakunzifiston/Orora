@php
    $filters = $filters ?? [];
    $farmOptions = $farmOptions ?? collect();
    $provinces = $provinces ?? [];
    $districts = $districts ?? [];
    $filtersActive = $filtersActive ?? false;
@endphp

<form method="GET" action="{{ route('central.users.index') }}" class="dash-ops-toolbar" id="admin-users-filters-form">
    <div class="dash-ops-toolbar__brand">
        <h1 class="dash-welcome" style="margin: 0;">Farms</h1>
    </div>
    <div class="dash-ops-toolbar__controls">
        <div class="dash-ops-field">
            <label for="admin_users_filter_farm">Farm</label>
            <select name="farm_id" id="admin_users_filter_farm">
                <option value="">All</option>
                @foreach ($farmOptions as $farmOption)
                    <option value="{{ $farmOption->id }}" @selected(($filters['farm_id'] ?? null) == $farmOption->id)>
                        {{ $farmOption->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="dash-ops-field" id="admin-users-location-fields">
            <label for="admin_users_filter_province">Province</label>
            <select name="province_code" id="admin_users_filter_province" data-location-province @disabled(! empty($filters['farm_id']))>
                <option value="">All</option>
                @foreach ($provinces as $province)
                    <option value="{{ $province['code'] }}" @selected(($filters['province_code'] ?? null) == $province['code'])>{{ $province['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="dash-ops-field" id="admin-users-district-field">
            <label for="admin_users_filter_district">District</label>
            <select name="district_code" id="admin_users_filter_district" data-location-district @disabled(! empty($filters['farm_id']) || empty($filters['province_code']))>
                <option value="">All</option>
                @foreach ($districts as $district)
                    <option value="{{ $district['code'] }}" @selected(($filters['district_code'] ?? null) == $district['code'])>{{ $district['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="dash-ops-field">
            <label for="admin_users_filter_period">Period</label>
            <select name="period" id="admin_users_filter_period">
                <option value="all" @selected(($filters['period'] ?? '') === 'all' || ($filters['period'] ?? '') === '')>All time</option>
                <option value="daily" @selected(($filters['period'] ?? '') === 'daily')>Daily</option>
                <option value="monthly" @selected(($filters['period'] ?? '') === 'monthly')>Monthly</option>
                <option value="yearly" @selected(($filters['period'] ?? '') === 'yearly')>Yearly</option>
                <option value="custom" @selected(($filters['period'] ?? '') === 'custom')>Custom</option>
            </select>
        </div>
        <div class="dash-ops-field dash-ops-field--dates @if(($filters['period'] ?? '') !== 'custom') dash-ops-field--muted @endif" id="admin-users-custom-dates">
            <label>Range</label>
            <div class="dash-ops-dates">
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" aria-label="From date">
                <span class="dash-ops-dates__sep">→</span>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" aria-label="To date">
            </div>
        </div>
        <button type="submit" class="dash-btn-save dash-ops-apply">Apply</button>
        @if ($filtersActive)
            <a href="{{ route('central.users.index') }}" class="dash-back-link" style="align-self: flex-end; padding-bottom: 0.45rem;">Clear</a>
        @endif
    </div>
</form>
