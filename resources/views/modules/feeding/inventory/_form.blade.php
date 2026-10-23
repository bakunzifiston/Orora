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
                <option value="{{ $type->id }}" data-unit="{{ $type->unit }}" @selected(old('feed_type_id', $feedInventory?->feed_type_id) == $type->id)>
                    {{ $type->name }} ({{ $type->unit }})
                </option>
            @endforeach
        </select>
    </div>
    @unless ($feedInventory)
        <div class="dash-form-field">
            <label for="opening_quantity">{{ __('Quantity in stock now') }} <span data-feed-unit-label></span></label>
            <input type="number" step="0.01" min="0" name="opening_quantity" id="opening_quantity" value="{{ old('opening_quantity') }}" placeholder="0">
            <p class="dash-field-hint">{{ __('How much of this feed you have today. Leave empty to start at zero.') }}</p>
            @error('opening_quantity')<p class="dash-form-error">{{ $message }}</p>@enderror
        </div>
    @endunless
    <div class="dash-form-field">
        <label for="reorder_level">{{ __('Reorder level') }} <span data-feed-unit-label></span></label>
        <input type="number" step="0.01" min="0" name="reorder_level" id="reorder_level" value="{{ old('reorder_level', $feedInventory?->reorder_level) }}">
        <p class="dash-field-hint">{{ __('You get a "Feed inventory low" alert when stock falls to this amount.') }}</p>
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="2">{{ old('notes', $feedInventory?->notes) }}</textarea>
    </div>
</div>

<script>
    (() => {
        const select = document.getElementById('feed_type_id');
        if (!select) return;
        const sync = () => {
            const unit = select.selectedOptions[0]?.dataset.unit;
            document.querySelectorAll('[data-feed-unit-label]').forEach((el) => { el.textContent = unit ? '(' + unit + ')' : ''; });
        };
        select.addEventListener('change', sync);
        sync();
    })();
</script>
