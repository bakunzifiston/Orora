@php $feedInventory = $feedInventory ?? null; @endphp
<div class="dash-form-grid">
    <div class="dash-form-field">
        <label for="farm_id">Farm <span class="dash-required">*</span></label>
        <select name="farm_id" id="farm_id" required>
            <option value="">Select farm</option>
            @foreach ($farms as $farm)
                <option value="{{ $farm->id }}" @selected(old('farm_id', $feedInventory?->farm_id) == $farm->id)>{{ $farm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="feed_type_id">Feed type <span class="dash-required">*</span></label>
        <select name="feed_type_id" id="feed_type_id" required>
            <option value="">Select feed type</option>
            @foreach ($feedTypes as $type)
                <option value="{{ $type->id }}" @selected(old('feed_type_id', $feedInventory?->feed_type_id) == $type->id)>
                    {{ $type->name }} ({{ $type->unit }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="reorder_level">Reorder level</label>
        <input type="number" step="0.01" min="0" name="reorder_level" id="reorder_level" value="{{ old('reorder_level', $feedInventory?->reorder_level) }}">
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="2">{{ old('notes', $feedInventory?->notes) }}</textarea>
    </div>
</div>
