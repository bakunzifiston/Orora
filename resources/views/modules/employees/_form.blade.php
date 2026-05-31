@php
    $employee = $employee ?? null;
    $profile = $employee?->profile;
    $payroll = $employee?->payroll;
@endphp

<div class="dash-form-grid">
    <div class="dash-form-field">
        <label for="display_name">Display name</label>
        <input type="text" name="display_name" id="display_name" value="{{ old('display_name', $employee?->display_name) }}" placeholder="Auto-filled from first + last name if blank">
    </div>
    <div class="dash-form-field">
        <label for="status">Status <span class="dash-required">*</span></label>
        <select name="status" id="status" required>
            @foreach (config('modules.employee_statuses') as $status)
                <option value="{{ $status }}" @selected(old('status', $employee?->status ?? 'active') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="employment_type">Employment type <span class="dash-required">*</span></label>
        <select name="employment_type" id="employment_type" required>
            @foreach (config('modules.employee_employment_types') as $value => $label)
                <option value="{{ $value }}" @selected(old('employment_type', $employee?->employment_type ?? 'full_time') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="job_role">Job role <span class="dash-required">*</span></label>
        <select name="job_role" id="job_role" required>
            @foreach (config('modules.employee_job_roles') as $value => $label)
                <option value="{{ $value }}" @selected(old('job_role', $employee?->job_role ?? 'farm_worker') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="primary_farm_id">Primary farm</label>
        <select name="primary_farm_id" id="primary_farm_id">
            <option value="">Not assigned</option>
            @foreach ($farms ?? [] as $farm)
                <option value="{{ $farm->id }}" @selected(old('primary_farm_id', $employee?->primary_farm_id) == $farm->id)>{{ $farm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="hire_date">Hire date</label>
        <input type="date" name="hire_date" id="hire_date" value="{{ old('hire_date', $employee?->hire_date?->format('Y-m-d')) }}">
    </div>
    <div class="dash-form-field">
        <label for="termination_date">Termination date</label>
        <input type="date" name="termination_date" id="termination_date" value="{{ old('termination_date', $employee?->termination_date?->format('Y-m-d')) }}">
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="2">{{ old('notes', $employee?->notes) }}</textarea>
    </div>
</div>

<div class="dash-panel" style="margin: 1.25rem 0;">
    <div class="dash-panel-title">Personal details</div>
    <div class="dash-form-grid">
        <div class="dash-form-field">
            <label for="first_name">First name <span class="dash-required">*</span></label>
            <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $profile?->first_name) }}" required>
        </div>
        <div class="dash-form-field">
            <label for="last_name">Last name <span class="dash-required">*</span></label>
            <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $profile?->last_name) }}" required>
        </div>
        <div class="dash-form-field">
            <label for="national_id">National ID</label>
            <input type="text" name="national_id" id="national_id" value="{{ old('national_id', $profile?->national_id) }}">
        </div>
        <div class="dash-form-field">
            <label for="passport_number">Passport</label>
            <input type="text" name="passport_number" id="passport_number" value="{{ old('passport_number', $profile?->passport_number) }}">
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
        <div class="dash-form-field">
            <label for="marital_status">Marital status</label>
            <select name="marital_status" id="marital_status">
                <option value="">Not set</option>
                @foreach (config('modules.employee_marital_statuses') as $value => $label)
                    <option value="{{ $value }}" @selected(old('marital_status', $profile?->marital_status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="dash-form-field">
            <label for="nationality">Nationality</label>
            <input type="text" name="nationality" id="nationality" value="{{ old('nationality', $profile?->nationality) }}">
        </div>
        <div class="dash-form-field">
            <label for="phone">Phone</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', $profile?->phone) }}">
        </div>
        <div class="dash-form-field">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $profile?->email) }}">
        </div>
        <div class="dash-form-field">
            <label for="education_level">Education</label>
            <input type="text" name="education_level" id="education_level" value="{{ old('education_level', $profile?->education_level) }}">
        </div>
        <div class="dash-form-field dash-form-field--full">
            <label for="skills">Skills & certifications</label>
            <textarea name="skills" id="skills" rows="2">{{ old('skills', $profile?->skills) }}</textarea>
        </div>
    </div>
</div>

@if (! $employee)
    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Emergency contact (optional)</div>
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="emergency_contact_name">Contact name</label>
                <input type="text" name="emergency_contact_name" id="emergency_contact_name" value="{{ old('emergency_contact_name') }}">
            </div>
            <div class="dash-form-field">
                <label for="emergency_relationship">Relationship</label>
                <input type="text" name="emergency_relationship" id="emergency_relationship" value="{{ old('emergency_relationship') }}">
            </div>
            <div class="dash-form-field">
                <label for="emergency_phone">Phone</label>
                <input type="text" name="emergency_phone" id="emergency_phone" value="{{ old('emergency_phone') }}">
            </div>
            <div class="dash-form-field">
                <label for="emergency_email">Email</label>
                <input type="email" name="emergency_email" id="emergency_email" value="{{ old('emergency_email') }}">
            </div>
        </div>
    </div>

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Payroll (optional)</div>
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="contract_type">Contract type</label>
                <select name="contract_type" id="contract_type">
                    @foreach (config('modules.employee_contract_types') as $value => $label)
                        <option value="{{ $value }}" @selected(old('contract_type', 'permanent') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="base_salary">Base salary</label>
                <input type="number" step="0.01" min="0" name="base_salary" id="base_salary" value="{{ old('base_salary') }}">
            </div>
            <div class="dash-form-field">
                <label for="currency">Currency</label>
                <input type="text" name="currency" id="currency" value="{{ old('currency', 'RWF') }}" maxlength="3">
            </div>
            <div class="dash-form-field">
                <label for="pay_frequency">Pay frequency</label>
                <select name="pay_frequency" id="pay_frequency">
                    @foreach (config('modules.employee_pay_frequencies') as $value => $label)
                        <option value="{{ $value }}" @selected(old('pay_frequency', 'monthly') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="payment_method">Payment method</label>
                <select name="payment_method" id="payment_method">
                    <option value="">Not set</option>
                    @foreach (config('modules.expense_payment_methods') as $method)
                        <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ $method }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
@endif
