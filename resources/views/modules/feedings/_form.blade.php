@php
    $feeding = $feeding ?? null;
    $selectedFarmId = old('farm_id', $feeding?->farm_id);
    $selectedLivestockId = old('livestock_id', $feeding?->livestock_id);
    $selectedAnimalId = old('animal_id', $feeding?->animal_id);
    $selectedFlockId = old('flock_id', $feeding?->flock_id);
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
        <label for="feed_inventory_id">{{ __('Feed inventory') }} <span class="dash-required">*</span></label>
        <select name="feed_inventory_id" id="feed_inventory_id" required data-feeding-inventory>
            <option value="">{{ __('Select stock item') }}</option>
            @foreach ($inventories as $inventory)
                <option
                    value="{{ $inventory->id }}"
                    data-farm-id="{{ $inventory->farm_id }}"
                    @selected(old('feed_inventory_id', $feeding?->feed_inventory_id) == $inventory->id)
                >
                    {{ $inventory->farm->name }} — {{ $inventory->feedType->name }} ({{ $inventory->quantity_on_hand }} {{ $inventory->unit }})
                </option>
            @endforeach
        </select>
        <p class="dash-field-hint">{{ __('Logging or updating a feeding deducts stock. Deleting it puts the stock back.') }}</p>
    </div>
    <div class="dash-form-field">
        <label for="feeding_schedule_id">{{ __('From schedule (optional)') }}</label>
        <select name="feeding_schedule_id" id="feeding_schedule_id" data-feeding-schedule>
            <option value="">{{ __('None') }}</option>
            @foreach ($schedules as $schedule)
                <option
                    value="{{ $schedule->id }}"
                    data-farm-id="{{ $schedule->farm_id }}"
                    @selected(old('feeding_schedule_id', $feeding?->feeding_schedule_id) == $schedule->id)
                >
                    {{ $schedule->select_label ?? $schedule->optionLabel() }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="fed_on">{{ __('Fed on') }} <span class="dash-required">*</span></label>
        <input type="date" name="fed_on" id="fed_on" value="{{ old('fed_on', $feeding?->fed_on?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="quantity">{{ __('Quantity') }} <span class="dash-required">*</span></label>
        <input type="number" step="0.01" name="quantity" id="quantity" min="0.01" value="{{ old('quantity', $feeding?->quantity) }}" required>
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
            <select name="livestock_id" id="livestock_id" data-feeding-livestock data-selected="{{ $selectedLivestockId }}">
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
            <select name="animal_id" id="animal_id" data-feeding-animal data-selected="{{ $selectedAnimalId }}">
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
        <textarea name="notes" id="notes" rows="3">{{ old('notes', $feeding?->notes) }}</textarea>
    </div>
</div>
