@php $sale = $sale ?? null; @endphp

<div class="dash-form-grid">
    <div class="dash-form-field">
        <label for="farm_id">Farm</label>
        <select name="farm_id" id="farm_id" required>
            <option value="">Select farm</option>
            @foreach ($farms as $farm)
                <option value="{{ $farm->id }}" @selected(old('farm_id', $sale?->farm_id) == $farm->id)>{{ $farm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="sold_on">Sold on</label>
        <input type="date" name="sold_on" id="sold_on" value="{{ old('sold_on', $sale?->sold_on?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="buyer_name">Buyer name</label>
        <input type="text" name="buyer_name" id="buyer_name" value="{{ old('buyer_name', $sale?->buyer_name) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="buyer_contact">Buyer contact</label>
        <input type="text" name="buyer_contact" id="buyer_contact" value="{{ old('buyer_contact', $sale?->buyer_contact) }}">
    </div>
    <div class="dash-form-field">
        <label for="animal_id">Animal (optional)</label>
        <select name="animal_id" id="animal_id">
            <option value="">None</option>
            @foreach ($animals as $animal)
                <option value="{{ $animal->id }}" @selected(old('animal_id', $sale?->animal_id) == $animal->id)>{{ $animal->tag_number }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="livestock_id">Livestock group (optional)</label>
        <select name="livestock_id" id="livestock_id">
            <option value="">None</option>
            @foreach ($livestockGroups as $group)
                <option value="{{ $group->id }}" @selected(old('livestock_id', $sale?->livestock_id) == $group->id)>{{ $group->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="quantity">Quantity</label>
        <input type="number" name="quantity" id="quantity" min="1" value="{{ old('quantity', $sale?->quantity ?? 1) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="unit_price">Unit price</label>
        <input type="number" step="0.01" name="unit_price" id="unit_price" min="0" value="{{ old('unit_price', $sale?->unit_price) }}">
    </div>
    <div class="dash-form-field">
        <label for="total_amount">Total amount</label>
        <input type="number" step="0.01" name="total_amount" id="total_amount" min="0" value="{{ old('total_amount', $sale?->total_amount) }}">
    </div>
    <div class="dash-form-field">
        <label for="currency">Currency</label>
        <input type="text" name="currency" id="currency" value="{{ old('currency', $sale?->currency ?? 'RWF') }}" required>
    </div>
    <div class="dash-form-field">
        <label for="payment_status">Payment status</label>
        <select name="payment_status" id="payment_status" required>
            @foreach (config('modules.payment_statuses') as $status)
                <option value="{{ $status }}" @selected(old('payment_status', $sale?->payment_status ?? 'pending') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="3">{{ old('notes', $sale?->notes) }}</textarea>
    </div>
</div>
