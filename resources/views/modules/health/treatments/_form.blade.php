@php
    $treatment = $treatment ?? null;
    $allowNewMedicine = $allowNewMedicine ?? ($treatment === null);
    $medicineMode = old('medicine_mode', 'existing');
    if (! $allowNewMedicine) {
        $medicineMode = 'existing';
    }
@endphp

<div class="treatment-form">
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => 'Animal and treatment',
        'description' => 'Select the farm and animal or flock, then enter disease and medication details.',
    ])
        <div class="dash-form-grid">
            @include('modules.partials.stock-picker', ['record' => $treatment, 'farms' => $farms ?? collect()])
            <div class="dash-form-field">
                <label for="disease_name">Disease name <span class="dash-required">*</span></label>
                <input type="text" name="disease_name" id="disease_name" value="{{ old('disease_name', $treatment?->disease_name) }}" required placeholder="e.g. Mastitis">
            </div>

            @if ($allowNewMedicine)
                <div class="dash-form-field dash-form-field--full">
                    <label>{{ __('Medicine source') }}</label>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <label class="dash-checkbox">
                            <input type="radio" name="medicine_mode" value="existing" @checked($medicineMode !== 'new') data-medicine-mode>
                            <span>{{ __('Use existing medicine') }}</span>
                        </label>
                        <label class="dash-checkbox">
                            <input type="radio" name="medicine_mode" value="new" @checked($medicineMode === 'new') data-medicine-mode>
                            <span>{{ __('Register new medicine') }}</span>
                        </label>
                    </div>
                </div>
            @else
                <input type="hidden" name="medicine_mode" value="existing">
            @endif

            <div class="dash-form-field" data-existing-medicine-field @if ($medicineMode === 'new') hidden @endif>
                <label for="medicine_id">{{ __('Medicine') }} <span class="dash-required">*</span></label>
                <select name="medicine_id" id="medicine_id" data-treatment-medicine @if ($medicineMode !== 'new') required @endif>
                    <option value="">{{ __('Select medicine') }}</option>
                    @forelse (($medicines ?? collect()) as $medicine)
                        @php
                            $isCurrent = (string) old('medicine_id', $treatment?->medicine_id) === (string) $medicine->id;
                            $isExpired = $medicine->isExpired(old('start_date', $treatment?->start_date));
                            $isOutOfStock = (float) $medicine->quantity_on_hand <= 0;
                            $isUnavailable = ($isExpired || $isOutOfStock) && ! $isCurrent;
                            $statusBits = collect([
                                $medicine->stockLabel(),
                                $medicine->expiry_date ? __('exp').' '.$medicine->expiry_date->format('Y-m-d') : null,
                                $isExpired ? __('Expired') : null,
                                $isOutOfStock ? __('Out of stock') : null,
                            ])->filter()->implode(' · ');
                        @endphp
                        <option
                            value="{{ $medicine->id }}"
                            data-unit="{{ $medicine->unit }}"
                            data-stock="{{ $medicine->quantity_on_hand }}"
                            data-instructions="{{ $medicine->dosage_instructions }}"
                            data-expired="{{ $isExpired ? '1' : '0' }}"
                            data-out-of-stock="{{ $isOutOfStock ? '1' : '0' }}"
                            @disabled($isUnavailable)
                            @selected($isCurrent)
                        >
                            {{ $medicine->name }} ({{ $statusBits }})
                        </option>
                    @empty
                        <option value="" disabled>{{ __('No medicines registered yet') }}</option>
                    @endforelse
                </select>
                <p class="dash-field-hint">
                    {{ __('Out-of-stock or expired medicines cannot be used. Add stock first in') }}
                    <a href="{{ route('health.medicines') }}">{{ __('Medicines') }}</a>.
                </p>
                @error('medicine_id')<p class="dash-form-error">{{ $message }}</p>@enderror
            </div>

            <div data-new-medicine-fields style="display: contents;" @if ($medicineMode !== 'new') hidden @endif>
                <div class="dash-form-field">
                    <label for="new_medicine_name">{{ __('New medicine name') }} <span class="dash-required">*</span></label>
                    <input type="text" name="new_medicine_name" id="new_medicine_name" value="{{ old('new_medicine_name') }}" placeholder="e.g. Oxytetracycline" @if ($medicineMode === 'new') required @endif>
                    @error('new_medicine_name')<p class="dash-form-error">{{ $message }}</p>@enderror
                </div>
                <div class="dash-form-field">
                    <label for="new_medicine_unit">{{ __('Unit') }} <span class="dash-required">*</span></label>
                    <select name="new_medicine_unit" id="new_medicine_unit" @if ($medicineMode === 'new') required @endif>
                        @foreach (config('modules.medicine_units') as $unit)
                            <option value="{{ $unit }}" @selected(old('new_medicine_unit', 'ml') === $unit)>{{ $unit }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="new_medicine_quantity">{{ __('Opening stock') }} <span class="dash-required">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="new_medicine_quantity" id="new_medicine_quantity" value="{{ old('new_medicine_quantity') }}" @if ($medicineMode === 'new') required @endif>
                    <p class="dash-field-hint">{{ __('Must cover the quantity used for this treatment. Remaining stock stays in Medicines.') }}</p>
                    @error('new_medicine_quantity')<p class="dash-form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="dash-form-field">
                <label for="quantity_used">{{ __('Quantity used') }} <span class="dash-required">*</span></label>
                <input type="number" step="0.01" min="0.01" name="quantity_used" id="quantity_used" value="{{ old('quantity_used', $treatment?->quantity_used) }}" required>
                <p class="dash-field-hint" data-medicine-stock-hint>
                    {{ __('Deducted from medicine inventory when saved.') }}
                </p>
                @error('quantity_used')<p class="dash-form-error">{{ $message }}</p>@enderror
            </div>
            <div class="dash-form-field">
                <label for="dosage">{{ __('Dosage') }}</label>
                <input type="text" name="dosage" id="dosage" value="{{ old('dosage', $treatment?->dosage) }}" placeholder="e.g. 10 ml twice daily" data-treatment-dosage>
            </div>
            @include('modules.partials.select-with-other', [
                'name' => 'treatment_method',
                'otherName' => 'treatment_method_other',
                'id' => 'treatment_method',
                'label' => 'Treatment method',
                'options' => config('modules.treatment_methods'),
                'value' => $treatment?->treatment_method,
            ])
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '2',
        'title' => 'Schedule and provider',
        'description' => 'Treatment dates, follow-up, and veterinarian.',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="start_date">Start date <span class="dash-required">*</span></label>
                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $treatment?->start_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="end_date">End date</label>
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $treatment?->end_date?->format('Y-m-d')) }}">
            </div>
            <div class="dash-form-field">
                <label for="follow_up_date">Follow-up date</label>
                <input type="date" name="follow_up_date" id="follow_up_date" value="{{ old('follow_up_date', $treatment?->follow_up_date?->format('Y-m-d')) }}">
            </div>
            <div class="dash-form-field">
                <label for="status">Status <span class="dash-required">*</span></label>
                <select name="status" id="status" required>
                    @foreach (config('modules.treatment_statuses') as $status)
                        <option value="{{ $status }}" @selected(old('status', $treatment?->status ?? 'Ongoing') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="veterinarian_name">Veterinarian name</label>
                <input type="text" name="veterinarian_name" id="veterinarian_name" value="{{ old('veterinarian_name', $treatment?->veterinarian_name) }}">
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '3',
        'title' => 'Clinical notes and files',
        'description' => 'Symptoms, diagnosis, notes, and supporting documents.',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field dash-form-field--full">
                <label for="symptoms">Symptoms</label>
                <textarea name="symptoms" id="symptoms" rows="2" placeholder="Observed symptoms…">{{ old('symptoms', $treatment?->symptoms) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="diagnosis">Diagnosis</label>
                <textarea name="diagnosis" id="diagnosis" rows="2" placeholder="Clinical diagnosis…">{{ old('diagnosis', $treatment?->diagnosis) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="3">{{ old('notes', $treatment?->notes) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="attachment">Attachment</label>
                <div class="dash-photo-field">
                    @if ($treatment?->attachmentUrl())
                        <p class="dash-field-hint">
                            Current file:
                            <a href="{{ $treatment->attachmentUrl() }}" target="_blank" rel="noopener">View attachment</a>
                        </p>
                    @endif
                    <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <p class="dash-field-hint">PDF, image, or Word document up to 5 MB.</p>
                </div>
            </div>
        </div>
    @endcomponent
</div>

@once
    @push('scripts')
        <script>
            (() => {
                const form = document.querySelector('.treatment-form');
                if (!form) return;

                const modeRadios = form.querySelectorAll('[data-medicine-mode]');
                const existingField = form.querySelector('[data-existing-medicine-field]');
                const newFields = form.querySelector('[data-new-medicine-fields]');
                const medicineSelect = form.querySelector('[data-treatment-medicine]');
                const newName = form.querySelector('#new_medicine_name');
                const newUnit = form.querySelector('#new_medicine_unit');
                const newQty = form.querySelector('#new_medicine_quantity');
                const dosage = form.querySelector('[data-treatment-dosage]');
                const hint = form.querySelector('[data-medicine-stock-hint]');
                const usedQty = form.querySelector('#quantity_used');

                const currentMode = () =>
                    form.querySelector('[data-medicine-mode]:checked')?.value || 'existing';

                const syncMode = () => {
                    const isNew = currentMode() === 'new';

                    if (existingField) existingField.hidden = isNew;
                    if (newFields) {
                        newFields.hidden = !isNew;
                        newFields.style.display = isNew ? 'contents' : 'none';
                    }

                    if (medicineSelect) {
                        medicineSelect.required = !isNew;
                        if (isNew) medicineSelect.value = '';
                    }

                    [newName, newUnit, newQty].forEach((input) => {
                        if (!input) return;
                        input.required = isNew;
                        if (!isNew) {
                            if (input.tagName === 'SELECT') return;
                            // keep typed values for when user toggles back
                        }
                    });

                    if (isNew && hint) {
                        hint.textContent = @json(__('Opening stock is registered, then this treatment quantity is deducted.'));
                    } else {
                        syncExistingStockHint();
                    }
                };

                const syncExistingStockHint = () => {
                    if (!medicineSelect || !hint || currentMode() === 'new') return;

                    const option = medicineSelect.selectedOptions?.[0];
                    if (!option?.value) {
                        hint.textContent = @json(__('Deducted from medicine inventory when saved.'));
                        return;
                    }

                    const stock = option.dataset.stock || '0';
                    const unit = option.dataset.unit || '';
                    hint.textContent = @json(__('Available')) + `: ${stock} ${unit}`.trim()
                        + ' — ' + @json(__('Deducted from medicine inventory when saved.'));

                    if (dosage && !dosage.value && option.dataset.instructions) {
                        dosage.placeholder = option.dataset.instructions;
                    }
                };

                // Prefill opening stock from quantity used when empty.
                usedQty?.addEventListener('change', () => {
                    if (currentMode() !== 'new' || !newQty || newQty.value) return;
                    newQty.value = usedQty.value;
                });

                modeRadios.forEach((radio) => radio.addEventListener('change', syncMode));
                medicineSelect?.addEventListener('change', syncExistingStockHint);
                syncMode();
            })();
        </script>
    @endpush
@endonce
