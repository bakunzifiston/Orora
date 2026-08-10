@php
    $selectedFarm = $selectedFarm ?? null;
    $farms = $farms ?? collect();
@endphp

<div class="dash-ops-field">
    <label for="milk_filter_farm">{{ __('Farm') }}</label>
    <select name="farm_id" id="milk_filter_farm" onchange="this.form.submit()">
        <option value="" @selected($selectedFarm === null)>{{ __('All farms') }}</option>
        @foreach ($farms as $farm)
            <option value="{{ $farm->id }}" @selected($selectedFarm === $farm->id)>{{ $farm->name }}</option>
        @endforeach
    </select>
</div>
