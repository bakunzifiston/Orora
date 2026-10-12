@extends('layouts.health-module')

@section('title', $medicine->name)

@section('health-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => $medicine->name,
            'subtitle' => __('Medicine details, stock, and treatment history'),
            'backRoute' => 'health.medicines',
            'createRoute' => 'health.medicines.edit',
            'createRouteParams' => $medicine,
            'createLabel' => __('Edit'),
        ])
        @include('modules.partials.flash')

        @php
            $isExpired = $medicine->isExpired();
            $isLow = $medicine->isLowStock();
            $movementLabels = $movementLabels ?? config('modules.medicine_movement_labels');
        @endphp

        <section class="farm-dash__section" aria-label="{{ __('Medicine summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Stock') }}</div>
                        <div class="farm-kpi__value">{{ $medicine->stockLabel() }}</div>
                    </div>
                </div>
                <div class="farm-kpi {{ $isExpired ? 'farm-kpi--expense' : 'farm-kpi--production' }}">
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Expiry') }}</div>
                        <div class="farm-kpi__value" style="font-size: 1.1rem;">{{ $medicine->expiry_date?->format('M j, Y') ?? '—' }}</div>
                    </div>
                </div>
                <div class="farm-kpi {{ $isLow ? 'farm-kpi--receivable' : 'farm-kpi--health' }}">
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Status') }}</div>
                        <div class="farm-kpi__value" style="font-size: 1.1rem;">
                            @if ($isExpired)
                                {{ __('Expired') }}
                            @elseif ($isLow)
                                {{ __('Low stock') }}
                            @elseif ($medicine->is_active)
                                {{ __('In stock') }}
                            @else
                                {{ __('Inactive') }}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--sales">
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Treatments') }}</div>
                        <div class="farm-kpi__value">{{ number_format($medicine->treatments_count) }}</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="dash-panel dash-profile-panel" style="margin-bottom: 1rem;">
            <div class="dash-form-grid">
                <div class="dash-form-field">
                    <label>{{ __('Category') }}</label>
                    <input type="text" value="{{ $medicine->category ?? '—' }}" readonly>
                </div>
                <div class="dash-form-field">
                    <label>{{ __('Active ingredient') }}</label>
                    <input type="text" value="{{ $medicine->active_ingredient ?? '—' }}" readonly>
                </div>
                <div class="dash-form-field">
                    <label>{{ __('Administration method') }}</label>
                    <input type="text" value="{{ $medicine->administration_method ?? '—' }}" readonly>
                </div>
                <div class="dash-form-field">
                    <label>{{ __('Supplier') }}</label>
                    <input type="text" value="{{ $medicine->supplier ?? '—' }}" readonly>
                </div>
                <div class="dash-form-field dash-form-field--full">
                    <label>{{ __('Dosage instructions') }}</label>
                    <textarea rows="2" readonly>{{ $medicine->dosage_instructions ?? '—' }}</textarea>
                </div>
            </div>
        </div>

        <div class="dash-panel dash-profile-panel" style="margin-bottom: 1rem;">
            <div class="dash-panel-title">{{ __('Add stock') }}</div>
            <p class="dash-field-hint" style="margin-bottom: 1rem;">{{ __('Record a purchase or stock adjustment. No expense logging required.') }}</p>
            <form method="POST" action="{{ route('health.medicines.movements.store', $medicine) }}" class="dash-profile-form">
                @csrf
                <div class="dash-form-grid">
                    <div class="dash-form-field">
                        <label for="movement_type">{{ __('Movement type') }} <span class="dash-required">*</span></label>
                        <select name="movement_type" id="movement_type" required>
                            @foreach (['purchase', 'adjustment_in', 'adjustment_out'] as $type)
                                <option value="{{ $type }}" @selected(old('movement_type', 'purchase') === $type)>
                                    {{ __($movementLabels[$type] ?? $type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="dash-form-field">
                        <label for="quantity">{{ __('Quantity') }} <span class="dash-required">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="quantity" id="quantity" value="{{ old('quantity') }}" required>
                        <p class="dash-field-hint">{{ __('Unit') }}: {{ $medicine->unit }}</p>
                        @error('quantity')<p class="dash-form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="dash-form-field">
                        <label for="moved_at">{{ __('Date') }}</label>
                        <input type="date" name="moved_at" id="moved_at" value="{{ old('moved_at', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="dash-form-field dash-form-field--full">
                        <label for="movement_notes">{{ __('Notes') }}</label>
                        <input type="text" name="notes" id="movement_notes" value="{{ old('notes') }}">
                    </div>
                </div>
                <div class="dash-form-actions">
                    <button type="submit" class="dash-btn-save">{{ __('Record movement') }}</button>
                </div>
            </form>
        </div>

        <div class="dash-panel health-page__table-panel" style="margin-bottom: 1rem;">
            <header class="farm-panel__head" style="padding: 1rem 1rem 0;">
                <h2 class="farm-panel__title">{{ __('Stock movements') }}</h2>
            </header>
            @if ($medicine->movements->isEmpty())
                <p class="dash-empty" style="padding: 1.5rem;">{{ __('No stock movements yet.') }}</p>
            @else
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Qty') }}</th>
                                <th>{{ __('Balance') }}</th>
                                <th>{{ __('Notes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($medicine->movements as $movement)
                                <tr>
                                    <td>
                                        <span class="health-table__value">{{ $movement->moved_at?->format('M j, Y') ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ __($movementLabels[$movement->movement_type] ?? $movement->movement_type) }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">
                                            {{ in_array($movement->movement_type, ['adjustment_out', 'consumption'], true) ? '−' : '+' }}
                                            {{ rtrim(rtrim(number_format((float) $movement->quantity, 2, '.', ''), '0'), '.') }}
                                            {{ $movement->unit }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">
                                            {{ rtrim(rtrim(number_format((float) $movement->balance_after, 2, '.', ''), '0'), '.') }}
                                            {{ $movement->unit }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $movement->notes ?: '—' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="dash-panel health-page__table-panel">
            <header class="farm-panel__head" style="padding: 1rem 1rem 0;">
                <h2 class="farm-panel__title">{{ __('Treatment history') }}</h2>
                <p class="farm-panel__desc">{{ __('Animals and flocks treated with this medicine.') }}</p>
            </header>

            @if ($treatments->isEmpty())
                <p class="dash-empty" style="padding: 1.5rem;">{{ __('No treatments have used this medicine yet.') }}</p>
            @else
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Animal / flock') }}</th>
                                <th>{{ __('Dosage') }}</th>
                                <th>{{ __('Qty used') }}</th>
                                <th>{{ __('Recorded by') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($treatments as $treatment)
                                <tr>
                                    <td>
                                        <span class="health-table__value">{{ $treatment->start_date?->format('M j, Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $treatment->stockLabel() }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $treatment->dosage ?: '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">
                                            {{ $treatment->quantity_used !== null ? rtrim(rtrim(number_format((float) $treatment->quantity_used, 2, '.', ''), '0'), '.').' '.$medicine->unit : '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $treatment->recordedBy?->name ?? '—' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="dash-pagination">{{ $treatments->links() }}</div>
            @endif
        </div>
    </div>
@endsection
