@php
    $expense = $expense ?? null;
    $defaultVendorName = $defaultVendorName ?? '';
    $vendors = $vendors ?? \App\Models\ExpenseVendor::query()->where('is_active', true)->orderBy('name')->get();
    $logExpense = old('log_expense', $expense ? true : false);
@endphp

@component('modules.farms._form-section', [
    'number' => $sectionNumber ?? 'E',
    'title' => 'Expense (optional)',
    'description' => 'Log the cost of this event in the Expenses module.',
])
    <div class="dash-form-grid">
        <div class="dash-form-field dash-form-field--full">
            <label class="dash-checkbox">
                <input type="checkbox" name="log_expense" value="1" @checked($logExpense) data-log-expense-toggle>
                <span>Log expense for this record</span>
            </label>
        </div>
        <div data-log-expense-fields @if(! $logExpense) hidden @endif>
            <div class="dash-form-grid">
                <div class="dash-form-field">
                    <label for="expense_amount">Amount (RWF) <span class="dash-required">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="expense_amount" id="expense_amount" value="{{ old('expense_amount', $expense?->amount) }}">
                </div>
                <div class="dash-form-field">
                    <label for="expense_payment_method">Payment method</label>
                    <select name="expense_payment_method" id="expense_payment_method">
                        <option value="">Not set</option>
                        @foreach (config('modules.expense_payment_methods') as $method)
                            <option value="{{ $method }}" @selected(old('expense_payment_method', $expense?->payment_method) === $method)>{{ $method }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="expense_vendor_id">Vendor</label>
                    <select name="expense_vendor_id" id="expense_vendor_id">
                        <option value="">Select or type below</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected(old('expense_vendor_id', $expense?->expense_vendor_id) == $vendor->id)>{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="expense_vendor_name">Or vendor name</label>
                    <input type="text" name="expense_vendor_name" id="expense_vendor_name" value="{{ old('expense_vendor_name', $expense?->vendor?->name ?? $defaultVendorName) }}" placeholder="New vendor name">
                </div>
                <div class="dash-form-field">
                    <label for="expense_paid_by">Paid by</label>
                    <input type="text" name="expense_paid_by" id="expense_paid_by" value="{{ old('expense_paid_by', $expense?->paid_by) }}">
                </div>
                <div class="dash-form-field dash-form-field--full">
                    <label for="expense_notes">Expense notes</label>
                    <input type="text" name="expense_notes" id="expense_notes" value="{{ old('expense_notes', $expense?->notes) }}">
                </div>
            </div>
        </div>
    </div>
@endcomponent

<script>
    document.querySelectorAll('[data-log-expense-toggle]').forEach((checkbox) => {
        const wrap = checkbox.closest('.dash-form-field--full')?.parentElement;
        const fields = wrap?.querySelector('[data-log-expense-fields]');
        const amount = wrap?.querySelector('#expense_amount');

        function sync() {
            if (!fields) return;
            fields.hidden = !checkbox.checked;
            if (amount) amount.required = checkbox.checked;
        }

        checkbox.addEventListener('change', sync);
        sync();
    });
</script>
