@php $expense = $expense ?? null; @endphp
<div class="dash-form-grid">
    <div class="dash-form-field">
        <label for="expense_category_id">Category <span class="dash-required">*</span></label>
        <select name="expense_category_id" id="expense_category_id" required>
            <option value="">Select category</option>
            @foreach ($categoriesByGroup as $group => $groupCategories)
                <optgroup label="{{ config('modules.expense_groups.'.$group.'.label', $group) }}">
                    @foreach ($groupCategories as $category)
                        <option value="{{ $category->id }}" @selected(old('expense_category_id', $expense?->expense_category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="farm_id">Farm</label>
        <select name="farm_id" id="farm_id">
            <option value="">None (general expense)</option>
            @foreach ($farms as $farm)
                <option value="{{ $farm->id }}" @selected(old('farm_id', $expense?->farm_id) == $farm->id)>{{ $farm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="expense_date">Date <span class="dash-required">*</span></label>
        <input type="date" name="expense_date" id="expense_date" value="{{ old('expense_date', $expense?->expense_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="amount">Amount <span class="dash-required">*</span></label>
        <input type="number" step="0.01" min="0.01" name="amount" id="amount" value="{{ old('amount', $expense?->amount) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="currency">Currency</label>
        <input type="text" name="currency" id="currency" value="{{ old('currency', $expense?->currency ?? 'RWF') }}" maxlength="3" required>
    </div>
    <div class="dash-form-field">
        <label for="expense_vendor_id">Vendor</label>
        <select name="expense_vendor_id" id="expense_vendor_id">
            <option value="">None</option>
            @foreach ($vendors as $vendor)
                <option value="{{ $vendor->id }}" @selected(old('expense_vendor_id', $expense?->expense_vendor_id) == $vendor->id)>{{ $vendor->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="payment_method">Payment method</label>
        <select name="payment_method" id="payment_method">
            <option value="">Not set</option>
            @foreach (config('modules.expense_payment_methods') as $method)
                <option value="{{ $method }}" @selected(old('payment_method', $expense?->payment_method) === $method)>{{ $method }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="paid_by">Paid by</label>
        <input type="text" name="paid_by" id="paid_by" value="{{ old('paid_by', $expense?->paid_by) }}">
    </div>
    <div class="dash-form-field">
        <label for="status">Status</label>
        <select name="status" id="status" required>
            @foreach (config('modules.expense_statuses') as $status)
                <option value="{{ $status }}" @selected(old('status', $expense?->status ?? 'paid') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="livestock_id">Livestock group</label>
        <select name="livestock_id" id="livestock_id">
            <option value="">None</option>
            @foreach ($livestockGroups as $group)
                <option value="{{ $group->id }}" @selected(old('livestock_id', $expense?->livestock_id) == $group->id)>{{ $group->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="animal_id">Animal</label>
        <select name="animal_id" id="animal_id">
            <option value="">None</option>
            @foreach ($animals as $animal)
                <option value="{{ $animal->id }}" @selected(old('animal_id', $expense?->animal_id) == $animal->id)>{{ $animal->tag_number }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="title">Title</label>
        <input type="text" name="title" id="title" value="{{ old('title', $expense?->title) }}" placeholder="Short description">
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="3">{{ old('notes', $expense?->notes) }}</textarea>
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="attachment">Receipt / attachment</label>
        <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
    </div>
</div>
