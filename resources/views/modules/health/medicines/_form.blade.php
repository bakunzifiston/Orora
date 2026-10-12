@php $medicine = $medicine ?? null; @endphp

<div class="dash-form-grid">
    <div class="dash-form-field">
        <label for="name">{{ __('Medicine name') }} <span class="dash-required">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name', $medicine?->name) }}" required placeholder="e.g. Oxytetracycline">
        @error('name')<p class="dash-form-error">{{ $message }}</p>@enderror
    </div>
    @include('modules.partials.select-with-other', [
        'name' => 'category',
        'otherName' => 'category_other',
        'id' => 'category',
        'label' => __('Category'),
        'options' => config('modules.medicine_categories'),
        'value' => $medicine?->category,
        'allowEmpty' => true,
        'emptyLabel' => __('Not set'),
    ])
    <div class="dash-form-field">
        <label for="active_ingredient">{{ __('Active ingredient') }}</label>
        <input type="text" name="active_ingredient" id="active_ingredient" value="{{ old('active_ingredient', $medicine?->active_ingredient) }}">
    </div>
    @include('modules.partials.select-with-other', [
        'name' => 'administration_method',
        'otherName' => 'administration_method_other',
        'id' => 'administration_method',
        'label' => __('Administration method'),
        'options' => config('modules.administration_methods'),
        'value' => $medicine?->administration_method,
        'allowEmpty' => true,
        'emptyLabel' => __('Not set'),
    ])
    <div class="dash-form-field">
        <label for="unit">{{ __('Unit') }} <span class="dash-required">*</span></label>
        <select name="unit" id="unit" required>
            @foreach (config('modules.medicine_units') as $unit)
                <option value="{{ $unit }}" @selected(old('unit', $medicine?->unit ?? 'ml') === $unit)>{{ $unit }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="quantity_on_hand">{{ __('Quantity in stock') }} <span class="dash-required">*</span></label>
        <input type="number" step="0.01" min="0" name="quantity_on_hand" id="quantity_on_hand" value="{{ old('quantity_on_hand', $medicine?->quantity_on_hand ?? 0) }}" required>
        <p class="dash-field-hint">
            @if ($medicine)
                {{ __('To restock later, open the medicine and use Add stock.') }}
            @else
                {{ __('Opening stock. You can add more later from the medicine page.') }}
            @endif
        </p>
        @error('quantity_on_hand')<p class="dash-form-error">{{ $message }}</p>@enderror
    </div>
    <div class="dash-form-field">
        <label for="reorder_level">{{ __('Reorder level') }}</label>
        <input type="number" step="0.01" min="0" name="reorder_level" id="reorder_level" value="{{ old('reorder_level', $medicine?->reorder_level) }}">
        <p class="dash-field-hint">{{ __('Alert when stock reaches this amount or lower.') }}</p>
    </div>
    <div class="dash-form-field">
        <label for="expiry_date">{{ __('Expiry date') }}</label>
        <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date', $medicine?->expiry_date?->format('Y-m-d')) }}">
    </div>
    <div class="dash-form-field">
        <label for="supplier">{{ __('Supplier') }}</label>
        <input type="text" name="supplier" id="supplier" value="{{ old('supplier', $medicine?->supplier) }}">
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="dosage_instructions">{{ __('Dosage instructions') }}</label>
        <textarea name="dosage_instructions" id="dosage_instructions" rows="2">{{ old('dosage_instructions', $medicine?->dosage_instructions) }}</textarea>
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">{{ __('Notes') }}</label>
        <textarea name="notes" id="notes" rows="2">{{ old('notes', $medicine?->notes) }}</textarea>
    </div>
    <div class="dash-form-field">
        <label class="dash-checkbox">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $medicine?->is_active ?? true))>
            <span>{{ __('Active medicine') }}</span>
        </label>
    </div>
</div>
