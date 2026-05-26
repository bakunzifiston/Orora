@php $feedType = $feedType ?? null; @endphp
<div class="dash-form-grid">
    <div class="dash-form-field">
        <label for="feed_supplier_id">Supplier</label>
        <select name="feed_supplier_id" id="feed_supplier_id">
            <option value="">No supplier</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected(old('feed_supplier_id', $feedType?->feed_supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="name">Feed type name <span class="dash-required">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name', $feedType?->name) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="category">Category</label>
        <select name="category" id="category">
            <option value="">Not set</option>
            @foreach (config('modules.feed_type_categories') as $category)
                <option value="{{ $category }}" @selected(old('category', $feedType?->category) === $category)>{{ $category }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="unit">Unit <span class="dash-required">*</span></label>
        <select name="unit" id="unit" required>
            @foreach (config('modules.feed_units') as $unit)
                <option value="{{ $unit }}" @selected(old('unit', $feedType?->unit ?? 'kg') === $unit)>{{ $unit }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="description">Description</label>
        <textarea name="description" id="description" rows="2">{{ old('description', $feedType?->description) }}</textarea>
    </div>
    <div class="dash-form-field">
        <label class="dash-checkbox">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $feedType?->is_active ?? true))>
            <span>Active feed type</span>
        </label>
    </div>
</div>
