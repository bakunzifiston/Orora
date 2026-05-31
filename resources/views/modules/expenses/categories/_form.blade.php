@php $category = $category ?? null; @endphp
<div class="dash-form-grid">
  <div class="dash-form-field">
    <label for="expense_group">Group <span class="dash-required">*</span></label>
    <select name="expense_group" id="expense_group" required @if($category?->is_system) disabled @endif>
      @foreach (config('modules.expense_groups') as $key => $meta)
        <option value="{{ $key }}" @selected(old('expense_group', $category?->expense_group) === $key)>{{ $meta['label'] }}</option>
      @endforeach
    </select>
    @if ($category?->is_system)
      <input type="hidden" name="expense_group" value="{{ $category->expense_group }}">
    @endif
  </div>
  <div class="dash-form-field">
    <label for="name">Name <span class="dash-required">*</span></label>
    <input type="text" name="name" id="name" value="{{ old('name', $category?->name) }}" required @if($category?->is_system) readonly @endif>
  </div>
  <div class="dash-form-field dash-form-field--full">
    <label for="description">Description</label>
    <textarea name="description" id="description" rows="2">{{ old('description', $category?->description) }}</textarea>
  </div>
  <div class="dash-form-field">
    <label class="dash-checkbox">
      <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category?->is_active ?? true))>
      <span>Active</span>
    </label>
  </div>
</div>
