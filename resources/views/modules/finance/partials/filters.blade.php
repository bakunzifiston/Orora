<form method="GET" action="{{ $filterAction ?? url()->current() }}" class="dash-index-toolbar" style="margin-bottom: 0;">
    <div class="dash-form-grid" style="align-items: end;">
        <div class="dash-form-field">
            <label for="filter_from">From</label>
            <input type="date" name="from" id="filter_from" value="{{ $filterFrom ?? now()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div class="dash-form-field">
            <label for="filter_to">To</label>
            <input type="date" name="to" id="filter_to" value="{{ $filterTo ?? now()->endOfMonth()->format('Y-m-d') }}">
        </div>
        <div class="dash-form-field">
            <label for="filter_farm">Farm</label>
            <select name="farm_id" id="filter_farm">
                <option value="">All farms</option>
                @foreach ($farms as $farm)
                    <option value="{{ $farm->id }}" @selected((string) ($filterFarmId ?? '') === (string) $farm->id)>{{ $farm->name }}</option>
                @endforeach
            </select>
        </div>
        @if (! empty($livestock))
            <div class="dash-form-field">
                <label for="filter_livestock">Livestock / herd</label>
                <select name="livestock_id" id="filter_livestock">
                    <option value="">All herds</option>
                    @foreach ($livestock as $herd)
                        <option value="{{ $herd->id }}" @selected((string) ($filterLivestockId ?? '') === (string) $herd->id)>{{ $herd->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="dash-form-field">
            <button type="submit" class="dash-btn-save">Apply</button>
        </div>
    </div>
</form>
