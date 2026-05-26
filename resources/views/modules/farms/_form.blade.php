@php
    $farm = $farm ?? null;
    $members = old('members', $farm?->members?->map(fn ($m) => [
        'first_name' => $m->first_name,
        'last_name' => $m->last_name,
        'date_of_birth' => $m->date_of_birth?->format('Y-m-d'),
        'phone' => $m->phone,
        'gender' => $m->gender,
    ])->toArray() ?? []);
    $ownershipType = old('ownership_type', $farm?->ownership_type ?? 'sole_proprietor');
@endphp

<div
    class="farm-registration"
    data-farm-form
    data-initial-province="{{ old('province_code', $farm?->province_code) }}"
    data-initial-district="{{ old('district_code', $farm?->district_code) }}"
    data-initial-sector="{{ old('sector_code', $farm?->sector_code) }}"
    data-initial-cell="{{ old('cell_code', $farm?->cell_code) }}"
    data-initial-village="{{ old('village_code', $farm?->village_code) }}"
>
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => 'Farm registration',
        'description' => 'Official farm identity and registration status.',
        'id' => 'section-farm-registration',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="name">Farm name <span class="dash-required">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $farm?->name) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="registration_number">Registration number <span class="dash-required">*</span></label>
                <input type="text" name="registration_number" id="registration_number" value="{{ old('registration_number', $farm?->registration_number) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="registration_date">Registration date <span class="dash-required">*</span></label>
                <input type="date" name="registration_date" id="registration_date" value="{{ old('registration_date', $farm?->registration_date?->format('Y-m-d')) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="farm_size_hectares">Farm size (hectares) <span class="dash-required">*</span></label>
                <input type="number" step="0.01" name="farm_size_hectares" id="farm_size_hectares" min="0" value="{{ old('farm_size_hectares', $farm?->farm_size_hectares) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="status">Status <span class="dash-required">*</span></label>
                <select name="status" id="status" required>
                    @foreach (config('modules.farm_statuses') as $status)
                        <option value="{{ $status }}" @selected(old('status', $farm?->status ?? 'pending') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '2',
        'title' => 'Location',
        'description' => 'Farm address in Rwanda — select province through village.',
        'id' => 'section-location',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="country">Country <span class="dash-required">*</span></label>
                <input type="text" name="country" id="country" value="{{ old('country', $farm?->country ?? 'Rwanda') }}" required readonly>
            </div>
            <div class="dash-form-field">
                <label for="province_code">Province <span class="dash-required">*</span></label>
                <select name="province_code" id="province_code" required data-location-province>
                    <option value="">Select province</option>
                    @foreach ($provinces as $province)
                        <option value="{{ $province['code'] }}" @selected(old('province_code', $farm?->province_code) == $province['code'])>{{ $province['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="district_code">District <span class="dash-required">*</span></label>
                <select name="district_code" id="district_code" required data-location-district disabled>
                    <option value="">Select district</option>
                </select>
            </div>
            <div class="dash-form-field">
                <label for="sector_code">Sector <span class="dash-required">*</span></label>
                <select name="sector_code" id="sector_code" required data-location-sector disabled>
                    <option value="">Select sector</option>
                </select>
            </div>
            <div class="dash-form-field">
                <label for="cell_code">Cell <span class="dash-required">*</span></label>
                <select name="cell_code" id="cell_code" required data-location-cell disabled>
                    <option value="">Select cell</option>
                </select>
            </div>
            <div class="dash-form-field">
                <label for="village_code">Village <span class="dash-required">*</span></label>
                <select name="village_code" id="village_code" required data-location-village disabled>
                    <option value="">Select village</option>
                </select>
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '3',
        'title' => 'Ownership structure',
        'description' => 'Legal ownership type and organization details when applicable.',
        'id' => 'section-ownership',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field dash-form-field--full">
                <label for="ownership_type">Ownership type</label>
                <select name="ownership_type" id="ownership_type" data-ownership-type>
                    @foreach (config('modules.ownership_types') as $value => $label)
                        <option value="{{ $value }}" @selected($ownershipType === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field" data-org-field hidden>
                <label for="organization_name">Organization name <span class="dash-required">*</span></label>
                <input type="text" name="organization_name" id="organization_name" value="{{ old('organization_name', $farm?->organization_name) }}">
            </div>
            <div class="dash-form-field" data-org-field hidden>
                <label for="tax_id">Tax ID (TIN) <span class="dash-required">*</span></label>
                <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id', $farm?->tax_id) }}">
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '4',
        'title' => 'Business owner',
        'description' => 'Primary owner or authorized representative personal details.',
        'id' => 'section-owner',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="owner_first_name">First name <span class="dash-required">*</span></label>
                <input type="text" name="owner_first_name" id="owner_first_name" value="{{ old('owner_first_name', $farm?->owner_first_name) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="owner_last_name">Last name <span class="dash-required">*</span></label>
                <input type="text" name="owner_last_name" id="owner_last_name" value="{{ old('owner_last_name', $farm?->owner_last_name) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="owner_national_id">National ID <span class="dash-required">*</span></label>
                <input type="text" name="owner_national_id" id="owner_national_id" value="{{ old('owner_national_id', $farm?->owner_national_id) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="owner_dob">Date of birth <span class="dash-required">*</span></label>
                <input type="date" name="owner_dob" id="owner_dob" value="{{ old('owner_dob', $farm?->owner_dob?->format('Y-m-d')) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="owner_gender">Gender <span class="dash-required">*</span></label>
                <select name="owner_gender" id="owner_gender" required>
                    @foreach (config('modules.genders') as $gender)
                        <option value="{{ $gender }}" @selected(old('owner_gender', $farm?->owner_gender) === $gender)>{{ ucfirst($gender) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '5',
        'title' => 'Contact information',
        'description' => 'Phone, email, and emergency contact for the owner.',
        'id' => 'section-contact',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="contact_phone">Contact phone <span class="dash-required">*</span></label>
                <input type="tel" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $farm?->contact_phone) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="contact_email">Email <span class="dash-required">*</span></label>
                <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $farm?->contact_email) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="owner_emergency_contact">Emergency contact <span class="dash-required">*</span></label>
                <input type="tel" name="owner_emergency_contact" id="owner_emergency_contact" value="{{ old('owner_emergency_contact', $farm?->owner_emergency_contact) }}" required>
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '6',
        'title' => 'Additional notes',
        'description' => 'Optional remarks about this farm registration.',
        'id' => 'section-notes',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field dash-form-field--full">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="3" placeholder="Any extra information for auditors or administrators…">{{ old('notes', $farm?->notes) }}</textarea>
            </div>
        </div>
    @endcomponent

    <section class="dash-form-section" id="section-members" data-members-section hidden>
        <header class="dash-form-section__head dash-form-section__head--split">
            <div class="dash-form-section__head-main">
                <span class="dash-form-section__number" aria-hidden="true">7</span>
                <div class="dash-form-section__titles">
                    <h2 class="dash-form-section-title">Organization members</h2>
                    <p class="dash-form-section-hint">Required for cooperatives and companies — add all registered members.</p>
                </div>
            </div>
            <button type="button" class="dash-btn-save dash-btn-save--sm" data-add-member>+ Add member</button>
        </header>
        <div class="dash-form-section__body">
            <div data-members-list>
                @forelse ($members as $index => $member)
                    @include('modules.farms._member-row', ['index' => $index, 'member' => $member])
                @empty
                    @include('modules.farms._member-row', ['index' => 0, 'member' => []])
                @endforelse
            </div>
        </div>
    </section>

    <template id="farm-member-template">
        @include('modules.farms._member-row', ['index' => '__INDEX__', 'member' => []])
    </template>
</div>
