@php
    $diseaseRecord = $diseaseRecord ?? null;
    $selectedFarmId = old('farm_id', $selectedFarmId ?? $diseaseRecord?->farm_id);
    $selectedLivestockId = old('livestock_id', $selectedLivestockId ?? $diseaseRecord?->livestock_id);
    $selectedAnimalId = old('animal_id', $selectedAnimalId ?? $diseaseRecord?->animal_id);
    $selectedFlockId = old('flock_id', $selectedFlockId ?? $diseaseRecord?->flock_id);
    $selectedFarm = ($farms ?? collect())->firstWhere('id', (int) $selectedFarmId);
    $isPoultryFarm = $selectedFarm?->isPoultry() ?? false;
    $diseaseTarget = old('disease_target');

    if ($diseaseTarget === null) {
        $diseaseTarget = $selectedAnimalId ? 'animal' : 'group';
    }

    if (! in_array($diseaseTarget, ['group', 'animal'], true)) {
        $diseaseTarget = 'group';
    }
@endphp

<div class="disease-form" data-disease-target-form>
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => __('Who is affected'),
        'description' => __('Choose the farm, then a whole livestock group or one animal. Poultry farms use a flock.'),
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="farm_id">{{ __('Farm') }} <span class="dash-required">*</span></label>
                <select name="farm_id" id="farm_id" required data-disease-farm>
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

            <div data-disease-cattle-target style="display: contents;" @if ($isPoultryFarm) hidden @endif>
                <div class="dash-form-field dash-form-field--full">
                    <label>{{ __('Affected') }}</label>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <label class="dash-checkbox">
                            <input type="radio" name="disease_target" value="group" @checked($diseaseTarget === 'group') data-disease-target>
                            <span>{{ __('Entire livestock group') }}</span>
                        </label>
                        <label class="dash-checkbox">
                            <input type="radio" name="disease_target" value="animal" @checked($diseaseTarget === 'animal') data-disease-target>
                            <span>{{ __('One animal') }}</span>
                        </label>
                    </div>
                    <p class="dash-field-hint">{{ __('Choose a whole group or a single animal from that group—not both.') }}</p>
                </div>

                <div class="dash-form-field" data-disease-livestock-field @if ($isPoultryFarm) hidden @endif>
                    <label for="livestock_id">{{ __('Livestock group') }} <span class="dash-required">*</span></label>
                    <select name="livestock_id" id="livestock_id" data-disease-livestock>
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

                <div class="dash-form-field" data-disease-animal-field @if ($diseaseTarget !== 'animal' || $isPoultryFarm) hidden @endif>
                    <label for="animal_id">{{ __('Animal') }} <span class="dash-required">*</span></label>
                    <select name="animal_id" id="animal_id" data-disease-animal>
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

            <div class="dash-form-field" data-disease-flock-field @if (! $isPoultryFarm) hidden @endif>
                <label for="flock_id">{{ __('Flock') }} <span class="dash-required">*</span></label>
                <select name="flock_id" id="flock_id" data-disease-flock>
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
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '2',
        'title' => 'Disease details',
        'description' => 'Diagnosis information and clinical classification.',
    ])
        <div class="dash-form-grid">
            @if ($diseaseRecord?->disease_code)
                <div class="dash-form-field">
                    <label>Record code</label>
                    <input type="text" value="{{ $diseaseRecord->disease_code }}" readonly>
                </div>
            @endif
            <div class="dash-form-field @if (! $diseaseRecord?->disease_code) dash-form-field--full @endif">
                <label for="disease_name">Disease name <span class="dash-required">*</span></label>
                <input type="text" name="disease_name" id="disease_name" value="{{ old('disease_name', $diseaseRecord?->disease_name) }}" required placeholder="e.g. East Coast Fever">
            </div>
            <div class="dash-form-field">
                <label for="diagnosis_date">Diagnosis date <span class="dash-required">*</span></label>
                <input type="date" name="diagnosis_date" id="diagnosis_date" max="{{ now()->format('Y-m-d') }}" value="{{ old('diagnosis_date', $diseaseRecord?->diagnosis_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label>Severity <span class="dash-required">*</span></label>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    @foreach (config('modules.disease_severity_levels') as $level)
                        <label class="dash-checkbox">
                            <input type="radio" name="severity_level" value="{{ $level }}" @checked(old('severity_level', $diseaseRecord?->severity_level ?? 'medium') === $level) required>
                            <span>{{ config('modules.disease_severity_labels')[$level] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label>Recovery status <span class="dash-required">*</span></label>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    @foreach (config('modules.disease_recovery_statuses') as $status)
                        <label class="dash-checkbox">
                            <input type="radio" name="recovery_status" value="{{ $status }}" @checked(old('recovery_status', $diseaseRecord?->recovery_status ?? 'recovering') === $status) required>
                            <span>{{ config('modules.disease_recovery_labels')[$status] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label>Contagious status <span class="dash-required">*</span></label>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    @foreach (config('modules.disease_contagious_statuses') as $status)
                        <label class="dash-checkbox">
                            <input type="radio" name="contagious_status" value="{{ $status }}" @checked(old('contagious_status', $diseaseRecord?->contagious_status ?? 'unknown') === $status) required>
                            <span>{{ config('modules.disease_contagious_labels')[$status] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label class="dash-checkbox">
                    <input type="checkbox" name="quarantine_required" value="1" @checked(old('quarantine_required', $diseaseRecord?->quarantine_required))>
                    <span>{{ __('Yes, quarantine is required') }}</span>
                </label>
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '3',
        'title' => 'Clinical notes and files',
        'description' => 'Symptoms, veterinarian, notes, and supporting documents.',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field dash-form-field--full">
                <label for="symptoms">Symptoms</label>
                <textarea name="symptoms" id="symptoms" rows="3" placeholder="Observed symptoms…">{{ old('symptoms', $diseaseRecord?->symptoms) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="veterinarian_name">Veterinarian name</label>
                <input type="text" name="veterinarian_name" id="veterinarian_name" value="{{ old('veterinarian_name', $diseaseRecord?->veterinarian_name) }}">
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="3">{{ old('notes', $diseaseRecord?->notes) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="attachment">Attachment</label>
                <div class="dash-photo-field">
                    @if ($diseaseRecord?->attachmentUrl())
                        <p class="dash-field-hint">
                            Current file:
                            <a href="{{ $diseaseRecord->attachmentUrl() }}" target="_blank" rel="noopener">View attachment</a>
                        </p>
                    @endif
                    <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png">
                    <p class="dash-field-hint">PDF, JPG, or PNG up to 4 MB.</p>
                </div>
            </div>
        </div>
    @endcomponent

    @include('modules.expenses.partials.linked-expense-fields', [
        'expense' => $diseaseRecord?->expense,
        'defaultVendorName' => $diseaseRecord?->veterinarian_name,
        'vendors' => $vendors ?? [],
        'sectionNumber' => '4',
    ])
</div>

@once
    @push('scripts')
        <script>
            (() => {
                const form = document.querySelector('[data-disease-target-form]');
                if (!form) return;

                const farmSelect = form.querySelector('[data-disease-farm]');
                const cattleTarget = form.querySelector('[data-disease-cattle-target]');
                const livestockSelect = form.querySelector('[data-disease-livestock]');
                const animalSelect = form.querySelector('[data-disease-animal]');
                const flockSelect = form.querySelector('[data-disease-flock]');
                const livestockField = form.querySelector('[data-disease-livestock-field]');
                const animalField = form.querySelector('[data-disease-animal-field]');
                const flockField = form.querySelector('[data-disease-flock-field]');
                const targetRadios = form.querySelectorAll('[data-disease-target]');

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
                    form.querySelector('[data-disease-target]:checked')?.value || 'group';

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

                    const groupRadio = form.querySelector('[data-disease-target][value="group"]');
                    if (groupRadio && !form.querySelector('[data-disease-target]:checked')) {
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
