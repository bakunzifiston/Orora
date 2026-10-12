@php
    $feedType = $feedType ?? null;
    $suppliers = $suppliers ?? collect();
@endphp
<div class="dash-form-grid">
    <div class="dash-form-field">
        <label for="feed_supplier_id">{{ __('Supplier') }} <span class="dash-field-optional">({{ __('optional') }})</span></label>
        <select name="feed_supplier_id" id="feed_supplier_id">
            <option value="">{{ __('No supplier') }}</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected(old('feed_supplier_id', $feedType?->feed_supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
        @if ($suppliers->isEmpty())
            <p class="dash-field-hint">
                {{ __('No suppliers yet. You can save this feed type without one.') }}
                <a href="{{ route('feeding.suppliers.create') }}">{{ __('Add a supplier') }}</a>
            </p>
        @else
            <p class="dash-field-hint">{{ __('Leave as “No supplier” if this feed is mixed on-farm or the source is unknown.') }}</p>
        @endif
        @error('feed_supplier_id')<p class="dash-form-error">{{ $message }}</p>@enderror
    </div>
    <div class="dash-form-field">
        <label for="name">{{ __('Feed type name') }} <span class="dash-required">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name', $feedType?->name) }}" required>
        @error('name')<p class="dash-form-error">{{ $message }}</p>@enderror
    </div>
    @include('modules.partials.select-with-other', [
        'name' => 'category',
        'otherName' => 'category_other',
        'id' => 'category',
        'label' => __('Category'),
        'options' => config('modules.feed_type_categories'),
        'value' => $feedType?->category,
        'allowEmpty' => true,
        'emptyLabel' => __('Not set'),
    ])
    <div class="dash-form-field">
        <label for="unit">{{ __('Unit') }} <span class="dash-required">*</span></label>
        <select name="unit" id="unit" required>
            @foreach (config('modules.feed_units') as $unit)
                <option value="{{ $unit }}" @selected(old('unit', $feedType?->unit ?? 'kg') === $unit)>{{ $unit }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="description">{{ __('Description') }}</label>
        <textarea name="description" id="description" rows="2">{{ old('description', $feedType?->description) }}</textarea>
    </div>
    <div class="dash-form-field">
        <label class="dash-checkbox">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $feedType?->is_active ?? true))>
            <span>{{ __('Active feed type') }}</span>
        </label>
    </div>
</div>
