@php
    $deworming = $deworming ?? null;
    $selectedFarmId = old('farm_id', $deworming?->farm_id);
    $selectedLivestockId = old('livestock_id', $deworming?->livestock_id);
    $selectedAnimalId = old('animal_id', $deworming?->animal_id);
    $selectedFlockId = old('flock_id', $deworming?->flock_id);
    $selectedFarm = ($farms ?? collect())->firstWhere('id', (int) $selectedFarmId);
    $isPoultryFarm = $selectedFarm?->isPoultry() ?? false;
    $dewormingTarget = old('deworming_target');

    if ($dewormingTarget === null) {
        $dewormingTarget = $selectedAnimalId ? 'animal' : 'group';
    }

    if (! in_array($dewormingTarget, ['group', 'animal'], true)) {
        $dewormingTarget = 'group';
    }
@endphp

<div class="deworming-form" data-deworming-target-form>
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => __('Who is dewormed'),
        'description' => __('Choose the farm, then a whole livestock group or one animal. Poultry farms use a flock.'),
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="farm_id">{{ __('Farm') }} <span class="dash-required">*</span></label>
                <select name="farm_id" id="farm_id" required data-deworming-farm>
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

            <div data-deworming-cattle-target style="display: contents;" @if ($isPoultryFarm) hidden @endif>
                <div class="dash-form-field dash-form-field--full">
                    <label>{{ __('Deworm') }}</label>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <label class="dash-checkbox">
                            <input type="radio" name="deworming_target" value="group" @checked($dewormingTarget === 'group') data-deworming-target>
                            <span>{{ __('Entire livestock group') }}</span>
                        </label>
                        <label class="dash-checkbox">
                            <input type="radio" name="deworming_target" value="animal" @checked($dewormingTarget === 'animal') data-deworming-target>
                            <span>{{ __('One animal') }}</span>
                        </label>
                    </div>
                    <p class="dash-field-hint">{{ __('Choose a whole group or a single animal from that group—not both.') }}</p>
                </div>

                <div class="dash-form-field" data-deworming-livestock-field @if ($isPoultryFarm) hidden @endif>
                    <label for="livestock_id">{{ __('Livestock group') }} <span class="dash-required">*</span></label>
                    <select name="livestock_id" id="livestock_id" data-deworming-livestock>
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

                <div class="dash-form-field" data-deworming-animal-field @if ($dewormingTarget !== 'animal' || $isPoultryFarm) hidden @endif>
                    <label for="animal_id">{{ __('Animal') }} <span class="dash-required">*</span></label>
                    <select name="animal_id" id="animal_id" data-deworming-animal>
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

            <div class="dash-form-field" data-deworming-flock-field @if (! $isPoultryFarm) hidden @endif>
                <label for="flock_id">{{ __('Flock') }} <span class="dash-required">*</span></label>
                <select name="flock_id" id="flock_id" data-deworming-flock>
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
                <label for="product_name">{{ __('Product name') }} <span class="dash-required">*</span></label>
                <input type="text" name="product_name" id="product_name" value="{{ old('product_name', $deworming?->product_name) }}" required placeholder="e.g. Ivomec, Albendazole">
            </div>

            <div class="dash-form-field">
                <label for="medicine_id">{{ __('Medicine inventory item') }}</label>
                <select name="medicine_id" id="medicine_id">
                    <option value="">{{ __('Select medicine from inventory (optional)') }}</option>
                    @foreach (($medicines ?? collect()) as $medicine)
                        <option value="{{ $medicine->id }}" @selected((string) old('medicine_id', $deworming?->medicine_id) === (string) $medicine->id)>
                            {{ $medicine->name }} ({{ $medicine->stockLabel() }})
                        </option>
                    @endforeach
                </select>
                @error('medicine_id')<p class="dash-form-error">{{ $message }}</p>@enderror
            </div>

            <div class="dash-form-field">
                <label for="quantity_used">{{ __('Quantity used') }}</label>
                <input type="number" step="0.01" min="0.01" name="quantity_used" id="quantity_used" value="{{ old('quantity_used', $deworming?->quantity_used) }}" placeholder="0.00">
                <p class="dash-field-hint">{{ __('Deducted from the medicine inventory when saved.') }}</p>
                @error('quantity_used')<p class="dash-form-error">{{ $message }}</p>@enderror
            </div>

            @include('modules.partials.select-with-other', [
                'name' => 'product_type',
                'otherName' => 'product_type_other',
                'id' => 'product_type',
                'label' => __('Product type'),
                'options' => config('modules.dewormer_types'),
                'value' => $deworming?->product_type,
            ])
            <div class="dash-form-field">
                <label for="active_ingredient">{{ __('Active ingredient') }}</label>
                <input type="text" name="active_ingredient" id="active_ingredient" value="{{ old('active_ingredient', $deworming?->active_ingredient) }}" placeholder="e.g. Ivermectin">
            </div>
            <div class="dash-form-field">
                <label for="manufacturer">{{ __('Manufacturer') }}</label>
                <input type="text" name="manufacturer" id="manufacturer" value="{{ old('manufacturer', $deworming?->manufacturer) }}">
            </div>
            <div class="dash-form-field">
                <label for="batch_number">{{ __('Batch number') }}</label>
                <input type="text" name="batch_number" id="batch_number" value="{{ old('batch_number', $deworming?->batch_number) }}">
            </div>
            <div class="dash-form-field">
                <label for="dosage">{{ __('Dosage') }}</label>
                <input type="text" name="dosage" id="dosage" value="{{ old('dosage', $deworming?->dosage) }}" placeholder="e.g. 1 ml / 50 kg">
            </div>
            @include('modules.partials.select-with-other', [
                'name' => 'administration_method',
                'otherName' => 'administration_method_other',
                'id' => 'administration_method',
                'label' => __('Administration method'),
                'options' => config('modules.administration_methods'),
                'value' => $deworming?->administration_method,
            ])
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '2',
        'title' => __('Schedule and provider'),
        'description' => __('When deworming was or will be done, and who administered it.'),
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="deworming_date">{{ __('Deworming date') }} <span class="dash-required">*</span></label>
                <input type="date" name="deworming_date" id="deworming_date" value="{{ old('deworming_date', $deworming?->deworming_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="next_due_date">{{ __('Next due date') }}</label>
                <input type="date" name="next_due_date" id="next_due_date" value="{{ old('next_due_date', $deworming?->next_due_date?->format('Y-m-d')) }}">
            </div>
            <div class="dash-form-field">
                <label for="status">{{ __('Status') }} <span class="dash-required">*</span></label>
                <select name="status" id="status" required>
                    @foreach (config('modules.vaccination_statuses') as $status)
                        <option value="{{ $status }}" @selected(old('status', $deworming?->status ?? 'Scheduled') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="veterinarian_name">{{ __('Veterinarian name') }}</label>
                <input type="text" name="veterinarian_name" id="veterinarian_name" value="{{ old('veterinarian_name', $deworming?->veterinarian_name) }}">
            </div>
            <div class="dash-form-field">
                <label for="administered_by">{{ __('Administered by') }}</label>
                <input type="text" name="administered_by" id="administered_by" value="{{ old('administered_by', $deworming?->administered_by) }}" placeholder="{{ __('Staff member name') }}">
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '3',
        'title' => __('Notes and files'),
        'description' => __('Optional notes and supporting documents.'),
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field dash-form-field--full">
                <label for="notes">{{ __('Notes') }}</label>
                <textarea name="notes" id="notes" rows="3">{{ old('notes', $deworming?->notes) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="attachment">{{ __('Attachment') }}</label>
                <div class="dash-photo-field">
                    @if ($deworming?->attachmentUrl())
                        <p class="dash-field-hint">
                            {{ __('Current file:') }}
                            <a href="{{ $deworming->attachmentUrl() }}" target="_blank" rel="noopener">{{ __('View attachment') }}</a>
                        </p>
                    @endif
                    <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <p class="dash-field-hint">{{ __('PDF, image, or Word document up to 5 MB.') }}</p>
                </div>
            </div>
        </div>
    @endcomponent

    @include('modules.expenses.partials.linked-expense-fields', [
        'expense' => $deworming?->expense,
        'defaultVendorName' => $deworming?->veterinarian_name,
        'vendors' => $vendors ?? [],
        'sectionNumber' => '4',
    ])
</div>

@once
    @push('scripts')
        <script>
            (() => {
                const form = document.querySelector('[data-deworming-target-form]');
                if (!form) return;

                const farmSelect = form.querySelector('[data-deworming-farm]');
                const cattleTarget = form.querySelector('[data-deworming-cattle-target]');
                const livestockSelect = form.querySelector('[data-deworming-livestock]');
                const animalSelect = form.querySelector('[data-deworming-animal]');
                const flockSelect = form.querySelector('[data-deworming-flock]');
                const livestockField = form.querySelector('[data-deworming-livestock-field]');
                const animalField = form.querySelector('[data-deworming-animal-field]');
                const flockField = form.querySelector('[data-deworming-flock-field]');
                const targetRadios = form.querySelectorAll('[data-deworming-target]');

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
                    form.querySelector('[data-deworming-target]:checked')?.value || 'group';

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

                    const groupRadio = form.querySelector('[data-deworming-target][value="group"]');
                    if (groupRadio && !form.querySelector('[data-deworming-target]:checked')) {
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
