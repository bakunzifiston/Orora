@php $member = $member ?? []; @endphp
<div class="dash-member-card" data-member-row>
    <div class="dash-member-card__header">
        <strong>{{ __('Member') }}</strong>
        <button type="button" class="dash-member-remove" data-remove-member aria-label="{{ __('Remove member') }}">&times;</button>
    </div>
    <div class="dash-form-grid">
        <div class="dash-form-field">
            <label>{{ __('First name') }} <span class="dash-required">*</span></label>
            <input type="text" name="members[{{ $index }}][first_name]" value="{{ $member['first_name'] ?? '' }}" data-member-required>
        </div>
        <div class="dash-form-field">
            <label>{{ __('Last name') }} <span class="dash-required">*</span></label>
            <input type="text" name="members[{{ $index }}][last_name]" value="{{ $member['last_name'] ?? '' }}" data-member-required>
        </div>
        <div class="dash-form-field">
            <label>{{ __('Date of birth') }} <span class="dash-required">*</span></label>
            <input type="date" name="members[{{ $index }}][date_of_birth]" value="{{ $member['date_of_birth'] ?? '' }}" data-member-required>
        </div>
        <div class="dash-form-field">
            <label>{{ __('Phone') }} <span class="dash-required">*</span></label>
            <input type="tel" name="members[{{ $index }}][phone]" value="{{ $member['phone'] ?? '' }}" data-member-required>
        </div>
        <div class="dash-form-field">
            <label>{{ __('Gender') }} <span class="dash-required">*</span></label>
            <select name="members[{{ $index }}][gender]" data-member-required>
                <option value="">{{ __('Select') }}</option>
                @foreach (config('modules.genders') as $gender)
                    <option value="{{ $gender }}" @selected(($member['gender'] ?? '') === $gender)>{{ ucfirst($gender) }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
