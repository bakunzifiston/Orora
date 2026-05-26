@extends('layouts.feeding-module')

@section('title', 'Edit inventory')

@section('feeding-content')
    @include('modules.partials.header', ['title' => 'Edit inventory', 'backRoute' => 'feeding.inventory'])
    @include('modules.partials.flash')

    <div class="dash-panel dash-profile-panel" style="margin-bottom: 1rem;">
        <p><strong>On hand:</strong> {{ $feedInventory->quantity_on_hand }} {{ $feedInventory->unit }}</p>
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

    <div class="dash-panel dash-profile-panel" style="margin-bottom: 1rem;">
        <div class="dash-panel-title">Record stock movement</div>
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
                    <label for="quantity">Quantity</label>
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
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Record movement</button>
            </div>
        </form>
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
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($feedInventory->movements as $movement)
                            <tr>
                                <td>{{ $movement->moved_at->format('M j, Y') }}</td>
                                <td>{{ $movementLabels[$movement->movement_type] ?? $movement->movement_type }}</td>
                                <td>{{ $movement->quantity }} {{ $movement->unit }}</td>
                                <td>{{ $movement->balance_after }}</td>
                                <td>{{ $movement->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
