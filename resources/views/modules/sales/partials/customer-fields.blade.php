@php
    $transaction = $transaction ?? null;
    $canEdit = ! $transaction || $transaction->sale_status === 'draft';
    $customerMode = old('customer_mode', match (true) {
        old('new_customer_display_name') && ! old('customer_id') => 'new',
        (bool) old('customer_id') => 'existing',
        (bool) ($transaction?->customer_id) => 'existing',
        $transaction !== null => 'none',
        default => 'existing',
    });
    $preselectedCustomerId = old('customer_id', $transaction?->customer_id ?? ($preselectedCustomerId ?? null));
@endphp

<div class="dash-form-field dash-form-field--full">
    <label>Customer</label>
    <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.75rem;">
        <label class="dash-checkbox">
            <input type="radio" name="customer_mode" value="existing" @checked($customerMode === 'existing') @disabled(! $canEdit)>
            <span>Existing customer</span>
        </label>
        <label class="dash-checkbox">
            <input type="radio" name="customer_mode" value="new" @checked($customerMode === 'new') @disabled(! $canEdit)>
            <span>New customer</span>
        </label>
        <label class="dash-checkbox">
            <input type="radio" name="customer_mode" value="none" @checked($customerMode === 'none') @disabled(! $canEdit)>
            <span>Walk-in / not assigned</span>
        </label>
    </div>

    <div id="sale-customer-existing" @if($customerMode !== 'existing') hidden @endif>
        <select name="customer_id" id="customer_id" @disabled(! $canEdit)>
            <option value="">Select customer</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected($preselectedCustomerId == $customer->id)>
                    {{ $customer->display_name }} ({{ $customer->customer_code }})
                </option>
            @endforeach
        </select>
        <p style="margin: 0.35rem 0 0; font-size: 0.8125rem; color: #808080;">
            <a href="{{ route('customers.directory') }}" target="_blank">Browse customer directory</a>
        </p>
    </div>

    <div id="sale-customer-new" class="dash-form-grid" style="margin-top: 0.5rem;" @if($customerMode !== 'new') hidden @endif>
        <div class="dash-form-field">
            <label for="new_customer_display_name">Name <span class="dash-required">*</span></label>
            <input type="text" name="new_customer_display_name" id="new_customer_display_name" value="{{ old('new_customer_display_name') }}" @disabled(! $canEdit) placeholder="Customer or business name">
        </div>
        <div class="dash-form-field">
            <label for="new_customer_type">Type</label>
            <select name="new_customer_type" id="new_customer_type" @disabled(! $canEdit)>
                @foreach (config('modules.customer_types') as $value => $label)
                    <option value="{{ $value }}" @selected(old('new_customer_type', 'individual') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="dash-form-field">
            <label for="new_customer_phone">Phone</label>
            <input type="text" name="new_customer_phone" id="new_customer_phone" value="{{ old('new_customer_phone') }}" @disabled(! $canEdit)>
        </div>
        <div class="dash-form-field">
            <label for="new_customer_email">Email</label>
            <input type="email" name="new_customer_email" id="new_customer_email" value="{{ old('new_customer_email') }}" @disabled(! $canEdit)>
        </div>
        <p style="margin: 0; font-size: 0.8125rem; color: #808080; grid-column: 1 / -1;">
            A customer record will be created when you save this sale.
        </p>
    </div>

    <p id="sale-customer-none" style="margin: 0; color: #808080; font-size: 0.875rem;" @if($customerMode !== 'none') hidden @endif>
        No customer linked — you can assign one later on this sale.
    </p>
</div>

<script>
    (function () {
        const modes = document.querySelectorAll('input[name="customer_mode"]');
        const existing = document.getElementById('sale-customer-existing');
        const fresh = document.getElementById('sale-customer-new');
        const none = document.getElementById('sale-customer-none');
        const select = document.getElementById('customer_id');
        const nameInput = document.getElementById('new_customer_display_name');

        function sync() {
            const mode = document.querySelector('input[name="customer_mode"]:checked')?.value || 'existing';
            existing.hidden = mode !== 'existing';
            fresh.hidden = mode !== 'new';
            none.hidden = mode !== 'none';

            if (select) select.disabled = mode !== 'existing';
            fresh.querySelectorAll('input, select').forEach(el => {
                el.disabled = mode !== 'new';
            });
            if (nameInput) nameInput.required = mode === 'new';
        }

        modes.forEach(r => r.addEventListener('change', sync));
        sync();
    })();
</script>
