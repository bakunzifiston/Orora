@extends('layouts.employees-module')

@section('title', 'Employees — '.$employee->display_name)

@section('employee-content')
    @include('modules.partials.header', [
        'title' => $employee->display_name,
        'subtitle' => $employee->employee_code.' · '.$employee->roleLabel().' · '.$employee->statusLabel(),
        'backRoute' => 'employees.directory',
    ])
    @include('modules.partials.flash')

    @php
        $profile = $employee->profile;
        $payroll = $employee->payroll;
        $primaryEmergency = $employee->emergencyContacts->firstWhere('is_primary', true) ?? $employee->emergencyContacts->first();
    @endphp

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Employment type</div>
                <div class="dash-stat-value">{{ config('modules.employee_employment_types.'.$employee->employment_type, $employee->employment_type) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'employee'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Tenure</div>
                <div class="dash-stat-value accent">
                    @if ($employee->tenureMonths() !== null)
                        {{ $employee->tenureMonths() }} mo
                    @else
                        —
                    @endif
                </div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'chart'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Base salary</div>
                <div class="dash-stat-value">
                    @if ($payroll?->base_salary)
                        {{ number_format($payroll->base_salary, 0) }} {{ $payroll->currency }}
                    @else
                        —
                    @endif
                </div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'expense'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Farm assignments</div>
                <div class="dash-stat-value">{{ $employee->farmAssignments->count() }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'farm'])
        </div>
    </div>

    <div style="margin-bottom: 1rem;">
        <a href="{{ route('employees.edit', $employee) }}" class="dash-btn-save">Edit profile</a>
    </div>

    <div class="dash-health-grid" style="margin-bottom: 1.25rem;">
        <div class="dash-panel">
            <div class="dash-panel-title">Profile</div>
            @if ($profile)
                <dl class="dash-detail-list">
                    <div><dt>Full name</dt><dd>{{ trim(($profile->first_name ?? '').' '.($profile->last_name ?? '')) ?: '—' }}</dd></div>
                    <div><dt>National ID</dt><dd>{{ $profile->national_id ?? '—' }}</dd></div>
                    <div><dt>Phone</dt><dd>{{ $profile->phone ?? '—' }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $profile->email ?? '—' }}</dd></div>
                    <div><dt>Date of birth</dt><dd>{{ $profile->date_of_birth?->format('M j, Y') ?? '—' }}</dd></div>
                    <div><dt>Gender</dt><dd>{{ $profile->gender ? config('modules.customer_genders.'.$profile->gender, $profile->gender) : '—' }}</dd></div>
                    <div><dt>Marital status</dt><dd>{{ $profile->marital_status ? config('modules.employee_marital_statuses.'.$profile->marital_status, $profile->marital_status) : '—' }}</dd></div>
                    <div><dt>Nationality</dt><dd>{{ $profile->nationality ?? '—' }}</dd></div>
                    <div><dt>Education</dt><dd>{{ $profile->education_level ?? '—' }}</dd></div>
                    <div><dt>Primary farm</dt><dd>{{ $employee->primaryFarm?->name ?? '—' }}</dd></div>
                    <div><dt>Hire date</dt><dd>{{ $employee->hire_date?->format('M j, Y') ?? '—' }}</dd></div>
                </dl>
                @if ($profile->skills)
                    <p style="margin-top:0.75rem;color:#666;"><strong>Skills:</strong> {{ $profile->skills }}</p>
                @endif
            @else
                <p class="dash-empty">No profile details recorded.</p>
            @endif
            @if ($primaryEmergency)
                <p style="margin-top:0.75rem;color:#666;">Emergency: {{ $primaryEmergency->contact_name }} · {{ $primaryEmergency->phone ?? '—' }}</p>
            @endif
        </div>
        <div class="dash-panel">
            <div class="dash-panel-title">Payroll & contract</div>
            <form method="POST" action="{{ route('employees.payroll.update', $employee) }}" class="dash-form-grid">
                @csrf
                @method('PUT')
                <div class="dash-form-field">
                    <label for="contract_type">Contract type</label>
                    <select name="contract_type" id="contract_type" required>
                        @foreach (config('modules.employee_contract_types') as $value => $label)
                            <option value="{{ $value }}" @selected(old('contract_type', $payroll?->contract_type ?? 'permanent') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="contract_start">Contract start</label>
                    <input type="date" name="contract_start" id="contract_start" value="{{ old('contract_start', $payroll?->contract_start?->format('Y-m-d')) }}">
                </div>
                <div class="dash-form-field">
                    <label for="contract_end">Contract end</label>
                    <input type="date" name="contract_end" id="contract_end" value="{{ old('contract_end', $payroll?->contract_end?->format('Y-m-d')) }}">
                </div>
                <div class="dash-form-field">
                    <label for="base_salary">Base salary</label>
                    <input type="number" step="0.01" min="0" name="base_salary" id="base_salary" value="{{ old('base_salary', $payroll?->base_salary) }}">
                </div>
                <div class="dash-form-field">
                    <label for="currency">Currency</label>
                    <input type="text" name="currency" id="currency" value="{{ old('currency', $payroll?->currency ?? 'RWF') }}" maxlength="3" required>
                </div>
                <div class="dash-form-field">
                    <label for="pay_frequency">Pay frequency</label>
                    <select name="pay_frequency" id="pay_frequency" required>
                        @foreach (config('modules.employee_pay_frequencies') as $value => $label)
                            <option value="{{ $value }}" @selected(old('pay_frequency', $payroll?->pay_frequency ?? 'monthly') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="payment_method">Payment method</label>
                    <select name="payment_method" id="payment_method">
                        <option value="">Not set</option>
                        @foreach (config('modules.expense_payment_methods') as $method)
                            <option value="{{ $method }}" @selected(old('payment_method', $payroll?->payment_method) === $method)>{{ $method }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="bank_name">Bank name</label>
                    <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $payroll?->bank_name) }}">
                </div>
                <div class="dash-form-field">
                    <label for="bank_account">Bank account</label>
                    <input type="text" name="bank_account" id="bank_account" value="{{ old('bank_account', $payroll?->bank_account) }}">
                </div>
                <div class="dash-form-field">
                    <label for="mobile_money_number">Mobile money</label>
                    <input type="text" name="mobile_money_number" id="mobile_money_number" value="{{ old('mobile_money_number', $payroll?->mobile_money_number) }}">
                </div>
                <div class="dash-form-field dash-form-field--full">
                    <label for="payroll_notes">Notes</label>
                    <textarea name="notes" id="payroll_notes" rows="2">{{ old('notes', $payroll?->notes) }}</textarea>
                </div>
                <div class="dash-form-field" style="align-self:end;">
                    <button type="submit" class="dash-btn-save">Update payroll</button>
                </div>
            </form>
        </div>
    </div>

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Farm assignments</div>
        @if ($employee->farmAssignments->isEmpty())
            <p class="dash-empty">No farm assignments yet.</p>
        @else
            <table class="dash-table">
                <thead><tr><th>Farm</th><th>Role</th><th>From</th><th>Until</th><th>Primary</th><th></th></tr></thead>
                <tbody>
                    @foreach ($employee->farmAssignments as $assignment)
                        <tr>
                            <td>{{ $assignment->farm?->name ?? '—' }}</td>
                            <td>{{ $assignment->assignment_role ? config('modules.employee_job_roles.'.$assignment->assignment_role, $assignment->assignment_role) : '—' }}</td>
                            <td>{{ $assignment->assigned_from?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $assignment->assigned_until?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $assignment->is_primary ? 'Yes' : '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('employees.farm_assignments.destroy', [$employee, $assignment]) }}" onsubmit="return confirm('Remove assignment?');">
                                    @csrf @method('DELETE')
                                    <button type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        <form method="POST" action="{{ route('employees.farm_assignments.store', $employee) }}" class="dash-form-grid" style="margin-top:1rem;">
            @csrf
            <div class="dash-form-field">
                <label>Farm *</label>
                <select name="farm_id" required>
                    <option value="">Select farm</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label>Role on farm</label>
                <select name="assignment_role">
                    <option value="">Same as job role</option>
                    @foreach (config('modules.employee_job_roles') as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field"><label>From</label><input type="date" name="assigned_from"></div>
            <div class="dash-form-field"><label>Until</label><input type="date" name="assigned_until"></div>
            <div class="dash-form-field"><label class="dash-checkbox"><input type="checkbox" name="is_primary" value="1"><span>Primary</span></label></div>
            <div class="dash-form-field" style="align-self:end;"><button type="submit" class="dash-btn-save">Add assignment</button></div>
        </form>
    </div>

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Emergency contacts</div>
        @if ($employee->emergencyContacts->isEmpty())
            <p class="dash-empty">No emergency contacts yet.</p>
        @else
            <table class="dash-table">
                <thead><tr><th>Name</th><th>Relationship</th><th>Phone</th><th>Email</th><th>Primary</th><th></th></tr></thead>
                <tbody>
                    @foreach ($employee->emergencyContacts as $contact)
                        <tr>
                            <td>{{ $contact->contact_name }}</td>
                            <td>{{ $contact->relationship ?? '—' }}</td>
                            <td>{{ $contact->phone ?? '—' }}</td>
                            <td>{{ $contact->email ?? '—' }}</td>
                            <td>{{ $contact->is_primary ? 'Yes' : '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('employees.emergency_contacts.destroy', [$employee, $contact]) }}" onsubmit="return confirm('Remove contact?');">
                                    @csrf @method('DELETE')
                                    <button type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        <form method="POST" action="{{ route('employees.emergency_contacts.store', $employee) }}" class="dash-form-grid" style="margin-top:1rem;">
            @csrf
            <div class="dash-form-field"><label>Name *</label><input type="text" name="contact_name" required></div>
            <div class="dash-form-field"><label>Relationship</label><input type="text" name="relationship"></div>
            <div class="dash-form-field"><label>Phone</label><input type="text" name="phone"></div>
            <div class="dash-form-field"><label>Email</label><input type="email" name="email"></div>
            <div class="dash-form-field"><label class="dash-checkbox"><input type="checkbox" name="is_primary" value="1"><span>Primary</span></label></div>
            <div class="dash-form-field" style="align-self:end;"><button type="submit" class="dash-btn-save">Add contact</button></div>
        </form>
    </div>

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Addresses</div>
        @if ($employee->addresses->isEmpty())
            <p class="dash-empty">No addresses yet.</p>
        @else
            @foreach ($employee->addresses as $address)
                <div style="margin-bottom:0.75rem;padding-bottom:0.75rem;border-bottom:1px solid #eee;">
                    <strong>{{ config('modules.employee_address_types.'.$address->address_type, $address->address_type) }}</strong>
                    @if ($address->address_label) — {{ $address->address_label }} @endif
                    @if ($address->is_default) <span style="color:#808080;">(default)</span> @endif
                    <div>{{ $address->locationLabel() ?: '—' }}</div>
                    <div>{{ $address->street_address }}</div>
                    <form method="POST" action="{{ route('employees.addresses.destroy', [$employee, $address]) }}" style="margin-top:0.25rem;" onsubmit="return confirm('Remove address?');">
                        @csrf @method('DELETE')
                        <button type="submit">Remove</button>
                    </form>
                </div>
            @endforeach
        @endif
        <form method="POST" action="{{ route('employees.addresses.store', $employee) }}" class="dash-form-grid" style="margin-top:1rem;">
            @csrf
            <div class="dash-form-field">
                <label>Type *</label>
                <select name="address_type" required>
                    @foreach (config('modules.employee_address_types') as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field"><label>Label</label><input type="text" name="address_label"></div>
            <div class="dash-form-field"><label>Province</label><input type="text" name="province"></div>
            <div class="dash-form-field"><label>District</label><input type="text" name="district"></div>
            <div class="dash-form-field"><label>Sector</label><input type="text" name="sector"></div>
            <div class="dash-form-field"><label>Cell</label><input type="text" name="cell"></div>
            <div class="dash-form-field"><label>Village</label><input type="text" name="village"></div>
            <div class="dash-form-field dash-form-field--full"><label>Street</label><textarea name="street_address" rows="2"></textarea></div>
            <div class="dash-form-field"><label class="dash-checkbox"><input type="checkbox" name="is_default" value="1"><span>Default</span></label></div>
            <div class="dash-form-field" style="align-self:end;"><button type="submit" class="dash-btn-save">Add address</button></div>
        </form>
    </div>

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Documents</div>
        @if ($employee->documents->isEmpty())
            <p class="dash-empty">No documents recorded.</p>
        @else
            <table class="dash-table">
                <thead><tr><th>Type</th><th>Name</th><th>Issued</th><th>Expires</th><th></th></tr></thead>
                <tbody>
                    @foreach ($employee->documents as $document)
                        <tr>
                            <td>{{ config('modules.employee_document_types.'.$document->document_type, $document->document_type) }}</td>
                            <td>{{ $document->document_name }}</td>
                            <td>{{ $document->issued_date?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $document->expiry_date?->format('M j, Y') ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('employees.documents.destroy', [$employee, $document]) }}" onsubmit="return confirm('Remove document?');">
                                    @csrf @method('DELETE')
                                    <button type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        <form method="POST" action="{{ route('employees.documents.store', $employee) }}" class="dash-form-grid" style="margin-top:1rem;">
            @csrf
            <div class="dash-form-field">
                <label>Type *</label>
                <select name="document_type" required>
                    @foreach (config('modules.employee_document_types') as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field"><label>Name *</label><input type="text" name="document_name" required></div>
            <div class="dash-form-field"><label>Issued</label><input type="date" name="issued_date"></div>
            <div class="dash-form-field"><label>Expires</label><input type="date" name="expiry_date"></div>
            <div class="dash-form-field dash-form-field--full"><label>Notes</label><textarea name="notes" rows="2"></textarea></div>
            <div class="dash-form-field" style="align-self:end;"><button type="submit" class="dash-btn-save">Record document</button></div>
        </form>
    </div>

    @if ($employee->logs->isNotEmpty())
        <div class="dash-panel">
            <div class="dash-panel-title">Audit trail</div>
            <ul class="dash-health-activity">
                @foreach ($employee->logs->take(15) as $log)
                    <li>
                        <div>
                            <strong>{{ ucfirst(str_replace('_', ' ', $log->action_type)) }}</strong>
                            <span style="color:#808080;">{{ $log->action_at->format('M j, g:i A') }}</span>
                        </div>
                        <span>{{ $log->notes ?? ($log->actor?->name ?? '') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
