@php
    $vetVisit = $vetVisit ?? null;
    $selectedFarmId = old('farm_id', $vetVisit?->farm_id);
    $selectedLivestockId = old('livestock_id', $vetVisit?->livestock_id);
    $selectedAnimalId = old('animal_id', $vetVisit?->animal_id);
    $selectedFlockId = old('flock_id', $vetVisit?->flock_id);
    $selectedFarm = ($farms ?? collect())->firstWhere('id', (int) $selectedFarmId);
    $isPoultryFarm = $selectedFarm?->isPoultry() ?? false;
    $visitTarget = old('visit_target');

    if ($visitTarget === null) {
        $visitTarget = $selectedAnimalId ? 'animal' : 'group';
    }

    if (! in_array($visitTarget, ['group', 'animal'], true)) {
        $visitTarget = 'group';
    }
@endphp

<div class="vet-visit-form" data-visit-target-form>
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => __('Who is visited'),
        'description' => __('Choose the farm, then a whole livestock group or one animal. Poultry farms use a flock.'),
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="farm_id">{{ __('Farm') }} <span class="dash-required">*</span></label>
                <select name="farm_id" id="farm_id" required data-visit-farm>
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

            <div data-visit-cattle-target style="display: contents;" @if ($isPoultryFarm) hidden @endif>
                <div class="dash-form-field dash-form-field--full">
                    <label>{{ __('Visit') }}</label>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <label class="dash-checkbox">
                            <input type="radio" name="visit_target" value="group" @checked($visitTarget === 'group') data-visit-target>
                            <span>{{ __('Entire livestock group') }}</span>
                        </label>
                        <label class="dash-checkbox">
                            <input type="radio" name="visit_target" value="animal" @checked($visitTarget === 'animal') data-visit-target>
                            <span>{{ __('One animal') }}</span>
                        </label>
                    </div>
                    <p class="dash-field-hint">{{ __('Choose a whole group or a single animal from that group—not both.') }}</p>
                </div>

                <div class="dash-form-field" data-visit-livestock-field @if ($isPoultryFarm) hidden @endif>
                    <label for="livestock_id">{{ __('Livestock group') }} <span class="dash-required">*</span></label>
                    <select name="livestock_id" id="livestock_id" data-visit-livestock>
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

                <div class="dash-form-field" data-visit-animal-field @if ($visitTarget !== 'animal' || $isPoultryFarm) hidden @endif>
                    <label for="animal_id">{{ __('Animal') }} <span class="dash-required">*</span></label>
                    <select name="animal_id" id="animal_id" data-visit-animal>
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

            <div class="dash-form-field" data-visit-flock-field @if (! $isPoultryFarm) hidden @endif>
                <label for="flock_id">{{ __('Flock') }} <span class="dash-required">*</span></label>
                <select name="flock_id" id="flock_id" data-visit-flock>
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
                <input type="text" name="disease_name" id="disease_name" value="{{ old('disease_name', $vetVisit?->disease_name) }}" required placeholder="e.g. Lameness">
            </div>
            <div class="dash-form-field">
                <label for="medicine_name">Medicine name <span class="dash-required">*</span></label>
                <input type="text" name="medicine_name" id="medicine_name" value="{{ old('medicine_name', $vetVisit?->medicine_name) }}" required placeholder="e.g. Anti-inflammatory">
            </div>
            <div class="dash-form-field">
                <label for="dosage">Dosage</label>
                <input type="text" name="dosage" id="dosage" value="{{ old('dosage', $vetVisit?->dosage) }}" placeholder="e.g. 10 ml twice daily">
            </div>
            @include('modules.partials.select-with-other', [
                'name' => 'treatment_method',
                'otherName' => 'treatment_method_other',
                'id' => 'treatment_method',
                'label' => 'Treatment method',
                'options' => config('modules.treatment_methods'),
                'value' => $vetVisit?->treatment_method,
            ])
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '2',
        'title' => 'Schedule and provider',
        'description' => 'Visit dates, follow-up, and veterinarian.',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="start_date">Start date <span class="dash-required">*</span></label>
                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $vetVisit?->start_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="end_date">End date</label>
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $vetVisit?->end_date?->format('Y-m-d')) }}">
            </div>
            <div class="dash-form-field">
                <label for="follow_up_date">Follow-up date</label>
                <input type="date" name="follow_up_date" id="follow_up_date" value="{{ old('follow_up_date', $vetVisit?->follow_up_date?->format('Y-m-d')) }}">
            </div>
            <div class="dash-form-field">
                <label for="status">Status <span class="dash-required">*</span></label>
                <select name="status" id="status" required>
                    @foreach (config('modules.treatment_statuses') as $status)
                        <option value="{{ $status }}" @selected(old('status', $vetVisit?->status ?? 'Ongoing') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="veterinarian_name">Veterinarian name</label>
                <input type="text" name="veterinarian_name" id="veterinarian_name" value="{{ old('veterinarian_name', $vetVisit?->veterinarian_name) }}">
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
                <textarea name="symptoms" id="symptoms" rows="2" placeholder="Observed symptoms…">{{ old('symptoms', $vetVisit?->symptoms) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="diagnosis">Diagnosis</label>
                <textarea name="diagnosis" id="diagnosis" rows="2" placeholder="Clinical diagnosis…">{{ old('diagnosis', $vetVisit?->diagnosis) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="3">{{ old('notes', $vetVisit?->notes) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="attachment">Attachment</label>
                <div class="dash-photo-field">
                    @if ($vetVisit?->attachmentUrl())
                        <p class="dash-field-hint">
                            Current file:
                            <a href="{{ $vetVisit->attachmentUrl() }}" target="_blank" rel="noopener">View attachment</a>
                        </p>
                    @endif
                    <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <p class="dash-field-hint">PDF, image, or Word document up to 5 MB.</p>
                </div>
            </div>
        </div>
    @endcomponent

    @include('modules.expenses.partials.linked-expense-fields', [
        'expense' => $vetVisit?->expense,
        'defaultVendorName' => $vetVisit?->veterinarian_name,
        'vendors' => $vendors ?? [],
        'sectionNumber' => '4',
    ])
</div>

@once
    @push('scripts')
        <script>
            (() => {
                const form = document.querySelector('[data-visit-target-form]');
                if (!form) return;

                const farmSelect = form.querySelector('[data-visit-farm]');
                const cattleTarget = form.querySelector('[data-visit-cattle-target]');
                const livestockSelect = form.querySelector('[data-visit-livestock]');
                const animalSelect = form.querySelector('[data-visit-animal]');
                const flockSelect = form.querySelector('[data-visit-flock]');
                const livestockField = form.querySelector('[data-visit-livestock-field]');
                const animalField = form.querySelector('[data-visit-animal-field]');
                const flockField = form.querySelector('[data-visit-flock-field]');
                const targetRadios = form.querySelectorAll('[data-visit-target]');

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
                    form.querySelector('[data-visit-target]:checked')?.value || 'group';

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

                    const groupRadio = form.querySelector('[data-visit-target][value="group"]');
                    if (groupRadio && !form.querySelector('[data-visit-target]:checked')) {
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
