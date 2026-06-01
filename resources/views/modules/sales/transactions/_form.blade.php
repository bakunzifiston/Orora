@php
    $transaction = $transaction ?? null;
    $saleType = old('sale_type', $transaction?->sale_type ?? ($saleType ?? 'animal_sale'));
@endphp
<div class="dash-form-grid">
    <div class="dash-form-field">
        <label for="farm_id">Farm <span class="dash-required">*</span></label>
        <select name="farm_id" id="farm_id" required @disabled($transaction && $transaction->sale_status !== 'draft')>
            <option value="">Select farm</option>
            @foreach ($farms as $farm)
                <option value="{{ $farm->id }}" @selected(old('farm_id', $transaction?->farm_id) == $farm->id)>{{ $farm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="sale_type">Sale type <span class="dash-required">*</span></label>
        <select name="sale_type" id="sale_type" required @disabled($transaction)>
            @foreach (config('modules.sale_type_labels') as $value => $typeLabel)
                <option value="{{ $value }}" @selected($saleType === $value)>{{ $typeLabel }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="sale_date">Sale date <span class="dash-required">*</span></label>
        <input type="date" name="sale_date" id="sale_date" value="{{ old('sale_date', $transaction?->sale_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required @disabled($transaction && $transaction->sale_status !== 'draft')>
    </div>
    <div class="dash-form-field dash-form-field--full">
        @include('modules.sales.partials.customer-fields')
    </div>
    <div class="dash-form-field">
        <label for="pricing_method">Pricing <span class="dash-required">*</span></label>
        <select name="pricing_method" id="pricing_method" required @disabled($transaction && $transaction->sale_status !== 'draft')>
            @foreach (config('modules.sale_pricing_methods') as $value => $pricingLabel)
                <option value="{{ $value }}" @selected(old('pricing_method', $transaction?->pricing_method ?? 'per_animal') === $value)>{{ $pricingLabel }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="currency">Currency</label>
        <input type="text" name="currency" id="currency" value="{{ old('currency', $transaction?->currency ?? 'RWF') }}" maxlength="3" required @disabled($transaction && $transaction->sale_status !== 'draft')>
    </div>
    <div class="dash-form-field">
        <label for="delivery_method">Delivery</label>
        <select name="delivery_method" id="delivery_method" @disabled($transaction && $transaction->sale_status !== 'draft')>
            <option value="">Not set</option>
            @foreach (config('modules.sale_delivery_methods') as $method)
                <option value="{{ $method }}" @selected(old('delivery_method', $transaction?->delivery_method) === $method)>{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="discount_amount">Discount</label>
        <input type="number" step="0.01" min="0" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', $transaction?->discount_amount ?? 0) }}" @disabled($transaction && $transaction->sale_status !== 'draft')>
    </div>
    <div class="dash-form-field">
        <label for="tax_amount">Tax</label>
        <input type="number" step="0.01" min="0" name="tax_amount" id="tax_amount" value="{{ old('tax_amount', $transaction?->tax_amount ?? 0) }}" @disabled($transaction && $transaction->sale_status !== 'draft')>
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="2" @disabled($transaction && $transaction->sale_status !== 'draft')>{{ old('notes', $transaction?->notes) }}</textarea>
    </div>
</div>
