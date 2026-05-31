@php $vendor = $vendor ?? null; @endphp
<div class="dash-form-grid">
    <div class="dash-form-field">
        <label for="name">Name <span class="dash-required">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name', $vendor?->name) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="contact_person">Contact person</label>
        <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person', $vendor?->contact_person) }}">
    </div>
    <div class="dash-form-field">
        <label for="phone">Phone</label>
        <input type="text" name="phone" id="phone" value="{{ old('phone', $vendor?->phone) }}">
    </div>
    <div class="dash-form-field">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email', $vendor?->email) }}">
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="2">{{ old('notes', $vendor?->notes) }}</textarea>
    </div>
    <div class="dash-form-field">
        <label class="dash-checkbox">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $vendor?->is_active ?? true))>
            <span>Active vendor</span>
        </label>
    </div>
</div>
