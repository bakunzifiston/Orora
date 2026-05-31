@php
    $customer = $customer ?? null;
    $profile = $customer?->profile;
    $type = old('customer_type', $customer?->customer_type ?? 'individual');
@endphp

<div class="dash-form-grid">
    <div class="dash-form-field">
        <label for="customer_type">Customer type <span class="dash-required">*</span></label>
        <select name="customer_type" id="customer_type" required @disabled($customer)>
            @foreach (config('modules.customer_types') as $value => $label)
                <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="display_name">Display name <span class="dash-required">*</span></label>
        <input type="text" name="display_name" id="display_name" value="{{ old('display_name', $customer?->display_name) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="status">Status <span class="dash-required">*</span></label>
        <select name="status" id="status" required>
            @foreach (config('modules.customer_statuses') as $status)
                <option value="{{ $status }}" @selected(old('status', $customer?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="trust_level">Trust level <span class="dash-required">*</span></label>
        <select name="trust_level" id="trust_level" required>
            @foreach (config('modules.customer_trust_levels') as $value => $label)
                <option value="{{ $value }}" @selected(old('trust_level', $customer?->trust_level ?? 'new') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="preferred_payment_method">Preferred payment</label>
        <select name="preferred_payment_method" id="preferred_payment_method">
            <option value="">Not set</option>
            @foreach (config('modules.expense_payment_methods') as $method)
                <option value="{{ $method }}" @selected(old('preferred_payment_method', $customer?->preferred_payment_method) === $method)>{{ $method }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="currency">Currency</label>
        <input type="text" name="currency" id="currency" value="{{ old('currency', $customer?->currency ?? 'RWF') }}" maxlength="3" required>
    </div>
</div>

<div class="dash-panel" style="margin: 1.25rem 0;" data-profile-section="individual" @if($type !== 'individual') hidden @endif>
    <div class="dash-panel-title">Individual details</div>
    <div class="dash-form-grid">
        <div class="dash-form-field">
            <label for="first_name">First name</label>
            <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $profile?->first_name) }}">
        </div>
        <div class="dash-form-field">
            <label for="last_name">Last name</label>
            <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $profile?->last_name) }}">
        </div>
        <div class="dash-form-field">
            <label for="national_id">National ID</label>
            <input type="text" name="national_id" id="national_id" value="{{ old('national_id', $profile?->national_id) }}">
        </div>
        <div class="dash-form-field">
            <label for="date_of_birth">Date of birth</label>
            <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $profile?->date_of_birth?->format('Y-m-d')) }}">
        </div>
        <div class="dash-form-field">
            <label for="gender">Gender</label>
            <select name="gender" id="gender">
                <option value="">Not set</option>
                @foreach (config('modules.customer_genders') as $value => $label)
                    <option value="{{ $value }}" @selected(old('gender', $profile?->gender) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="dash-panel" style="margin: 1.25rem 0;" data-profile-section="organization" @if($type === 'individual') hidden @endif>
    <div class="dash-panel-title">Organization details</div>
    <div class="dash-form-grid">
        <div class="dash-form-field">
            <label for="organization_name">Organization name</label>
            <input type="text" name="organization_name" id="organization_name" value="{{ old('organization_name', $profile?->organization_name) }}">
        </div>
        <div class="dash-form-field">
            <label for="registration_number">Registration number</label>
            <input type="text" name="registration_number" id="registration_number" value="{{ old('registration_number', $profile?->registration_number) }}">
        </div>
        <div class="dash-form-field">
            <label for="tax_id">Tax ID</label>
            <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id', $profile?->tax_id) }}">
        </div>
        <div class="dash-form-field">
            <label for="license_number">License number</label>
            <input type="text" name="license_number" id="license_number" value="{{ old('license_number', $profile?->license_number) }}">
        </div>
        <div class="dash-form-field">
            <label for="license_expiry_date">License expiry</label>
            <input type="date" name="license_expiry_date" id="license_expiry_date" value="{{ old('license_expiry_date', $profile?->license_expiry_date?->format('Y-m-d')) }}">
        </div>
        <div class="dash-form-field">
            <label for="website">Website</label>
            <input type="text" name="website" id="website" value="{{ old('website', $profile?->website) }}">
        </div>
        <div class="dash-form-field">
            <label for="industry">Industry</label>
            <input type="text" name="industry" id="industry" value="{{ old('industry', $profile?->industry) }}">
        </div>
        <div class="dash-form-field">
            <label for="number_of_employees">Employees</label>
            <input type="number" min="0" name="number_of_employees" id="number_of_employees" value="{{ old('number_of_employees', $profile?->number_of_employees) }}">
        </div>
        <div class="dash-form-field">
            <label for="established_date">Established</label>
            <input type="date" name="established_date" id="established_date" value="{{ old('established_date', $profile?->established_date?->format('Y-m-d')) }}">
        </div>
    </div>
</div>

@if (! $customer)
    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Primary contact (optional)</div>
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="contact_name">Contact name</label>
                <input type="text" name="contact_name" id="contact_name" value="{{ old('contact_name') }}">
            </div>
            <div class="dash-form-field">
                <label for="contact_role">Role</label>
                <input type="text" name="contact_role" id="contact_role" value="{{ old('contact_role') }}">
            </div>
            <div class="dash-form-field">
                <label for="contact_phone">Phone</label>
                <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone') }}">
            </div>
            <div class="dash-form-field">
                <label for="contact_email">Email</label>
                <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email') }}">
            </div>
        </div>
    </div>
@endif

<div class="dash-form-field dash-form-field--full">
    <label for="notes">Notes</label>
    <textarea name="notes" id="notes" rows="2">{{ old('notes', $customer?->notes) }}</textarea>
</div>

<script>
    document.getElementById('customer_type')?.addEventListener('change', function () {
        const isIndividual = this.value === 'individual';
        document.querySelector('[data-profile-section="individual"]').hidden = !isIndividual;
        document.querySelector('[data-profile-section="organization"]').hidden = isIndividual;
    });
</script>
