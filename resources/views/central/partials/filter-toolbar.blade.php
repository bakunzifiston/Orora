@php
    $filters = $filters ?? [];
    $farmOptions = $farmOptions ?? collect();
    $toolbarTitle = $toolbarTitle ?? __('Dashboard');
    $toolbarAction = $toolbarAction ?? route('central.dashboard');
    $toolbarFormId = $toolbarFormId ?? 'admin-dash-filters-form';
    $toolbarPeriodId = $toolbarPeriodId ?? 'admin_filter_period';
    $toolbarDatesId = $toolbarDatesId ?? 'admin-dash-custom-dates';
    $period = $filters['period'] ?? 'all';
    $periodActive = ($period ?? 'all') !== 'all' && ($period ?? '') !== '';
    $farmActive = ! empty($filters['farm_id']);
    $filterActive = $periodActive || $farmActive;
@endphp

<form method="GET" action="{{ $toolbarAction }}" class="dash-ops-toolbar farms-page__toolbar admin-dash__toolbar" id="{{ $toolbarFormId }}">
    <div class="admin-dash__header-text dash-ops-toolbar__brand">
        <h1 class="admin-dash__title">{{ $toolbarTitle }}</h1>
        <p class="admin-dash__period">
            {{ __('Showing') }}
            <strong>{{ $filters['label'] ?? __('All time') }}</strong>
        </p>
    </div>

    <div class="dash-ops-toolbar__controls farms-page__filters">
        <div class="dash-ops-field">
            <label for="admin_filter_farm">{{ __('Farm') }}</label>
            <select name="farm_id" id="admin_filter_farm" onchange="this.form.submit()">
                <option value="">{{ __('All farms') }}</option>
                @foreach ($farmOptions as $farmOption)
                    <option value="{{ $farmOption->id }}" @selected(($filters['farm_id'] ?? null) == $farmOption->id)>
                        {{ $farmOption->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="dash-ops-field">
            <label for="{{ $toolbarPeriodId }}">{{ __('Period') }}</label>
            <select name="period" id="{{ $toolbarPeriodId }}" onchange="this.form.submit()">
                <option value="all" @selected($period === 'all' || $period === '')>{{ __('All time') }}</option>
                <option value="daily" @selected($period === 'daily')>{{ __('Daily') }}</option>
                <option value="monthly" @selected($period === 'monthly')>{{ __('Monthly') }}</option>
                <option value="yearly" @selected($period === 'yearly')>{{ __('Yearly') }}</option>
                <option value="custom" @selected($period === 'custom')>{{ __('Custom range') }}</option>
            </select>
        </div>
        <div class="dash-ops-field dash-ops-field--dates @if($period !== 'custom') dash-ops-field--muted @endif" id="{{ $toolbarDatesId }}">
            <label>{{ __('Date range') }}</label>
            <div class="dash-ops-dates">
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" aria-label="{{ __('From date') }}">
                <span class="dash-ops-dates__sep">→</span>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" aria-label="{{ __('To date') }}">
            </div>
        </div>
        <button type="submit" class="dash-btn-save dash-ops-apply">{{ __('Apply') }}</button>
        @if ($filterActive)
            <a href="{{ $toolbarAction }}" class="dash-btn-cancel">{{ __('Clear') }}</a>
        @endif
    </div>
</form>
