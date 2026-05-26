@php $schedule = $schedule ?? null; @endphp
<div class="dash-form-grid">
    <div class="dash-form-field">
        <label for="farm_id">Farm <span class="dash-required">*</span></label>
        <select name="farm_id" id="farm_id" required>
            <option value="">Select farm</option>
            @foreach ($farms as $farm)
                <option value="{{ $farm->id }}" @selected(old('farm_id', $schedule?->farm_id) == $farm->id)>{{ $farm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="feed_type_id">Feed type <span class="dash-required">*</span></label>
        <select name="feed_type_id" id="feed_type_id" required>
            <option value="">Select feed type</option>
            @foreach ($feedTypes as $type)
                <option value="{{ $type->id }}" @selected(old('feed_type_id', $schedule?->feed_type_id) == $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="feed_inventory_id">Inventory (optional)</label>
        <select name="feed_inventory_id" id="feed_inventory_id">
            <option value="">Auto-select for farm</option>
            @foreach ($inventories as $inv)
                <option value="{{ $inv->id }}" @selected(old('feed_inventory_id', $schedule?->feed_inventory_id) == $inv->id)>
                    {{ $inv->farm->name }} — {{ $inv->feedType->name }} ({{ $inv->quantity_on_hand }} {{ $inv->unit }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="quantity">Quantity <span class="dash-required">*</span></label>
        <input type="number" step="0.01" min="0.01" name="quantity" id="quantity" value="{{ old('quantity', $schedule?->quantity) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="unit">Unit <span class="dash-required">*</span></label>
        <select name="unit" id="unit" required>
            @foreach (config('modules.feed_units') as $unit)
                <option value="{{ $unit }}" @selected(old('unit', $schedule?->unit ?? 'kg') === $unit)>{{ $unit }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="frequency">Frequency <span class="dash-required">*</span></label>
        <select name="frequency" id="frequency" required>
            @foreach (config('modules.schedule_frequencies') as $frequency)
                <option value="{{ $frequency }}" @selected(old('frequency', $schedule?->frequency ?? 'daily') === $frequency)>{{ ucfirst($frequency) }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="scheduled_time">Time</label>
        <input type="time" name="scheduled_time" id="scheduled_time" value="{{ old('scheduled_time', $schedule?->scheduled_time ? \Illuminate\Support\Carbon::parse($schedule->scheduled_time)->format('H:i') : '') }}">
    </div>
    <div class="dash-form-field">
        <label for="start_date">Start date <span class="dash-required">*</span></label>
        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $schedule?->start_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="end_date">End date</label>
        <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $schedule?->end_date?->format('Y-m-d')) }}">
    </div>
    <div class="dash-form-field">
        <label for="next_due_date">Next due date</label>
        <input type="date" name="next_due_date" id="next_due_date" value="{{ old('next_due_date', $schedule?->next_due_date?->format('Y-m-d')) }}">
    </div>
    <div class="dash-form-field">
        <label for="status">Status <span class="dash-required">*</span></label>
        <select name="status" id="status" required>
            @foreach (config('modules.schedule_statuses') as $status)
                <option value="{{ $status }}" @selected(old('status', $schedule?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="livestock_id">Livestock group</label>
        <select name="livestock_id" id="livestock_id">
            <option value="">None</option>
            @foreach ($livestockGroups as $group)
                <option value="{{ $group->id }}" @selected(old('livestock_id', $schedule?->livestock_id) == $group->id)>{{ $group->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="animal_id">Animal</label>
        <select name="animal_id" id="animal_id">
            <option value="">None</option>
            @foreach ($animals as $animal)
                <option value="{{ $animal->id }}" @selected(old('animal_id', $schedule?->animal_id) == $animal->id)>{{ $animal->tag_number }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="2">{{ old('notes', $schedule?->notes) }}</textarea>
    </div>
</div>
