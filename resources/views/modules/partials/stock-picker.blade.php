@php
    $record = $record ?? null;
    $animals = $animals ?? collect();
    $flocks = $flocks ?? collect();
    $farms = $farms ?? collect();
    $required = $required ?? true;
    $showFarmSelect = $showFarmSelect ?? $farms->isNotEmpty();
    $farmSelectId = $farmSelectId ?? 'farm_id';
    $selectedFarmId = old('farm_id', $selectedFarmId ?? $record?->farm_id);
    $selectedFarm = $farms->firstWhere('id', (int) $selectedFarmId);
    $selectedSpecies = $selectedFarm?->primary_species ?: null;
    $showAnimals = $showAnimals ?? ($animals->isNotEmpty() || $flocks->isEmpty());
    $showFlocks = $showFlocks ?? $flocks->isNotEmpty();

    if ($selectedSpecies === 'cattle') {
        $showFlocks = false;
        $showAnimals = true;
    } elseif ($selectedSpecies === 'poultry') {
        $showAnimals = false;
        $showFlocks = true;
    }

    $selectedAnimalId = old('animal_id', $record?->animal_id);
    $selectedFlockId = old('flock_id', $record?->flock_id);
    $animalRequired = $required && $showAnimals && ! $showFlocks;
    $flockRequired = $required && $showFlocks && ! $showAnimals;
    $fullWidth = $fullWidth ?? true;
    $animalFarmAttr = $animalFarmAttr ?? false;
@endphp

<div data-stock-picker data-stock-farm-select="{{ $farmSelectId }}">
    @if ($showFarmSelect)
        <div class="dash-form-field @if ($fullWidth) dash-form-field--full @endif">
            <label for="{{ $farmSelectId }}">{{ __('Farm') }} <span class="dash-required">*</span></label>
            <select name="farm_id" id="{{ $farmSelectId }}" required data-stock-farm>
                <option value="">{{ __('Select farm') }}</option>
                @foreach ($farms as $farm)
                    <option
                        value="{{ $farm->id }}"
                        data-species="{{ $farm->primary_species ?: 'cattle' }}"
                        @selected((string) $selectedFarmId === (string) $farm->id)
                    >{{ $farm->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="dash-form-field @if ($fullWidth && ! $showFlocks) dash-form-field--full @endif" data-stock-animal-field @if (! $showAnimals) hidden @endif>
        <label for="animal_id">{{ __('Animal') }} <span class="dash-required" data-stock-animal-required @if (! $showAnimals || (! $animalRequired && ! $required)) hidden @endif>*</span></label>
        <select name="animal_id" id="animal_id" @if ($animalRequired) required @endif data-stock-animal>
            <option value="">{{ __('Select animal') }}</option>
            @foreach ($animals as $animal)
                <option
                    value="{{ $animal->id }}"
                    data-farm-id="{{ $animal->farm_id }}"
                    data-species="{{ $animal->farm?->primary_species ?: 'cattle' }}"
                    @if ($animalFarmAttr)
                        data-health-status="{{ $animal->health_status }}"
                    @endif
                    @selected((string) $selectedAnimalId === (string) $animal->id)
                >{{ $animal->tag_number }}@if($animal->name) — {{ $animal->name }}@endif @if($animal->farm) ({{ $animal->farm->name }}) @endif</option>
            @endforeach
        </select>
    </div>

    <div class="dash-form-field @if ($fullWidth && ! $showAnimals) dash-form-field--full @endif" data-stock-flock-field @if (! $showFlocks) hidden @endif>
        <label for="flock_id">{{ __('Flock') }} <span class="dash-required" data-stock-flock-required @if (! $showFlocks || (! $flockRequired && ! $required)) hidden @endif>*</span></label>
        <select name="flock_id" id="flock_id" @if ($flockRequired) required @endif data-stock-flock>
            <option value="">{{ __('Select flock') }}</option>
            @foreach ($flocks as $flock)
                <option
                    value="{{ $flock->id }}"
                    data-farm-id="{{ $flock->farm_id }}"
                    data-species="{{ $flock->farm?->primary_species ?: 'poultry' }}"
                    @selected((string) $selectedFlockId === (string) $flock->id)
                >{{ $flock->label() }} ({{ $flock->farm?->name }} · {{ number_format($flock->current_count) }} {{ __('birds') }})</option>
            @endforeach
        </select>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.querySelectorAll('[data-stock-picker]').forEach((root) => {
                const farmSelectId = root.dataset.stockFarmSelect || 'farm_id';
                const farmSelect = document.getElementById(farmSelectId) || root.querySelector('[data-stock-farm]');
                const animalSelect = root.querySelector('[data-stock-animal]');
                const flockSelect = root.querySelector('[data-stock-flock]');
                const animalField = root.querySelector('[data-stock-animal-field]');
                const flockField = root.querySelector('[data-stock-flock-field]');
                const animalRequiredMark = root.querySelector('[data-stock-animal-required]');
                const flockRequiredMark = root.querySelector('[data-stock-flock-required]');

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

                const speciesForFarm = () => {
                    const option = farmSelect?.selectedOptions?.[0];
                    return option?.dataset?.species || '';
                };

                const sync = () => {
                    const farmId = farmSelect?.value || '';
                    const species = speciesForFarm() || 'cattle';
                    const isPoultry = species === 'poultry';

                    filterOptions(animalSelect, (option) => !farmId || option.dataset.farmId === farmId);
                    filterOptions(flockSelect, (option) => !farmId || option.dataset.farmId === farmId);

                    if (!farmId) {
                        if (animalField) animalField.hidden = true;
                        if (flockField) flockField.hidden = true;
                        if (animalSelect) {
                            animalSelect.value = '';
                            animalSelect.required = false;
                        }
                        if (flockSelect) {
                            flockSelect.value = '';
                            flockSelect.required = false;
                        }
                        if (animalRequiredMark) animalRequiredMark.hidden = true;
                        if (flockRequiredMark) flockRequiredMark.hidden = true;
                        return;
                    }

                    // Cattle: animals only. Poultry: flocks only.
                    if (isPoultry) {
                        if (animalField) animalField.hidden = true;
                        if (flockField) flockField.hidden = false;
                        if (animalSelect) {
                            animalSelect.value = '';
                            animalSelect.required = false;
                        }
                        if (flockSelect) flockSelect.required = true;
                        if (animalRequiredMark) animalRequiredMark.hidden = true;
                        if (flockRequiredMark) flockRequiredMark.hidden = false;
                    } else {
                        if (animalField) animalField.hidden = false;
                        if (flockField) flockField.hidden = true;
                        if (flockSelect) {
                            flockSelect.value = '';
                            flockSelect.required = false;
                        }
                        if (animalSelect) animalSelect.required = true;
                        if (animalRequiredMark) animalRequiredMark.hidden = false;
                        if (flockRequiredMark) flockRequiredMark.hidden = true;
                    }
                };

                farmSelect?.addEventListener('change', sync);
                sync();
            });
        </script>
    @endpush
@endonce
