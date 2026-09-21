@php
    $filterQuery = array_filter([
        'from' => $filterFrom ?? null,
        'to' => $filterTo ?? null,
        'farm_id' => $filterFarmId ?? null,
        'livestock_id' => $filterLivestockId ?? null,
    ], fn ($value) => $value !== null && $value !== '');
@endphp

<form method="GET" action="{{ $filterAction ?? url()->current() }}" class="dash-ops-toolbar farms-page__toolbar" id="finance-filters-form">
    <div class="dash-ops-toolbar__controls farms-page__filters">
        <div class="dash-ops-field">
            <label for="filter_from">{{ __('From') }}</label>
            <input type="date" name="from" id="filter_from" value="{{ $filterFrom ?? now()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div class="dash-ops-field">
            <label for="filter_to">{{ __('To') }}</label>
            <input type="date" name="to" id="filter_to" value="{{ $filterTo ?? now()->endOfMonth()->format('Y-m-d') }}">
        </div>
        <div class="dash-ops-field">
            <label for="filter_farm">{{ __('Farm') }}</label>
            <select name="farm_id" id="filter_farm" onchange="this.form.submit()">
                <option value="">{{ __('All farms') }}</option>
                @foreach ($farms as $farm)
                    <option value="{{ $farm->id }}" @selected((string) ($filterFarmId ?? '') === (string) $farm->id)>{{ $farm->name }}</option>
                @endforeach
            </select>
        </div>
        @if (! empty($livestock))
            <div class="dash-ops-field">
                <label for="filter_livestock">{{ __('Livestock') }}</label>
                <select name="livestock_id" id="filter_livestock" onchange="this.form.submit()">
                    <option value="">{{ __('All herds') }}</option>
                    @foreach ($livestock as $herd)
                        <option value="{{ $herd->id }}" @selected((string) ($filterLivestockId ?? '') === (string) $herd->id)>{{ $herd->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <button type="submit" class="dash-btn-save dash-ops-apply">{{ __('Apply') }}</button>
        @if (! empty($filterFarmId) || ! empty($filterLivestockId))
            <a href="{{ ($filterAction ?? url()->current()).'?'.http_build_query(array_filter(['from' => $filterFrom ?? null, 'to' => $filterTo ?? null])) }}" class="dash-btn-cancel">{{ __('Clear') }}</a>
        @endif
    </div>
</form>
