@php
    $filters = $dashboard['filters'] ?? [];
    $user = auth()->user();
    $welcomeName = $user?->name
        ? strtok($user->name, ' ') ?: $user->name
        : null;
@endphp

<form method="GET" action="{{ route('dashboard') }}" class="dash-ops-toolbar" id="dash-filters-form">
    <div class="dash-ops-toolbar__brand">
        <h1 class="dash-welcome" style="margin: 0;">
            @if ($welcomeName)
                {{ __('Welcome, :name', ['name' => $welcomeName]) }}
            @else
                {{ __('Dashboard') }}
            @endif
        </h1>
        @if (! empty($filters['label']))
            <p class="admin-panel-meta" style="margin: 0.25rem 0 0;">{{ __($filters['label']) }}</p>
        @endif
    </div>
    <div class="dash-ops-toolbar__controls">
        <div class="dash-ops-field">
            <label for="filter_farm">{{ __('Farm') }}</label>
            <select name="farm_id" id="filter_farm">
                <option value="">{{ __('All') }}</option>
                @foreach ($farms as $farm)
                    <option value="{{ $farm->id }}" @selected(($filters['farm_id'] ?? null) == $farm->id)>{{ $farm->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="dash-ops-field">
            <label for="filter_period">{{ __('Period') }}</label>
            <select name="period" id="filter_period">
                <option value="all" @selected(($filters['period'] ?? '') === 'all')>{{ __('All time') }}</option>
                <option value="this_month" @selected(($filters['period'] ?? '') === 'this_month')>{{ __('This month') }}</option>
                <option value="last_month" @selected(($filters['period'] ?? '') === 'last_month')>{{ __('Last month') }}</option>
                <option value="this_quarter" @selected(($filters['period'] ?? '') === 'this_quarter')>{{ __('This quarter') }}</option>
                <option value="this_year" @selected(($filters['period'] ?? 'this_year') === 'this_year')>{{ __('This year') }}</option>
                <option value="custom" @selected(($filters['period'] ?? '') === 'custom')>{{ __('Custom') }}</option>
            </select>
        </div>
        <div class="dash-ops-field dash-ops-field--dates @if(($filters['period'] ?? 'this_year') !== 'custom') dash-ops-field--muted @endif" id="dash-custom-dates">
            <label>{{ __('Range') }}</label>
            <div class="dash-ops-dates">
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" aria-label="From date">
                <span class="dash-ops-dates__sep">→</span>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" aria-label="To date">
            </div>
        </div>
        <button type="submit" class="dash-btn-save dash-ops-apply">{{ __('Apply') }}</button>
    </div>
</form>
