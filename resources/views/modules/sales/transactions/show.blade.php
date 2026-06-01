@extends('layouts.sales-module')

@section('title', 'Sales — '.$transaction->sale_number)

@section('sales-content')
    @include('modules.partials.header', [
        'title' => $transaction->sale_number,
        'subtitle' => $transaction->typeLabel(),
        'backRoute' => 'sales.transactions',
    ])
    <p class="dash-page-meta" style="margin: -0.5rem 0 1rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
        @include('modules.sales.partials.sale-status-badge', ['sale' => $transaction])
        @include('modules.sales.partials.payment-status-badge', ['sale' => $transaction])
    </p>
    @include('modules.partials.flash')

    @if ($errors->has('sale') || $errors->has('item') || $errors->has('payment'))
        <div class="dash-flash dash-flash--error" style="margin-bottom: 1rem;">
            {{ $errors->first('sale') ?: $errors->first('item') ?: $errors->first('payment') }}
        </div>
    @endif

    @php
        $paidTotal = $transaction->totalPaid();
        $balance = $transaction->balanceDue();
    @endphp

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Sale total</div>
                <div class="dash-stat-value">{{ number_format($transaction->total_amount, 0) }} {{ $transaction->currency }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'sale'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Paid</div>
                <div class="dash-stat-value accent">{{ number_format($paidTotal, 0) }} {{ $transaction->currency }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'chart'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Balance</div>
                <div class="dash-stat-value">{{ number_format($balance, 0) }} {{ $transaction->currency }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'expense'])
        </div>
    </div>

    @if ($transaction->sale_status === 'draft')
        <form method="POST" action="{{ route('sales.transactions.update', $transaction) }}" class="dash-farm-form" style="margin-bottom: 1.25rem;">
            @csrf
            @method('PUT')
            @include('modules.sales.transactions._form')
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save header</button>
            </div>
        </form>
    @else
        <div class="dash-panel" style="margin-bottom: 1.25rem;">
            <div class="dash-panel-title">Sale details</div>
            <dl class="dash-detail-list">
                <div><dt>Farm</dt><dd>{{ $transaction->farm?->name ?? '—' }}</dd></div>
                <div><dt>Customer</dt><dd>@if($transaction->customer)<a href="{{ route('customers.show', $transaction->customer) }}">{{ $transaction->customer->display_name }}</a>@else—@endif</dd></div>
                <div><dt>Date</dt><dd>{{ $transaction->sale_date->format('M j, Y') }}</dd></div>
                <div><dt>Payment</dt><dd>@include('modules.sales.partials.payment-status-badge', ['sale' => $transaction])</dd></div>
                <div><dt>Status</dt><dd>@include('modules.sales.partials.sale-status-badge', ['sale' => $transaction])</dd></div>
            </dl>
        </div>
    @endif

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Line items</div>
        @if ($transaction->items->isEmpty())
            <p class="dash-empty">No items on this sale.</p>
        @else
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Unit price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaction->items as $item)
                        <tr>
                            <td>
                                {{ $item->description }}
                                @if ($item->animal)
                                    <span style="color:#808080;">({{ $item->animal->tag_number }})</span>
                                @elseif ($item->milkStorage)
                                    <span style="color:#808080;">({{ $item->milkStorage->container_name }})</span>
                                @endif
                            </td>
                            <td>{{ number_format($item->quantity, 2) }} {{ $item->unit }}</td>
                            <td>{{ $item->unit_price ? number_format($item->unit_price, 0) : '—' }}</td>
                            <td>{{ number_format($item->total_price, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if ($transaction->sale_status === 'draft')
        <div class="dash-panel" style="margin-bottom: 1.25rem;">
            <div class="dash-panel-title">Add line item</div>
            <form method="POST" action="{{ route('sales.transactions.items.store', $transaction) }}" class="dash-form-grid">
                @csrf
                <input type="hidden" name="item_type" value="{{ match($transaction->sale_type) { 'animal_sale' => 'animal', 'meat_sale' => 'meat_cut', default => 'milk' } }}">

                @if ($transaction->sale_type === 'animal_sale')
                    @if ($animals->isEmpty())
                        <p class="dash-empty" style="margin-bottom: 1rem;">No active animals available for this farm (or all are already on a sale).</p>
                    @endif
                    <div class="dash-form-field">
                        <label for="animal_id">Animal <span class="dash-required">*</span></label>
                        <select name="animal_id" id="animal_id" required @disabled($animals->isEmpty())>
                            <option value="">Select animal</option>
                            @foreach ($animals as $animal)
                                <option
                                    value="{{ $animal->id }}"
                                    data-tag="{{ $animal->tag_number }}"
                                    data-name="{{ $animal->name }}"
                                    data-weight="{{ $animal->weight_kg }}"
                                >{{ $animal->tag_number }}@if($animal->name) — {{ $animal->name }}@endif ({{ $animal->weight_kg ?? '?' }} kg)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="dash-form-field">
                        <label for="description">Description <span class="dash-required">*</span></label>
                        <input type="text" name="description" id="description" placeholder="e.g. Heifer sale" required>
                    </div>
                    @if ($transaction->pricing_method === 'per_kg')
                        <div class="dash-form-field dash-form-field--full">
                            <p style="font-size: 0.8125rem; color: #808080; margin: 0;">Pricing is <strong>per kg</strong> — enter live weight and price per kg.</p>
                        </div>
                    @endif
                    <div class="dash-form-field">
                        <label for="live_weight_kg">Live weight (kg)</label>
                        <input type="number" step="0.01" min="0" name="live_weight_kg" id="live_weight_kg">
                    </div>
                    <div class="dash-form-field">
                        <label for="unit_price">Price</label>
                        <input type="number" step="0.01" min="0" name="unit_price" id="unit_price">
                    </div>
                    <div class="dash-form-field">
                        <label for="price_per_kg">Price / kg</label>
                        <input type="number" step="0.01" min="0" name="price_per_kg" id="price_per_kg">
                    </div>
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="unit" value="head">
                @elseif ($transaction->sale_type === 'milk_sale')
                    <div class="dash-form-field">
                        <label for="milk_storage_id">From tank</label>
                        <select name="milk_storage_id" id="milk_storage_id">
                            <option value="">Not specified</option>
                            @foreach ($storageTanks as $tank)
                                <option value="{{ $tank->id }}">{{ $tank->container_name }} ({{ number_format($tank->current_quantity_liters, 1) }} L)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="dash-form-field">
                        <label for="description">Description <span class="dash-required">*</span></label>
                        <input type="text" name="description" id="description" value="Milk sale" required>
                    </div>
                    <div class="dash-form-field">
                        <label for="quantity">Liters <span class="dash-required">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="quantity" id="quantity" required>
                    </div>
                    <div class="dash-form-field">
                        <label for="unit_price">Price / liter</label>
                        <input type="number" step="0.01" min="0" name="unit_price" id="unit_price">
                    </div>
                    <input type="hidden" name="unit" value="L">
                @else
                    <div class="dash-form-field dash-form-field--full">
                        <label for="description">Cut description <span class="dash-required">*</span></label>
                        <input type="text" name="description" id="description" required>
                    </div>
                    <div class="dash-form-field">
                        <label for="quantity">Weight (kg) <span class="dash-required">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="quantity" id="quantity" required>
                    </div>
                    <div class="dash-form-field">
                        <label for="unit_price">Price / kg</label>
                        <input type="number" step="0.01" min="0" name="unit_price" id="unit_price">
                    </div>
                    <input type="hidden" name="unit" value="kg">
                @endif

                <div class="dash-form-field" style="align-self: end;">
                    <button type="submit" class="dash-btn-save">Add item</button>
                </div>
            </form>
        </div>

        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.25rem;">
            @if ($transaction->sale_type === 'milk_sale')
                <form method="POST" action="{{ route('sales.transactions.confirm', $transaction) }}">
                    @csrf
                    <button type="submit" class="dash-btn-save" onclick="return confirm('Confirm sale and deduct milk from storage?');">Confirm &amp; deduct stock</button>
                </form>
            @else
                <form method="POST" action="{{ route('sales.transactions.complete', $transaction) }}">
                    @csrf
                    <button type="submit" class="dash-btn-save" onclick="return confirm('Complete this sale?');">Complete sale</button>
                </form>
            @endif
            <form method="POST" action="{{ route('sales.transactions.cancel', $transaction) }}">
                @csrf
                <button type="submit" class="dash-btn-cancel" onclick="return confirm('Cancel this sale?');">Cancel sale</button>
            </form>
            <form method="POST" action="{{ route('sales.transactions.destroy', $transaction) }}" onsubmit="return confirm('Delete this draft sale permanently?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="dash-btn-cancel" style="color: #b91c1c;">Delete draft</button>
            </form>
        </div>
    @endif

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Payments</div>
        @if ($transaction->payments->isEmpty())
            <p class="dash-empty">No payments recorded yet.</p>
        @else
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Reference</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaction->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date->format('M j, Y') }}</td>
                            <td>{{ $payment->payment_method ?? '—' }}</td>
                            <td><strong>{{ number_format($payment->amount_paid, 0) }} {{ $transaction->currency }}</strong></td>
                            <td>{{ $payment->transaction_reference ?? '—' }}</td>
                            <td>{{ $payment->notes ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if ($transaction->sale_status !== 'cancelled')
        <div class="dash-panel" style="margin-bottom: 1.25rem;">
            <div class="dash-panel-title">Record payment</div>
            <form method="POST" action="{{ route('sales.transactions.payments.store', $transaction) }}" class="dash-form-grid">
                @csrf
                <div class="dash-form-field">
                    <label for="amount_paid">Amount <span class="dash-required">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount_paid" id="amount_paid" value="{{ old('amount_paid', $balance > 0 ? $balance : '') }}" required>
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
                    <label for="payment_date">Paid on <span class="dash-required">*</span></label>
                    <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                </div>
                <div class="dash-form-field">
                    <label for="transaction_reference">Reference</label>
                    <input type="text" name="transaction_reference" id="transaction_reference" value="{{ old('transaction_reference') }}">
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

    @if ($transaction->logs->isNotEmpty())
        <div class="dash-panel">
            <div class="dash-panel-title">Activity</div>
            <ul class="dash-health-activity">
                @foreach ($transaction->logs as $log)
                    <li>
                        <div>
                            <strong>{{ ucfirst(str_replace('_', ' ', $log->action_type)) }}</strong>
                            <span style="color: #808080;">{{ $log->action_at->format('M j, g:i A') }}</span>
                        </div>
                        <span>{{ $log->notes ?? ($log->actor?->name ?? '') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection

@if ($transaction->sale_status === 'draft' && $transaction->sale_type === 'animal_sale')
    @push('scripts')
        <script>
            (function () {
                const select = document.getElementById('animal_id');
                const desc = document.getElementById('description');
                const weight = document.getElementById('live_weight_kg');
                if (!select || !desc) return;
                select.addEventListener('change', function () {
                    const opt = select.selectedOptions[0];
                    if (!opt?.value) return;
                    const tag = opt.dataset.tag || '';
                    const name = opt.dataset.name || '';
                    desc.value = name ? `Sale of ${tag} (${name})` : `Sale of ${tag}`;
                    if (weight && opt.dataset.weight && !weight.value) {
                        weight.value = opt.dataset.weight;
                    }
                });
            })();
        </script>
    @endpush
@endif
