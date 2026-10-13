@php
    $vaccination = $vaccination ?? null;
    $selectedFarmId = old('farm_id', $vaccination?->farm_id);
    $selectedLivestockId = old('livestock_id', $vaccination?->livestock_id);
    $selectedAnimalId = old('animal_id', $vaccination?->animal_id);
    $selectedFlockId = old('flock_id', $vaccination?->flock_id);
    $selectedFarm = ($farms ?? collect())->firstWhere('id', (int) $selectedFarmId);
    $isPoultryFarm = $selectedFarm?->isPoultry() ?? false;
    $vaccinationTarget = old('vaccination_target');

    if ($vaccinationTarget === null) {
        $vaccinationTarget = $selectedAnimalId ? 'animal' : 'group';
    }

    if (! in_array($vaccinationTarget, ['group', 'animal'], true)) {
        $vaccinationTarget = 'group';
    }
@endphp

<div class="vaccination-form" data-vaccination-target-form>
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => __('Who is vaccinated'),
        'description' => __('Choose the farm, then a whole livestock group or one animal. Poultry farms use a flock.'),
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="farm_id">{{ __('Farm') }} <span class="dash-required">*</span></label>
                <select name="farm_id" id="farm_id" required data-vaccination-farm>
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

            <div data-vaccination-cattle-target style="display: contents;" @if ($isPoultryFarm) hidden @endif>
                <div class="dash-form-field dash-form-field--full">
                    <label>{{ __('Vaccinate') }}</label>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <label class="dash-checkbox">
                            <input type="radio" name="vaccination_target" value="group" @checked($vaccinationTarget === 'group') data-vaccination-target>
                            <span>{{ __('Entire livestock group') }}</span>
                        </label>
                        <label class="dash-checkbox">
                            <input type="radio" name="vaccination_target" value="animal" @checked($vaccinationTarget === 'animal') data-vaccination-target>
                            <span>{{ __('One animal') }}</span>
                        </label>
                    </div>
                    <p class="dash-field-hint">{{ __('Choose a whole group or a single animal from that group—not both.') }}</p>
                </div>

                <div class="dash-form-field" data-vaccination-livestock-field @if ($isPoultryFarm) hidden @endif>
                    <label for="livestock_id">{{ __('Livestock group') }} <span class="dash-required">*</span></label>
                    <select name="livestock_id" id="livestock_id" data-vaccination-livestock>
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

                <div class="dash-form-field" data-vaccination-animal-field @if ($vaccinationTarget !== 'animal' || $isPoultryFarm) hidden @endif>
                    <label for="animal_id">{{ __('Animal') }} <span class="dash-required">*</span></label>
                    <select name="animal_id" id="animal_id" data-vaccination-animal>
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

            <div class="dash-form-field" data-vaccination-flock-field @if (! $isPoultryFarm) hidden @endif>
                <label for="flock_id">{{ __('Flock') }} <span class="dash-required">*</span></label>
                <select name="flock_id" id="flock_id" data-vaccination-flock>
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
                <label for="vaccine_name">Vaccine name <span class="dash-required">*</span></label>
                <input type="text" name="vaccine_name" id="vaccine_name" value="{{ old('vaccine_name', $vaccination?->vaccine_name) }}" required placeholder="e.g. FMD vaccine">
            </div>
            @include('modules.partials.select-with-other', [
                'name' => 'vaccine_type',
                'otherName' => 'vaccine_type_other',
                'id' => 'vaccine_type',
                'label' => 'Vaccine type',
                'options' => config('modules.vaccine_types'),
                'value' => $vaccination?->vaccine_type,
            ])
            <div class="dash-form-field">
                <label for="manufacturer">Manufacturer</label>
                <input type="text" name="manufacturer" id="manufacturer" value="{{ old('manufacturer', $vaccination?->manufacturer) }}">
            </div>
            <div class="dash-form-field">
                <label for="batch_number">Batch number</label>
                <input type="text" name="batch_number" id="batch_number" value="{{ old('batch_number', $vaccination?->batch_number) }}">
            </div>
            <div class="dash-form-field">
                <label for="dosage">Dosage</label>
                <input type="text" name="dosage" id="dosage" value="{{ old('dosage', $vaccination?->dosage) }}" placeholder="e.g. 2 ml">
            </div>
            @include('modules.partials.select-with-other', [
                'name' => 'administration_method',
                'otherName' => 'administration_method_other',
                'id' => 'administration_method',
                'label' => 'Administration method',
                'options' => config('modules.administration_methods'),
                'value' => $vaccination?->administration_method,
            ])
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '2',
        'title' => 'Schedule and provider',
        'description' => 'When the vaccination was or will be given, and who administered it.',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="vaccination_date">Vaccination date <span class="dash-required">*</span></label>
                <input type="date" name="vaccination_date" id="vaccination_date" value="{{ old('vaccination_date', $vaccination?->vaccination_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="next_due_date">Next due date</label>
                <input type="date" name="next_due_date" id="next_due_date" value="{{ old('next_due_date', $vaccination?->next_due_date?->format('Y-m-d')) }}">
            </div>
            <div class="dash-form-field">
                <label for="status">Status <span class="dash-required">*</span></label>
                <select name="status" id="status" required>
                    @foreach (config('modules.vaccination_statuses') as $status)
                        <option value="{{ $status }}" @selected(old('status', $vaccination?->status ?? 'Scheduled') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="veterinarian_name">Veterinarian name</label>
                <input type="text" name="veterinarian_name" id="veterinarian_name" value="{{ old('veterinarian_name', $vaccination?->veterinarian_name) }}">
            </div>
            <div class="dash-form-field">
                <label for="veterinary_clinic">Veterinary clinic</label>
                <input type="text" name="veterinary_clinic" id="veterinary_clinic" value="{{ old('veterinary_clinic', $vaccination?->veterinary_clinic) }}">
            </div>
            <div class="dash-form-field">
                <label for="administered_by">Administered by</label>
                <input type="text" name="administered_by" id="administered_by" value="{{ old('administered_by', $vaccination?->administered_by) }}" placeholder="Staff member name">
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '3',
        'title' => 'Observations and files',
        'description' => 'Side effects, reactions, notes, and supporting documents.',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field dash-form-field--full">
                <label for="side_effects">Side effects</label>
                <textarea name="side_effects" id="side_effects" rows="2" placeholder="Any observed side effects…">{{ old('side_effects', $vaccination?->side_effects) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="reaction_notes">Reaction notes</label>
                <textarea name="reaction_notes" id="reaction_notes" rows="2" placeholder="Adverse reactions or follow-up observations…">{{ old('reaction_notes', $vaccination?->reaction_notes) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="3">{{ old('notes', $vaccination?->notes) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="attachment">Attachment</label>
                <div class="dash-photo-field">
                    @if ($vaccination?->attachmentUrl())
                        <p class="dash-field-hint">
                            Current file:
                            <a href="{{ $vaccination->attachmentUrl() }}" target="_blank" rel="noopener">View attachment</a>
                        </p>
                    @endif
                    <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <p class="dash-field-hint">PDF, image, or Word document up to 5 MB.</p>
                </div>
            </div>
        </div>
    @endcomponent

    @include('modules.expenses.partials.linked-expense-fields', [
        'expense' => $vaccination?->expense,
        'defaultVendorName' => $vaccination?->veterinary_clinic ?: $vaccination?->veterinarian_name,
        'vendors' => $vendors ?? [],
        'sectionNumber' => '4',
    ])
</div>

@once
    @push('scripts')
        <script>
            (() => {
                const form = document.querySelector('[data-vaccination-target-form]');
                if (!form) return;

                const farmSelect = form.querySelector('[data-vaccination-farm]');
                const cattleTarget = form.querySelector('[data-vaccination-cattle-target]');
                const livestockSelect = form.querySelector('[data-vaccination-livestock]');
                const animalSelect = form.querySelector('[data-vaccination-animal]');
                const flockSelect = form.querySelector('[data-vaccination-flock]');
                const livestockField = form.querySelector('[data-vaccination-livestock-field]');
                const animalField = form.querySelector('[data-vaccination-animal-field]');
                const flockField = form.querySelector('[data-vaccination-flock-field]');
                const targetRadios = form.querySelectorAll('[data-vaccination-target]');

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
                    form.querySelector('[data-vaccination-target]:checked')?.value || 'group';

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

                    const groupRadio = form.querySelector('[data-vaccination-target][value="group"]');
                    if (groupRadio && !form.querySelector('[data-vaccination-target]:checked')) {
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

                farmSelect?.addEventListener('change', syncSpecies);
                livestockSelect?.addEventListener('change', syncTarget);
                targetRadios.forEach((radio) => radio.addEventListener('change', syncTarget));

                syncSpecies();
            })();
        </script>
    @endpush
@endonce
