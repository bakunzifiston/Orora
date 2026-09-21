@php
    $filters = $filters ?? [];
    $farmOptions = $farmOptions ?? collect();
    $provinces = $provinces ?? [];
    $districts = $districts ?? [];
    $filtersActive = $filtersActive ?? false;
    $period = $filters['period'] ?? 'all';
@endphp

<form method="GET" action="{{ route('central.farms.index') }}" class="dash-ops-toolbar farms-page__toolbar admin-dash__toolbar" id="admin-users-filters-form">
    <div class="admin-dash__header-text dash-ops-toolbar__brand">
        <h1 class="admin-dash__title">{{ __('Farms') }}</h1>
        <p class="admin-dash__period">
            {{ __('Showing') }}
            <strong>{{ $filters['label'] ?? __('All time') }}</strong>
        </p>
    </div>

    <div class="dash-ops-toolbar__controls farms-page__filters">
        <div class="dash-ops-field">
            <label for="admin_users_filter_farm">{{ __('Farm') }}</label>
            <select name="farm_id" id="admin_users_filter_farm" onchange="this.form.submit()">
                <option value="">{{ __('All farms') }}</option>
                @foreach ($farmOptions as $farmOption)
                    <option value="{{ $farmOption->id }}" @selected(($filters['farm_id'] ?? null) == $farmOption->id)>
                        {{ $farmOption->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="dash-ops-field" id="admin-users-location-fields">
            <label for="admin_users_filter_province">{{ __('Province') }}</label>
            <select name="province_code" id="admin_users_filter_province" data-location-province @disabled(! empty($filters['farm_id']))>
                <option value="">{{ __('All') }}</option>
                @foreach ($provinces as $province)
                    <option value="{{ $province['code'] }}" @selected(($filters['province_code'] ?? null) == $province['code'])>{{ $province['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="dash-ops-field" id="admin-users-district-field">
            <label for="admin_users_filter_district">{{ __('District') }}</label>
            <select name="district_code" id="admin_users_filter_district" data-location-district @disabled(! empty($filters['farm_id']) || empty($filters['province_code']))>
                <option value="">{{ __('All') }}</option>
                @foreach ($districts as $district)
                    <option value="{{ $district['code'] }}" @selected(($filters['district_code'] ?? null) == $district['code'])>{{ $district['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="dash-ops-field">
            <label for="admin_users_filter_period">{{ __('Period') }}</label>
            <select name="period" id="admin_users_filter_period" onchange="this.form.submit()">
                <option value="all" @selected($period === 'all' || $period === '')>{{ __('All time') }}</option>
                <option value="daily" @selected($period === 'daily')>{{ __('Daily') }}</option>
                <option value="monthly" @selected($period === 'monthly')>{{ __('Monthly') }}</option>
                <option value="yearly" @selected($period === 'yearly')>{{ __('Yearly') }}</option>
                <option value="custom" @selected($period === 'custom')>{{ __('Custom') }}</option>
            </select>
        </div>
        <div class="dash-ops-field dash-ops-field--dates @if($period !== 'custom') dash-ops-field--muted @endif" id="admin-users-custom-dates">
            <label>{{ __('Range') }}</label>
            <div class="dash-ops-dates">
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" aria-label="{{ __('From date') }}">
                <span class="dash-ops-dates__sep">→</span>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" aria-label="{{ __('To date') }}">
            </div>
        </div>
        <button type="submit" class="dash-btn-save dash-ops-apply">{{ __('Apply') }}</button>
        @if ($filtersActive)
            <a href="{{ route('central.farms.index') }}" class="dash-btn-cancel">{{ __('Clear') }}</a>
        @endif
    </div>
</form>
