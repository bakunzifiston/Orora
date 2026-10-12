@php
    $schedule = $schedule ?? null;
    $selectedFarmId = old('farm_id', $schedule?->farm_id);
    $selectedLivestockId = old('livestock_id', $schedule?->livestock_id);
    $selectedAnimalId = old('animal_id', $schedule?->animal_id);
    $selectedFlockId = old('flock_id', $schedule?->flock_id);
    $selectedFarm = $farms->firstWhere('id', (int) $selectedFarmId);
    $isPoultryFarm = $selectedFarm?->isPoultry() ?? false;
    $feedTarget = old('feed_target');

    if ($feedTarget === null) {
        $feedTarget = $selectedAnimalId ? 'animal' : 'group';
    }

    if (! in_array($feedTarget, ['group', 'animal'], true)) {
        $feedTarget = 'group';
    }
@endphp

<div class="dash-form-grid" data-feeding-target-form>
    <div class="dash-form-field">
        <label for="farm_id">{{ __('Farm') }} <span class="dash-required">*</span></label>
        <select name="farm_id" id="farm_id" required data-feeding-farm>
            <option value="">{{ __('Select farm') }}</option>
            @foreach ($farms as $farm)
                <option
                    value="{{ $farm->id }}"
                    data-species="{{ $farm->primary_species ?: 'cattle' }}"
                    @selected((string) $selectedFarmId === (string) $farm->id)
                >{{ $farm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="feed_type_id">{{ __('Feed type') }} <span class="dash-required">*</span></label>
        <select name="feed_type_id" id="feed_type_id" required data-feeding-feed-type>
            <option value="">{{ __('Select feed type') }}</option>
            @foreach ($feedTypes as $type)
                <option value="{{ $type->id }}" @selected(old('feed_type_id', $schedule?->feed_type_id) == $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="feed_inventory_id">{{ __('Inventory (optional)') }}</label>
        <select name="feed_inventory_id" id="feed_inventory_id" data-feeding-inventory>
            <option value="">{{ __('Auto-select for farm') }}</option>
            @foreach ($inventories as $inv)
                <option
                    value="{{ $inv->id }}"
                    data-farm-id="{{ $inv->farm_id }}"
                    data-feed-type-id="{{ $inv->feed_type_id }}"
                    @selected(old('feed_inventory_id', $schedule?->feed_inventory_id) == $inv->id)
                >
                    {{ $inv->farm->name }} — {{ $inv->feedType->name }} ({{ $inv->quantity_on_hand }} {{ $inv->unit }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="quantity">{{ __('Quantity') }} <span class="dash-required">*</span></label>
        <input type="number" step="0.01" min="0.01" name="quantity" id="quantity" value="{{ old('quantity', $schedule?->quantity) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="unit">{{ __('Unit') }} <span class="dash-required">*</span></label>
        <select name="unit" id="unit" required>
            @foreach (config('modules.feed_units') as $unit)
                <option value="{{ $unit }}" @selected(old('unit', $schedule?->unit ?? 'kg') === $unit)>{{ $unit }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="frequency">{{ __('Frequency') }} <span class="dash-required">*</span></label>
        <select name="frequency" id="frequency" required>
            @foreach (config('modules.schedule_frequencies') as $frequency)
                <option value="{{ $frequency }}" @selected(old('frequency', $schedule?->frequency ?? 'daily') === $frequency)>{{ ucfirst($frequency) }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="scheduled_time">{{ __('Time') }}</label>
        <input type="time" name="scheduled_time" id="scheduled_time" value="{{ old('scheduled_time', $schedule?->scheduled_time ? \Illuminate\Support\Carbon::parse($schedule->scheduled_time)->format('H:i') : '') }}">
    </div>
    <div class="dash-form-field">
        <label for="start_date">{{ __('Start date') }} <span class="dash-required">*</span></label>
        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $schedule?->start_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="end_date">{{ __('End date') }}</label>
        <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $schedule?->end_date?->format('Y-m-d')) }}">
    </div>
    <div class="dash-form-field">
        <label for="next_due_date">{{ __('Next due date') }}</label>
        <input type="date" name="next_due_date" id="next_due_date" value="{{ old('next_due_date', $schedule?->next_due_date?->format('Y-m-d')) }}">
    </div>
    <div class="dash-form-field">
        <label for="status">{{ __('Status') }} <span class="dash-required">*</span></label>
        <select name="status" id="status" required>
            @foreach (config('modules.schedule_statuses') as $status)
                <option value="{{ $status }}" @selected(old('status', $schedule?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>

    <div data-feeding-cattle-target style="display: contents;" @if ($isPoultryFarm) hidden @endif>
        <div class="dash-form-field dash-form-field--full">
            <label>{{ __('Feed for') }}</label>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <label class="dash-checkbox">
                    <input type="radio" name="feed_target" value="group" @checked($feedTarget === 'group') data-feeding-target>
                    <span>{{ __('Entire livestock group') }}</span>
                </label>
                <label class="dash-checkbox">
                    <input type="radio" name="feed_target" value="animal" @checked($feedTarget === 'animal') data-feeding-target>
                    <span>{{ __('One animal') }}</span>
                </label>
            </div>
            <p class="dash-field-hint">{{ __('Choose a whole group or a single animal from that group—not both.') }}</p>
        </div>

        <div class="dash-form-field" data-feeding-livestock-field @if ($isPoultryFarm) hidden @endif>
            <label for="livestock_id">{{ __('Livestock group') }} <span class="dash-required" data-feeding-livestock-required @if ($isPoultryFarm) hidden @endif>*</span></label>
            <select name="livestock_id" id="livestock_id" data-feeding-livestock>
                <option value="">{{ __('Select livestock group') }}</option>
                @foreach ($livestockGroups as $group)
                    <option
                        value="{{ $group->id }}"
                        data-farm-id="{{ $group->farm_id }}"
                        @selected((string) $selectedLivestockId === (string) $group->id)
                    >
                        {{ $group->name }}@if ($group->farm) — {{ $group->farm->name }}@endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="dash-form-field" data-feeding-animal-field @if ($feedTarget !== 'animal' || $isPoultryFarm) hidden @endif>
            <label for="animal_id">{{ __('Animal') }} <span class="dash-required">*</span></label>
            <select name="animal_id" id="animal_id" data-feeding-animal>
                <option value="">{{ __('Select animal') }}</option>
                @foreach ($animals as $animal)
                    <option
                        value="{{ $animal->id }}"
                        data-farm-id="{{ $animal->farm_id }}"
                        data-livestock-id="{{ $animal->livestock_id }}"
                        @selected((string) $selectedAnimalId === (string) $animal->id)
                    >
                        {{ $animal->tag_number }}@if ($animal->name) — {{ $animal->name }}@endif
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="dash-form-field" data-feeding-flock-field @if (! $isPoultryFarm) hidden @endif>
        <label for="flock_id">{{ __('Flock') }}</label>
        <select name="flock_id" id="flock_id" data-feeding-flock>
            <option value="">{{ __('None') }}</option>
            @foreach (($flocks ?? []) as $flock)
                <option
                    value="{{ $flock->id }}"
                    data-farm-id="{{ $flock->farm_id }}"
                    @selected((string) $selectedFlockId === (string) $flock->id)
                >{{ $flock->label() }}@if ($flock->farm) — {{ $flock->farm->name }}@endif</option>
            @endforeach
        </select>
    </div>

    <div class="dash-form-field dash-form-field--full">
        <label for="notes">{{ __('Notes') }}</label>
        <textarea name="notes" id="notes" rows="2">{{ old('notes', $schedule?->notes) }}</textarea>
    </div>
</div>
