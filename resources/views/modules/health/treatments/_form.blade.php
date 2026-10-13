@php
    $treatment = $treatment ?? null;
    $allowNewMedicine = $allowNewMedicine ?? ($treatment === null);
    $medicineMode = old('medicine_mode', 'existing');
    if (! $allowNewMedicine) {
        $medicineMode = 'existing';
    }

    $selectedFarmId = old('farm_id', $treatment?->farm_id);
    $selectedLivestockId = old('livestock_id', $treatment?->livestock_id);
    $selectedAnimalId = old('animal_id', $treatment?->animal_id);
    $selectedFlockId = old('flock_id', $treatment?->flock_id);
    $selectedFarm = ($farms ?? collect())->firstWhere('id', (int) $selectedFarmId);
    $isPoultryFarm = $selectedFarm?->isPoultry() ?? false;
    $treatmentTarget = old('treatment_target');

    if ($treatmentTarget === null) {
        $treatmentTarget = $selectedAnimalId ? 'animal' : 'group';
    }

    if (! in_array($treatmentTarget, ['group', 'animal'], true)) {
        $treatmentTarget = 'group';
    }
@endphp

<div class="treatment-form" data-treatment-target-form>
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => __('Who is treated'),
        'description' => __('Choose the farm, then a whole livestock group or one animal. Poultry farms use a flock.'),
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="farm_id">{{ __('Farm') }} <span class="dash-required">*</span></label>
                <select name="farm_id" id="farm_id" required data-treatment-farm>
                    <option value="">{{ __('Select farm') }}</option>
                    @foreach (($farms ?? collect()) as $farm)
                        <option
                            value="{{ $farm->id }}"
                            data-species="{{ $farm->primary_species ?: 'cattle' }}"
                            @selected((string) $selectedFarmId === (string) $farm->id)
                        >{{ $farm->name }}</option>
                    @endforeach
                </select>
            </div>

            <div data-treatment-cattle-target style="display: contents;" @if ($isPoultryFarm) hidden @endif>
                <div class="dash-form-field dash-form-field--full">
                    <label>{{ __('Treat') }}</label>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <label class="dash-checkbox">
                            <input type="radio" name="treatment_target" value="group" @checked($treatmentTarget === 'group') data-treatment-target>
                            <span>{{ __('Entire livestock group') }}</span>
                        </label>
                        <label class="dash-checkbox">
                            <input type="radio" name="treatment_target" value="animal" @checked($treatmentTarget === 'animal') data-treatment-target>
                            <span>{{ __('One animal') }}</span>
                        </label>
                    </div>
                    <p class="dash-field-hint">{{ __('Choose a whole group or a single animal from that group—not both.') }}</p>
                </div>

                <div class="dash-form-field" data-treatment-livestock-field @if ($isPoultryFarm) hidden @endif>
                    <label for="livestock_id">{{ __('Livestock group') }} <span class="dash-required">*</span></label>
                    <select name="livestock_id" id="livestock_id" data-treatment-livestock>
                        <option value="">{{ __('Select livestock group') }}</option>
                        @foreach (($livestockGroups ?? collect()) as $group)
                            <option
                                value="{{ $group->id }}"
                                data-farm-id="{{ $group->farm_id }}"
                                @selected((string) $selectedLivestockId === (string) $group->id)
                            >
                                {{ $group->name }}@if ($group->farm) — {{ $group->farm->name }}@endif
                            </option>
                        @endforeach
                    </select>
                    @error('livestock_id')<p class="dash-form-error">{{ $message }}</p>@enderror
                </div>

                <div class="dash-form-field" data-treatment-animal-field @if ($treatmentTarget !== 'animal' || $isPoultryFarm) hidden @endif>
                    <label for="animal_id">{{ __('Animal') }} <span class="dash-required">*</span></label>
                    <select name="animal_id" id="animal_id" data-treatment-animal>
                        <option value="">{{ __('Select animal') }}</option>
                        @foreach (($animals ?? collect()) as $animal)
                            <option
                                value="{{ $animal->id }}"
                                data-farm-id="{{ $animal->farm_id }}"
                                data-livestock-id="{{ $animal->livestock_id }}"
                                @selected((string) $selectedAnimalId === (string) $animal->id)
                            >
                                {{ $animal->tag_number }}@if ($animal->name) — {{ $animal->name }}@endif
                            </option>
                        @endforeach
                    </select>
                    @error('animal_id')<p class="dash-form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="dash-form-field" data-treatment-flock-field @if (! $isPoultryFarm) hidden @endif>
                <label for="flock_id">{{ __('Flock') }} <span class="dash-required">*</span></label>
                <select name="flock_id" id="flock_id" data-treatment-flock>
                    <option value="">{{ __('Select flock') }}</option>
                    @foreach (($flocks ?? collect()) as $flock)
                        <option
                            value="{{ $flock->id }}"
                            data-farm-id="{{ $flock->farm_id }}"
                            @selected((string) $selectedFlockId === (string) $flock->id)
                        >{{ $flock->label() }}@if ($flock->farm) — {{ $flock->farm->name }}@endif</option>
                    @endforeach
                </select>
                @error('flock_id')<p class="dash-form-error">{{ $message }}</p>@enderror
            </div>

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
                const form = document.querySelector('[data-treatment-target-form]') || document.querySelector('.treatment-form');
                if (!form) return;

                const farmSelect = form.querySelector('[data-treatment-farm]');
                const cattleTarget = form.querySelector('[data-treatment-cattle-target]');
                const livestockSelect = form.querySelector('[data-treatment-livestock]');
                const animalSelect = form.querySelector('[data-treatment-animal]');
                const flockSelect = form.querySelector('[data-treatment-flock]');
                const livestockField = form.querySelector('[data-treatment-livestock-field]');
                const animalField = form.querySelector('[data-treatment-animal-field]');
                const flockField = form.querySelector('[data-treatment-flock-field]');
                const targetRadios = form.querySelectorAll('[data-treatment-target]');

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

                const filterOptions = (select, predicate) => {
                    if (!select) return;
                    let keep = false;
                    Array.from(select.options).forEach((option) => {
                        if (!option.value) {
                            option.hidden = false;
                            return;
                        }
                        const matches = predicate(option);
                        option.hidden = !matches;
                        if (option.selected && matches) keep = true;
                    });
                    if (!keep) select.value = '';
                };

                const species = () => farmSelect?.selectedOptions?.[0]?.dataset?.species || 'cattle';
                const isPoultry = () => species() === 'poultry';
                const currentTarget = () =>
                    form.querySelector('[data-treatment-target]:checked')?.value || 'group';

                const syncSpecies = () => {
                    const poultry = isPoultry();
                    const farmId = farmSelect?.value || '';

                    if (cattleTarget) {
                        cattleTarget.hidden = poultry;
                        cattleTarget.style.display = poultry ? 'none' : 'contents';
                    }
                    if (flockField) flockField.hidden = !poultry;

                    filterOptions(livestockSelect, (option) => !farmId || option.dataset.farmId === farmId);
                    filterOptions(animalSelect, (option) => {
                        const farmOk = !farmId || option.dataset.farmId === farmId;
                        const livestockId = livestockSelect?.value || '';
                        const livestockOk = !livestockId || option.dataset.livestockId === livestockId;
                        return farmOk && livestockOk;
                    });
                    filterOptions(flockSelect, (option) => !farmId || option.dataset.farmId === farmId);

                    if (poultry) {
                        if (livestockSelect) {
                            livestockSelect.value = '';
                            livestockSelect.required = false;
                        }
                        if (animalSelect) {
                            animalSelect.value = '';
                            animalSelect.required = false;
                        }
                        if (livestockField) livestockField.hidden = true;
                        if (animalField) animalField.hidden = true;
                        if (flockSelect) flockSelect.required = !!farmId;
                        return;
                    }

                    if (flockSelect) {
                        flockSelect.value = '';
                        flockSelect.required = false;
                    }

                    const groupRadio = form.querySelector('[data-treatment-target][value="group"]');
                    if (groupRadio && !form.querySelector('[data-treatment-target]:checked')) {
                        groupRadio.checked = true;
                    }

                    syncTarget();
                };

                const syncTarget = () => {
                    if (isPoultry()) return;

                    const target = currentTarget();
                    const showAnimal = target === 'animal';

                    if (livestockField) livestockField.hidden = false;
                    if (animalField) animalField.hidden = !showAnimal;
                    if (livestockSelect) livestockSelect.required = true;
                    if (animalSelect) {
                        animalSelect.required = showAnimal;
                        if (!showAnimal) animalSelect.value = '';
                    }

                    filterOptions(animalSelect, (option) => {
                        const farmId = farmSelect?.value || '';
                        const livestockId = livestockSelect?.value || '';
                        const farmOk = !farmId || option.dataset.farmId === farmId;
                        const livestockOk = !livestockId || option.dataset.livestockId === livestockId;
                        return farmOk && livestockOk;
                    });
                };

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
                        if (input) input.required = isNew;
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

                usedQty?.addEventListener('change', () => {
                    if (currentMode() !== 'new' || !newQty || newQty.value) return;
                    newQty.value = usedQty.value;
                });

                farmSelect?.addEventListener('change', syncSpecies);
                livestockSelect?.addEventListener('change', syncTarget);
                targetRadios.forEach((radio) => radio.addEventListener('change', syncTarget));
                modeRadios.forEach((radio) => radio.addEventListener('change', syncMode));
                medicineSelect?.addEventListener('change', syncExistingStockHint);

                syncSpecies();
                syncMode();
            })();
        </script>
    @endpush
@endonce
