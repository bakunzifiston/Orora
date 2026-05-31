@extends('layouts.milk-module')

@section('title', 'Milk sale')

@section('milk-content')
    @include('modules.partials.header', [
        'title' => $milkSale->sale_code,
        'subtitle' => $milkSale->buyer_name.' · '.ucfirst($milkSale->status),
        'backRoute' => 'milk.sales',
    ])
    @include('modules.partials.flash')

    @if ($errors->has('sale') || $errors->has('payment'))
        <div class="dash-flash dash-flash--error" style="margin-bottom: 1rem;">
            {{ $errors->first('sale') ?: $errors->first('payment') }}
        </div>
    @endif

    @php
        $paidTotal = $milkSale->totalPaid();
        $balance = $milkSale->balanceDue();
    @endphp

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div class="dash-stat-label">Sale total</div>
            <div class="dash-stat-value">{{ number_format($milkSale->total_amount, 0) }} {{ $milkSale->currency }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Paid</div>
            <div class="dash-stat-value accent">{{ number_format($paidTotal, 0) }} {{ $milkSale->currency }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Balance</div>
            <div class="dash-stat-value">{{ number_format($balance, 0) }} {{ $milkSale->currency }}</div>
        </div>
    </div>

    @if ($milkSale->status === 'draft')
        <form method="POST" action="{{ route('milk.sales.update', $milkSale) }}" class="dash-farm-form" style="margin-bottom: 1.25rem;">
            @csrf
            @method('PUT')
            @include('modules.milk.sales._form', ['milkSale' => $milkSale])
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save</button>
            </div>
        </form>
    @endif

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Line items</div>
        @if ($milkSale->items->isEmpty())
            <p class="dash-empty">No items on this sale.</p>
        @else
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Tank</th>
                        <th>Liters</th>
                        <th>Unit price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($milkSale->items as $item)
                        <tr>
                            <td>{{ $item->storage?->container_name ?? '—' }}</td>
                            <td>{{ number_format($item->quantity_liters, 2) }}</td>
                            <td>{{ $item->unit_price ? number_format($item->unit_price, 0) : '—' }}</td>
                            <td>{{ $item->line_total ? number_format($item->line_total, 0) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if ($milkSale->status === 'draft')
        <div class="dash-panel" style="margin-bottom: 1.25rem;">
            <div class="dash-panel-title">Add line item</div>
            <form method="POST" action="{{ route('milk.sales.items.store', $milkSale) }}" class="dash-form-grid">
                @csrf
                <div class="dash-form-field">
                    <label for="item_storage">From tank</label>
                    <select name="milk_storage_id" id="item_storage">
                        <option value="">Not specified</option>
                        @foreach ($storageTanks as $tank)
                            <option value="{{ $tank->id }}">{{ $tank->container_name }} ({{ number_format($tank->current_quantity_liters, 1) }} L)</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="item_liters">Liters <span class="dash-required">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="quantity_liters" id="item_liters" required>
                </div>
                <div class="dash-form-field">
                    <label for="item_price">Price / liter</label>
                    <input type="number" step="0.01" min="0" name="unit_price" id="item_price">
                </div>
                <div class="dash-form-field" style="align-self: end;">
                    <button type="submit" class="dash-btn-save">Add item</button>
                </div>
            </form>
        </div>

        <form method="POST" action="{{ route('milk.sales.confirm', $milkSale) }}" style="margin-bottom: 1.25rem;">
            @csrf
            <button type="submit" class="dash-btn-save" onclick="return confirm('Confirm sale and deduct milk from storage?');">Confirm sale</button>
        </form>
    @endif

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Payments</div>
        @if ($milkSale->payments->isEmpty())
            <p class="dash-empty">No payments recorded yet.</p>
        @else
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($milkSale->payments as $payment)
                        <tr>
                            <td>{{ $payment->paid_on->format('M j, Y') }}</td>
                            <td>{{ $payment->payment_method ?? '—' }}</td>
                            <td><strong>{{ number_format($payment->amount, 0) }} {{ $milkSale->currency }}</strong></td>
                            <td>{{ $payment->notes ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if ($milkSale->status !== 'cancelled')
        <div class="dash-panel">
            <div class="dash-panel-title">Record payment</div>
            <form method="POST" action="{{ route('milk.sales.payments.store', $milkSale) }}" class="dash-form-grid">
                @csrf
                <div class="dash-form-field">
                    <label for="payment_amount">Amount <span class="dash-required">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="payment_amount" value="{{ old('amount', $balance > 0 ? $balance : '') }}" required>
                </div>
                <div class="dash-form-field">
                    <label for="payment_method">Method</label>
                    <select name="payment_method" id="payment_method">
                        <option value="">Not set</option>
                        @foreach (config('modules.expense_payment_methods') as $method)
                            <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ $method }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="paid_on">Paid on <span class="dash-required">*</span></label>
                    <input type="date" name="paid_on" id="paid_on" value="{{ old('paid_on', now()->format('Y-m-d')) }}" required>
                </div>
                <div class="dash-form-field dash-form-field--full">
                    <label for="payment_notes">Notes</label>
                    <input type="text" name="notes" id="payment_notes" value="{{ old('notes') }}">
                </div>
                <div class="dash-form-field" style="align-self: end;">
                    <button type="submit" class="dash-btn-save">Record payment</button>
                </div>
            </form>
        </div>
    @endif
@endsection
