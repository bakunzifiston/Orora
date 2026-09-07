@php $movement = $movement ?? null; @endphp

<div class="dash-form-grid">
    @include('modules.partials.stock-picker', ['record' => $movement])
    <div class="dash-form-field">
        <label for="quantity">{{ __('Quantity') }}</label>
        <input type="number" name="quantity" id="quantity" min="1" value="{{ old('quantity', $movement?->quantity ?? 1) }}">
        <p class="dash-field-hint">{{ __('Heads moved. Use 1 for a single animal.') }}</p>
    </div>
    <div class="dash-form-field">
        <label for="movement_type">Movement type</label>
        <select name="movement_type" id="movement_type" required>
            @foreach (config('modules.movement_types') as $type)
                <option value="{{ $type }}" @selected(old('movement_type', $movement?->movement_type) === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="from_farm_id">From farm</label>
        <select name="from_farm_id" id="from_farm_id" required>
            <option value="">Select farm</option>
            @foreach ($farms as $farm)
                <option value="{{ $farm->id }}" @selected(old('from_farm_id', $movement?->from_farm_id) == $farm->id)>{{ $farm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="to_farm_id">To farm (optional)</label>
        <select name="to_farm_id" id="to_farm_id">
            <option value="">None</option>
            @foreach ($farms as $farm)
                <option value="{{ $farm->id }}" @selected(old('to_farm_id', $movement?->to_farm_id) == $farm->id)>{{ $farm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="moved_on">Moved on</label>
        <input type="date" name="moved_on" id="moved_on" value="{{ old('moved_on', $movement?->moved_on?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="reference">Reference</label>
        <input type="text" name="reference" id="reference" value="{{ old('reference', $movement?->reference) }}" placeholder="Permit or document ref">
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="3">{{ old('notes', $movement?->notes) }}</textarea>
    </div>
</div>
