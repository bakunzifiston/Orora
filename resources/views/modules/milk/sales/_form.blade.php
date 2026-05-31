@php $milkSale = $milkSale ?? null; @endphp

<div class="dash-form-grid">
    <div class="dash-form-field">
        <label for="farm_id">Farm <span class="dash-required">*</span></label>
        <select name="farm_id" id="farm_id" required @disabled($milkSale && $milkSale->status !== 'draft')>
            <option value="">Select farm</option>
            @foreach ($farms as $farm)
                <option value="{{ $farm->id }}" @selected(old('farm_id', $milkSale?->farm_id) == $farm->id)>{{ $farm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="sold_on">Sold on <span class="dash-required">*</span></label>
        <input type="date" name="sold_on" id="sold_on" value="{{ old('sold_on', $milkSale?->sold_on?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required @disabled($milkSale && $milkSale->status !== 'draft')>
    </div>
    <div class="dash-form-field">
        <label for="buyer_name">Buyer <span class="dash-required">*</span></label>
        <input type="text" name="buyer_name" id="buyer_name" value="{{ old('buyer_name', $milkSale?->buyer_name) }}" required @disabled($milkSale && $milkSale->status !== 'draft')>
    </div>
    <div class="dash-form-field">
        <label for="buyer_contact">Contact</label>
        <input type="text" name="buyer_contact" id="buyer_contact" value="{{ old('buyer_contact', $milkSale?->buyer_contact) }}" @disabled($milkSale && $milkSale->status !== 'draft')>
    </div>
    <div class="dash-form-field">
        <label for="currency">Currency</label>
        <input type="text" name="currency" id="currency" value="{{ old('currency', $milkSale?->currency ?? 'RWF') }}" required @disabled($milkSale && $milkSale->status !== 'draft')>
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="2" @disabled($milkSale && $milkSale->status !== 'draft')>{{ old('notes', $milkSale?->notes) }}</textarea>
    </div>
</div>

@if (! $milkSale)
    @component('modules.farms._form-section', ['number' => '2', 'title' => 'First line item', 'description' => 'Optional — add more after saving.'])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="items_0_storage">From tank</label>
                <select name="items[0][milk_storage_id]" id="items_0_storage">
                    <option value="">Select tank</option>
                    @foreach ($storageTanks as $tank)
                        <option value="{{ $tank->id }}">{{ $tank->container_name }} ({{ $tank->current_quantity_liters }} L)</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="items_0_liters">Liters</label>
                <input type="number" step="0.01" min="0.01" name="items[0][quantity_liters]" id="items_0_liters">
            </div>
            <div class="dash-form-field">
                <label for="items_0_price">Price / liter</label>
                <input type="number" step="0.01" min="0" name="items[0][unit_price]" id="items_0_price">
            </div>
        </div>
    @endcomponent
@endif
