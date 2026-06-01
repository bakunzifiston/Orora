@extends('layouts.customers-module')

@section('title', 'Customers — '.$customer->display_name)

@section('customer-content')
    @include('modules.partials.header', [
        'title' => $customer->display_name,
        'subtitle' => $customer->customer_code.' · '.$customer->typeLabel().' · '.ucfirst($customer->status),
        'backRoute' => 'customers.directory',
    ])
    @include('modules.partials.flash')

    @php
        $credit = $customer->credit;
        $primary = $customer->primaryContact();
    @endphp

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Lifetime purchases</div>
                <div class="dash-stat-value">{{ number_format($purchaseStats['lifetime_value'], 0) }} {{ $customer->currency }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'sale'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Sales count</div>
                <div class="dash-stat-value accent">{{ number_format($purchaseStats['total_sales']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'chart'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Outstanding balance</div>
                <div class="dash-stat-value">{{ number_format($credit?->outstanding_balance ?? 0, 0) }} {{ $customer->currency }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'expense'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Credit limit</div>
                <div class="dash-stat-value">{{ number_format($credit?->credit_limit ?? 0, 0) }} {{ $customer->currency }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'customer'])
        </div>
    </div>

    <div style="margin-bottom: 1rem;">
        <a href="{{ route('customers.edit', $customer) }}" class="dash-btn-save">Edit profile</a>
        <a href="{{ route('sales.transactions.create', ['customer_id' => $customer->id]) }}" class="dash-btn-cancel" style="margin-left: 0.5rem;">New sale</a>
    </div>

    <div class="dash-health-grid" style="margin-bottom: 1.25rem;">
        <div class="dash-panel">
            <div class="dash-panel-title">Profile</div>
            @if ($customer->isIndividual() && $customer->profile)
                <dl class="dash-detail-list">
                    <div><dt>Name</dt><dd>{{ trim(($customer->profile->first_name ?? '').' '.($customer->profile->last_name ?? '')) ?: '—' }}</dd></div>
                    <div><dt>National ID</dt><dd>{{ $customer->profile->national_id ?? '—' }}</dd></div>
                    <div><dt>Trust</dt><dd>{{ config('modules.customer_trust_levels.'.$customer->trust_level, $customer->trust_level) }}</dd></div>
                    <div><dt>Payment</dt><dd>{{ $customer->preferred_payment_method ?? '—' }}</dd></div>
                </dl>
            @elseif ($customer->profile)
                <dl class="dash-detail-list">
                    <div><dt>Organization</dt><dd>{{ $customer->profile->organization_name ?? '—' }}</dd></div>
                    <div><dt>Registration</dt><dd>{{ $customer->profile->registration_number ?? '—' }}</dd></div>
                    <div><dt>License</dt><dd>{{ $customer->profile->license_number ?? '—' }}</dd></div>
                    <div><dt>Industry</dt><dd>{{ $customer->profile->industry ?? '—' }}</dd></div>
                </dl>
            @endif
            @if ($primary)
                <p style="margin-top:0.75rem;color:#666;">Primary contact: {{ $primary->contact_name }} · {{ $primary->phone ?? '—' }}</p>
            @endif
        </div>
        <div class="dash-panel">
            <div class="dash-panel-title">Purchases by type</div>
            <ul class="dash-health-activity">
                @foreach (config('modules.sale_type_labels') as $type => $typeLabel)
                    <li>
                        <div>{{ $typeLabel }}</div>
                        <span>{{ number_format($purchaseStats['by_type'][$type] ?? 0, 0) }} {{ $customer->currency }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Credit settings</div>
        <form method="POST" action="{{ route('customers.credit.update', $customer) }}" class="dash-form-grid">
            @csrf
            @method('PUT')
            <div class="dash-form-field">
                <label for="credit_limit">Credit limit</label>
                <input type="number" step="0.01" min="0" name="credit_limit" id="credit_limit" value="{{ old('credit_limit', $credit?->credit_limit ?? 0) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="payment_terms">Payment terms</label>
                <input type="text" name="payment_terms" id="payment_terms" value="{{ old('payment_terms', $credit?->payment_terms) }}" placeholder="e.g. Net 30">
            </div>
            <div class="dash-form-field">
                <label for="last_reviewed_at">Last reviewed</label>
                <input type="date" name="last_reviewed_at" id="last_reviewed_at" value="{{ old('last_reviewed_at', $credit?->last_reviewed_at?->format('Y-m-d')) }}">
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="credit_notes">Notes</label>
                <textarea name="notes" id="credit_notes" rows="2">{{ old('notes', $credit?->notes) }}</textarea>
            </div>
            <div class="dash-form-field" style="align-self:end;">
                <button type="submit" class="dash-btn-save">Update credit</button>
            </div>
        </form>
        @if ($credit?->isOverLimit())
            <p class="dash-flash dash-flash--error" style="margin-top:0.75rem;">This customer is over their credit limit.</p>
        @endif
    </div>

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Contacts</div>
        @if ($customer->contacts->isEmpty())
            <p class="dash-empty">No contacts yet.</p>
        @else
            <table class="dash-table">
                <thead><tr><th>Name</th><th>Role</th><th>Phone</th><th>Email</th><th>Primary</th><th></th></tr></thead>
                <tbody>
                    @foreach ($customer->contacts as $contact)
                        <tr>
                            <td>{{ $contact->contact_name }}</td>
                            <td>{{ $contact->role ?? '—' }}</td>
                            <td>{{ $contact->phone ?? '—' }}</td>
                            <td>{{ $contact->email ?? '—' }}</td>
                            <td>{{ $contact->is_primary ? 'Yes' : '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('customers.contacts.destroy', [$customer, $contact]) }}" onsubmit="return confirm('Remove contact?');">
                                    @csrf @method('DELETE')
                                    <button type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        <form method="POST" action="{{ route('customers.contacts.store', $customer) }}" class="dash-form-grid" style="margin-top:1rem;">
            @csrf
            <div class="dash-form-field"><label>Name *</label><input type="text" name="contact_name" required></div>
            <div class="dash-form-field"><label>Role</label><input type="text" name="role"></div>
            <div class="dash-form-field"><label>Phone</label><input type="text" name="phone"></div>
            <div class="dash-form-field"><label>Email</label><input type="email" name="email"></div>
            <div class="dash-form-field"><label class="dash-checkbox"><input type="checkbox" name="is_primary" value="1"><span>Primary</span></label></div>
            <div class="dash-form-field" style="align-self:end;"><button type="submit" class="dash-btn-save">Add contact</button></div>
        </form>
    </div>

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Addresses</div>
        @if ($customer->addresses->isEmpty())
            <p class="dash-empty">No addresses yet.</p>
        @else
            @foreach ($customer->addresses as $address)
                <div style="margin-bottom:0.75rem;padding-bottom:0.75rem;border-bottom:1px solid #eee;">
                    <strong>{{ config('modules.customer_address_types.'.$address->address_type, $address->address_type) }}</strong>
                    @if ($address->address_label) — {{ $address->address_label }} @endif
                    @if ($address->is_default) <span style="color:#808080;">(default)</span> @endif
                    <div>{{ $address->locationLabel() ?: '—' }}</div>
                    <div>{{ $address->street_address }}</div>
                    <form method="POST" action="{{ route('customers.addresses.destroy', [$customer, $address]) }}" style="margin-top:0.25rem;" onsubmit="return confirm('Remove address?');">
                        @csrf @method('DELETE')
                        <button type="submit">Remove</button>
                    </form>
                </div>
            @endforeach
        @endif
        <form method="POST" action="{{ route('customers.addresses.store', $customer) }}" class="dash-form-grid" style="margin-top:1rem;">
            @csrf
            <div class="dash-form-field">
                <label>Type *</label>
                <select name="address_type" required>
                    @foreach (config('modules.customer_address_types') as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field"><label>Label</label><input type="text" name="address_label"></div>
            <div class="dash-form-field"><label>District</label><input type="text" name="district"></div>
            <div class="dash-form-field"><label>Sector</label><input type="text" name="sector"></div>
            <div class="dash-form-field dash-form-field--full"><label>Street</label><textarea name="street_address" rows="2"></textarea></div>
            <div class="dash-form-field"><label class="dash-checkbox"><input type="checkbox" name="is_default" value="1"><span>Default</span></label></div>
            <div class="dash-form-field" style="align-self:end;"><button type="submit" class="dash-btn-save">Add address</button></div>
        </form>
    </div>

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Purchase history</div>
        @if ($customer->saleTransactions->isEmpty())
            <p class="dash-empty">No sales linked yet.</p>
        @else
            <table class="dash-table">
                <thead><tr><th>Sale #</th><th>Type</th><th>Date</th><th>Farm</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach ($customer->saleTransactions as $sale)
                        <tr>
                            <td><a href="{{ route('sales.transactions.show', $sale) }}">{{ $sale->sale_number }}</a></td>
                            <td>{{ $sale->typeLabel() }}</td>
                            <td>{{ $sale->sale_date->format('M j, Y') }}</td>
                            <td>{{ $sale->farm?->name ?? '—' }}</td>
                            <td>{{ number_format($sale->total_amount, 0) }} {{ $sale->currency }}</td>
                            <td>@include('modules.sales.partials.sale-status-badge', ['sale' => $sale])</td>
                            <td>
                                @include('modules.partials.row-actions', [
                                    'model' => $sale,
                                    'showRoute' => 'sales.transactions.show',
                                ])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Communications</div>
        @if ($customer->communications->isEmpty())
            <p class="dash-empty">No communications logged.</p>
        @else
            <ul class="dash-health-activity">
                @foreach ($customer->communications->take(10) as $comm)
                    <li>
                        <div>
                            <strong>{{ config('modules.customer_communication_types.'.$comm->communication_type, $comm->communication_type) }}</strong>
                            <span style="color:#808080;">{{ $comm->communication_date->format('M j, Y g:i A') }}</span>
                        </div>
                        <span>{{ Str::limit($comm->summary, 60) }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
        <form method="POST" action="{{ route('customers.communications.store', $customer) }}" class="dash-form-grid" style="margin-top:1rem;">
            @csrf
            <div class="dash-form-field">
                <label>Type *</label>
                <select name="communication_type" required>
                    @foreach (config('modules.customer_communication_types') as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label>Direction</label>
                <select name="direction">
                    <option value="">—</option>
                    @foreach (config('modules.customer_communication_directions') as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label>Date *</label>
                <input type="datetime-local" name="communication_date" value="{{ now()->format('Y-m-d\TH:i') }}" required>
            </div>
            <div class="dash-form-field"><label>Subject</label><input type="text" name="subject"></div>
            <div class="dash-form-field"><label>Contact person</label><input type="text" name="contact_person"></div>
            <div class="dash-form-field dash-form-field--full"><label>Summary *</label><textarea name="summary" rows="3" required></textarea></div>
            <div class="dash-form-field"><label class="dash-checkbox"><input type="checkbox" name="follow_up_required" value="1"><span>Follow-up required</span></label></div>
            <div class="dash-form-field"><label>Follow-up date</label><input type="date" name="follow_up_date"></div>
            <div class="dash-form-field" style="align-self:end;"><button type="submit" class="dash-btn-save">Log communication</button></div>
        </form>
    </div>

    @if ($customer->logs->isNotEmpty())
        <div class="dash-panel">
            <div class="dash-panel-title">Audit trail</div>
            <ul class="dash-health-activity">
                @foreach ($customer->logs->take(15) as $log)
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
