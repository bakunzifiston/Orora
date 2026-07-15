@php
    $filters = $filters ?? [];
    $toolbarTitle = $toolbarTitle ?? 'Platform overview';
    $toolbarSubtitle = $toolbarSubtitle ?? 'Showing data for';
    $toolbarAction = $toolbarAction ?? route('central.dashboard');
    $toolbarFormId = $toolbarFormId ?? 'admin-dash-filters-form';
    $toolbarPeriodId = $toolbarPeriodId ?? 'admin_filter_period';
    $toolbarDatesId = $toolbarDatesId ?? 'admin-dash-custom-dates';
    $period = $filters['period'] ?? 'all';
@endphp

<form method="GET" action="{{ $toolbarAction }}" class="dash-ops-toolbar" id="{{ $toolbarFormId }}">
    <div class="dash-ops-toolbar__brand">
        <h1 class="dash-welcome" style="margin: 0;">{{ $toolbarTitle }}</h1>
        <p class="dash-home-subtitle" style="margin: 0.25rem 0 0;">
            {{ $toolbarSubtitle }} <strong>{{ $filters['label'] ?? 'All time' }}</strong>
        </p>
    </div>
    <div class="dash-ops-toolbar__controls">
        <div class="dash-ops-field">
            <label for="{{ $toolbarPeriodId }}">Period</label>
            <select name="period" id="{{ $toolbarPeriodId }}">
                <option value="all" @selected($period === 'all' || $period === '')>All time</option>
                <option value="daily" @selected($period === 'daily')>Daily</option>
                <option value="monthly" @selected($period === 'monthly')>Monthly</option>
                <option value="yearly" @selected($period === 'yearly')>Yearly</option>
                <option value="custom" @selected($period === 'custom')>Custom range</option>
            </select>
        </div>
        <div class="dash-ops-field dash-ops-field--dates @if($period !== 'custom') dash-ops-field--muted @endif" id="{{ $toolbarDatesId }}">
            <label>Date range</label>
            <div class="dash-ops-dates">
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" aria-label="From date">
                <span class="dash-ops-dates__sep">→</span>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" aria-label="To date">
            </div>
        </div>
        <button type="submit" class="dash-btn-save dash-ops-apply">Apply</button>
        @if (($period ?? 'all') !== 'all' && ($period ?? '') !== '')
            <a href="{{ $toolbarAction }}" class="dash-back-link" style="align-self: flex-end; padding-bottom: 0.45rem;">Clear</a>
        @endif
    </div>
</form>
