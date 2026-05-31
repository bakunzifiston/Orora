@php $milkStorage = $milkStorage ?? null; @endphp

<div class="dash-form-grid">
    <div class="dash-form-field">
        <label for="farm_id">Farm <span class="dash-required">*</span></label>
        <select name="farm_id" id="farm_id" required>
            <option value="">Select farm</option>
            @foreach ($farms as $farm)
                <option value="{{ $farm->id }}" @selected(old('farm_id', $milkStorage?->farm_id) == $farm->id)>{{ $farm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="container_name">Container name <span class="dash-required">*</span></label>
        <input type="text" name="container_name" id="container_name" value="{{ old('container_name', $milkStorage?->container_name) }}" required placeholder="e.g. Tank A">
    </div>
    <div class="dash-form-field">
        <label for="container_type">Type <span class="dash-required">*</span></label>
        <select name="container_type" id="container_type" required>
            @foreach (config('modules.milk_storage_container_types') as $type)
                <option value="{{ $type }}" @selected(old('container_type', $milkStorage?->container_type ?? 'bulk_tank') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="capacity_liters">Capacity (liters) <span class="dash-required">*</span></label>
        <input type="number" step="0.01" min="1" name="capacity_liters" id="capacity_liters" value="{{ old('capacity_liters', $milkStorage?->capacity_liters) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="storage_temperature">Temperature (°C)</label>
        <input type="number" step="0.1" name="storage_temperature" id="storage_temperature" value="{{ old('storage_temperature', $milkStorage?->storage_temperature) }}">
    </div>
    <div class="dash-form-field">
        <label for="storage_location">Location</label>
        <input type="text" name="storage_location" id="storage_location" value="{{ old('storage_location', $milkStorage?->storage_location) }}">
    </div>
    @if ($milkStorage)
        <div class="dash-form-field">
            <label for="status">Status</label>
            <select name="status" id="status">
                @foreach (config('modules.milk_storage_statuses') as $status)
                    <option value="{{ $status }}" @selected(old('status', $milkStorage->status) === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="dash-form-field">
            <label>Current quantity</label>
            <input type="text" readonly value="{{ number_format($milkStorage->current_quantity_liters, 2) }} L">
        </div>
    @endif
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="2">{{ old('notes', $milkStorage?->notes) }}</textarea>
    </div>
</div>
