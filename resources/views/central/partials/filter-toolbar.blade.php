@php
    $filters = $filters ?? [];
    $toolbarTitle = $toolbarTitle ?? 'Platform overview';
    $toolbarSubtitle = $toolbarSubtitle ?? 'Showing data for';
    $toolbarAction = $toolbarAction ?? route('central.dashboard');
    $toolbarFormId = $toolbarFormId ?? 'admin-dash-filters-form';
    $toolbarPeriodId = $toolbarPeriodId ?? 'admin_filter_period';
    $toolbarDatesId = $toolbarDatesId ?? 'admin-dash-custom-dates';
@endphp

<form method="GET" action="{{ $toolbarAction }}" class="dash-ops-toolbar" id="{{ $toolbarFormId }}">
    <div class="dash-ops-toolbar__brand">
        <h1 class="dash-welcome" style="margin: 0;">{{ $toolbarTitle }}</h1>
        <p class="dash-home-subtitle" style="margin: 0.25rem 0 0;">
            {{ $toolbarSubtitle }} <strong>{{ $filters['label'] ?? 'This month' }}</strong>
        </p>
    </div>
    <div class="dash-ops-toolbar__controls">
        <div class="dash-ops-field">
            <label for="{{ $toolbarPeriodId }}">Period</label>
            <select name="period" id="{{ $toolbarPeriodId }}">
                <option value="daily" @selected(($filters['period'] ?? '') === 'daily')>Daily</option>
                <option value="monthly" @selected(($filters['period'] ?? 'monthly') === 'monthly')>Monthly</option>
                <option value="yearly" @selected(($filters['period'] ?? '') === 'yearly')>Yearly</option>
                <option value="custom" @selected(($filters['period'] ?? '') === 'custom')>Custom range</option>
            </select>
        </div>
        <div class="dash-ops-field dash-ops-field--dates @if(($filters['period'] ?? 'monthly') !== 'custom') dash-ops-field--muted @endif" id="{{ $toolbarDatesId }}">
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
