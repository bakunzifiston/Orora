@extends('layouts.feeding-module')

@section('title', 'Edit inventory')

@section('feeding-content')
    @include('modules.partials.header', ['title' => 'Edit inventory', 'backRoute' => 'feeding.inventory'])
    @include('modules.partials.flash')

    @php
        $isLow = $feedInventory->isLowStock();
        $fmt = fn ($value) => rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
    @endphp
    <section class="farm-dash__section" aria-label="{{ __('Stock') }}" style="margin-bottom: 1rem;">
        <div class="farm-dash__kpis farm-dash__kpis--3">
            <div class="farm-kpi {{ $isLow ? 'farm-kpi--negative' : 'farm-kpi--stock' }}">
                <div class="farm-kpi__icon" aria-hidden="true">@include('layouts.partials.dashboard-nav-icon', ['icon' => 'box'])</div>
                <div class="farm-kpi__body">
                    <div class="farm-kpi__label">{{ __('Current stock') }} · {{ $feedInventory->feedType->name }}</div>
                    <div class="farm-kpi__value">{{ $fmt($feedInventory->quantity_on_hand) }} <span class="farm-kpi__unit">{{ $feedInventory->unit }}</span></div>
                </div>
            </div>
            <div class="farm-kpi farm-kpi--feeding">
                <div class="farm-kpi__icon" aria-hidden="true">@include('layouts.partials.dashboard-nav-icon', ['icon' => 'chart'])</div>
                <div class="farm-kpi__body">
                    <div class="farm-kpi__label">{{ __('Reorder level') }}</div>
                    <div class="farm-kpi__value">
                        @if ($feedInventory->reorder_level !== null)
                            {{ $fmt($feedInventory->reorder_level) }} <span class="farm-kpi__unit">{{ $feedInventory->unit }}</span>
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
            <a href="#record-stock" class="farm-kpi {{ $isLow ? 'farm-kpi--negative' : 'farm-kpi--production' }}">
                <div class="farm-kpi__icon" aria-hidden="true">@include('layouts.partials.dashboard-nav-icon', ['icon' => 'movement'])</div>
                <div class="farm-kpi__body">
                    <div class="farm-kpi__label">{{ __('Status') }}</div>
                    <div class="farm-kpi__value" style="font-size: 1.05rem;">{{ $isLow ? __('Low stock: add more ↓') : __('In stock') }}</div>
                </div>
            </a>
        </div>
    </section>

    <div class="dash-panel dash-profile-panel" style="margin-bottom: 1rem;">
        <div class="dash-panel-title">{{ __('Settings') }}</div>
        <form method="POST" action="{{ route('feeding.inventory.update', $feedInventory) }}" class="dash-profile-form">
            @csrf
            @method('PUT')
            @include('modules.feeding.inventory._form', ['feedInventory' => $feedInventory])
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save settings</button>
                <a href="{{ route('feeding.inventory') }}" class="dash-btn-cancel">Back</a>
            </div>
        </form>
    </div>

    <div class="dash-panel dash-profile-panel" style="margin-bottom: 1rem;" id="record-stock">
        <div class="dash-panel-title">{{ __('Add or remove stock') }}</div>
        <p class="dash-field-hint" style="margin: -0.25rem 0 0.75rem;">{{ __('Use "Purchase / stock in" when you buy feed, and tick "Log expense" to record the cost and vendor. Feeding records deduct stock automatically.') }}</p>
        <form method="POST" action="{{ route('feeding.inventory.movements.store', $feedInventory) }}" class="dash-profile-form">
            @csrf
            <div class="dash-form-grid">
                <div class="dash-form-field">
                    <label for="movement_type">Movement type</label>
                    <select name="movement_type" id="movement_type" required>
                        @foreach (['purchase', 'adjustment_in', 'adjustment_out'] as $type)
                            <option value="{{ $type }}" @selected(old('movement_type') === $type)>{{ $movementLabels[$type] ?? $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="quantity">{{ __('Quantity') }} ({{ $feedInventory->unit }})</label>
                    <input type="number" step="0.01" min="0.01" name="quantity" id="quantity" value="{{ old('quantity') }}" required>
                </div>
                <div class="dash-form-field">
                    <label for="moved_at">Date</label>
                    <input type="date" name="moved_at" id="moved_at" value="{{ old('moved_at', now()->format('Y-m-d')) }}">
                </div>
                <div class="dash-form-field dash-form-field--full">
                    <label for="movement_notes">Notes</label>
                    <input type="text" name="notes" id="movement_notes" value="{{ old('notes') }}">
                </div>
            </div>
            <div id="purchase-expense-fields" @if(old('movement_type', 'purchase') !== 'purchase') hidden @endif style="margin-top: 1rem;">
                @include('modules.expenses.partials.linked-expense-fields', [
                    'expense' => null,
                    'defaultVendorName' => $lastVendorName,
                    'vendors' => $vendors ?? [],
                    'sectionNumber' => '+',
                ])
            </div>
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Record movement</button>
            </div>
        </form>
        <script>
            document.getElementById('movement_type')?.addEventListener('change', function () {
                const isPurchase = this.value === 'purchase';
                const block = document.getElementById('purchase-expense-fields');
                if (block) block.hidden = !isPurchase;
            });
        </script>
    </div>

    <div class="dash-panel">
        <div class="dash-panel-title">Movement history</div>
        @if ($feedInventory->movements->isEmpty())
            <p class="dash-empty">No movements yet.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Qty</th>
                            <th>Balance</th>
                            <th>Vendor</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($feedInventory->movements as $movement)
                            <tr>
                                <td>{{ $movement->moved_at->format('M j, Y') }}</td>
                                <td>{{ $movementLabels[$movement->movement_type] ?? $movement->movement_type }}</td>
                                <td>{{ $movement->quantity }} {{ $movement->unit }}</td>
                                <td>{{ $movement->balance_after }} {{ $movement->unit }}</td>
                                <td>{{ $movement->expense?->vendor?->name ?? '—' }}</td>
                                <td>{{ $movement->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
