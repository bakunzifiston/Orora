@php $supplier = $supplier ?? null; @endphp
<div class="dash-form-grid">
    <div class="dash-form-field">
        <label for="name">Name <span class="dash-required">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name', $supplier?->name) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="contact_person">Contact person</label>
        <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person', $supplier?->contact_person) }}">
    </div>
    <div class="dash-form-field">
        <label for="phone">Phone</label>
        <input type="text" name="phone" id="phone" value="{{ old('phone', $supplier?->phone) }}">
    </div>
    <div class="dash-form-field">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email', $supplier?->email) }}">
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="address">Address</label>
        <input type="text" name="address" id="address" value="{{ old('address', $supplier?->address) }}">
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="3">{{ old('notes', $supplier?->notes) }}</textarea>
    </div>
    <div class="dash-form-field">
        <label class="dash-checkbox">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplier?->is_active ?? true))>
            <span>Active supplier</span>
        </label>
    </div>
</div>
