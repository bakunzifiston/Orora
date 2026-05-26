@php $feeding = $feeding ?? null; @endphp

<div class="dash-form-grid">
    <div class="dash-form-field">
        <label for="farm_id">Farm <span class="dash-required">*</span></label>
        <select name="farm_id" id="farm_id" required>
            <option value="">Select farm</option>
            @foreach ($farms as $farm)
                <option value="{{ $farm->id }}" @selected(old('farm_id', $feeding?->farm_id) == $farm->id)>{{ $farm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="feed_inventory_id">Feed inventory <span class="dash-required">*</span></label>
        <select name="feed_inventory_id" id="feed_inventory_id" required>
            <option value="">Select stock item</option>
            @foreach ($inventories as $inventory)
                <option
                    value="{{ $inventory->id }}"
                    data-farm="{{ $inventory->farm_id }}"
                    @selected(old('feed_inventory_id', $feeding?->feed_inventory_id) == $inventory->id)
                >
                    {{ $inventory->farm->name }} — {{ $inventory->feedType->name }} ({{ $inventory->quantity_on_hand }} {{ $inventory->unit }})
                </option>
            @endforeach
        </select>
        <p class="dash-field-hint">Consuming a record deducts stock via inventory movements.</p>
    </div>
    <div class="dash-form-field">
        <label for="feeding_schedule_id">From schedule (optional)</label>
        <select name="feeding_schedule_id" id="feeding_schedule_id">
            <option value="">None</option>
            @foreach ($schedules as $schedule)
                <option value="{{ $schedule->id }}" @selected(old('feeding_schedule_id', $feeding?->feeding_schedule_id) == $schedule->id)>
                    {{ $schedule->farm->name }} — {{ $schedule->feedType->name }} ({{ $schedule->quantity }} {{ $schedule->unit }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="fed_on">Fed on <span class="dash-required">*</span></label>
        <input type="date" name="fed_on" id="fed_on" value="{{ old('fed_on', $feeding?->fed_on?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="quantity">Quantity <span class="dash-required">*</span></label>
        <input type="number" step="0.01" name="quantity" id="quantity" min="0.01" value="{{ old('quantity', $feeding?->quantity) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="livestock_id">Livestock group (optional)</label>
        <select name="livestock_id" id="livestock_id">
            <option value="">None</option>
            @foreach ($livestockGroups as $group)
                <option value="{{ $group->id }}" @selected(old('livestock_id', $feeding?->livestock_id) == $group->id)>{{ $group->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="animal_id">Animal (optional)</label>
        <select name="animal_id" id="animal_id">
            <option value="">None</option>
            @foreach ($animals as $animal)
                <option value="{{ $animal->id }}" @selected(old('animal_id', $feeding?->animal_id) == $animal->id)>{{ $animal->tag_number }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="3">{{ old('notes', $feeding?->notes) }}</textarea>
    </div>
</div>
