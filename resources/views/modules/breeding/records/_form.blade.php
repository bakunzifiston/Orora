@php
    $breedingRecord = $breedingRecord ?? null;
    $gestationDefaults = $gestationDefaults ?? config('modules.breeding_gestation_days');
@endphp

<div class="dash-form-grid" data-breeding-form data-gestation-defaults='@json($gestationDefaults)'>
    <div class="dash-form-field">
        <label for="farm_id">Farm <span class="dash-required">*</span></label>
        <select name="farm_id" id="farm_id" required data-breeding-farm @disabled($breedingRecord?->birthRecord)>
            <option value="">Select farm</option>
            @foreach ($farms as $farm)
                <option value="{{ $farm->id }}" @selected(old('farm_id', $breedingRecord?->farm_id) == $farm->id)>{{ $farm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="animal_type">Animal type <span class="dash-required">*</span></label>
        <select name="animal_type" id="animal_type" required data-breeding-animal-type @disabled($breedingRecord?->birthRecord)>
            @foreach (config('modules.breeding_animal_types') as $type)
                <option value="{{ $type }}" @selected(old('animal_type', $breedingRecord?->animal_type ?? 'cattle') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="female_animal_id">Female <span class="dash-required">*</span></label>
        <select name="female_animal_id" id="female_animal_id" required data-breeding-female @disabled($breedingRecord?->birthRecord)>
            <option value="">Select female</option>
            @foreach ($femaleAnimals as $animal)
                <option value="{{ $animal->id }}" data-farm-id="{{ $animal->farm_id }}" @selected(old('female_animal_id', $breedingRecord?->female_animal_id) == $animal->id)>
                    {{ $animal->tag_number }} — {{ $animal->name ?: 'Unnamed' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="breeding_date">Breeding date <span class="dash-required">*</span></label>
        <input type="date" name="breeding_date" id="breeding_date" max="{{ now()->format('Y-m-d') }}" value="{{ old('breeding_date', $breedingRecord?->breeding_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required @disabled($breedingRecord?->birthRecord)>
    </div>
    <div class="dash-form-field">
        <label for="breeding_type">Breeding type <span class="dash-required">*</span></label>
        <select name="breeding_type" id="breeding_type" required data-breeding-type @disabled($breedingRecord?->birthRecord)>
            @foreach (config('modules.breeding_types') as $type)
                <option value="{{ $type }}" @selected(old('breeding_type', $breedingRecord?->breeding_type ?? 'natural_mating') === $type)>
                    {{ config('modules.breeding_type_labels')[$type] }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="male_animal_id">Internal sire</label>
        <select name="male_animal_id" id="male_animal_id" data-breeding-male @disabled($breedingRecord?->birthRecord)>
            <option value="">None / external</option>
            @foreach ($maleAnimals as $animal)
                <option value="{{ $animal->id }}" data-farm-id="{{ $animal->farm_id }}" @selected(old('male_animal_id', $breedingRecord?->male_animal_id) == $animal->id)>
                    {{ $animal->tag_number }} — {{ $animal->name ?: 'Unnamed' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="external_sire_name">External sire name</label>
        <input type="text" name="external_sire_name" id="external_sire_name" value="{{ old('external_sire_name', $breedingRecord?->external_sire_name) }}" @disabled($breedingRecord?->birthRecord)>
    </div>
    <div class="dash-form-field">
        <label for="external_sire_breed">External sire breed</label>
        <input type="text" name="external_sire_breed" id="external_sire_breed" value="{{ old('external_sire_breed', $breedingRecord?->external_sire_breed) }}" @disabled($breedingRecord?->birthRecord)>
    </div>
    <div class="dash-form-field">
        <label for="external_sire_code">External sire code</label>
        <input type="text" name="external_sire_code" id="external_sire_code" value="{{ old('external_sire_code', $breedingRecord?->external_sire_code) }}" @disabled($breedingRecord?->birthRecord)>
    </div>
    <div class="dash-form-field">
        <label for="heat_detection_method">Heat detection</label>
        <select name="heat_detection_method" id="heat_detection_method" @disabled($breedingRecord?->birthRecord)>
            <option value="">Not recorded</option>
            @foreach (config('modules.heat_detection_methods') as $method)
                <option value="{{ $method }}" @selected(old('heat_detection_method', $breedingRecord?->heat_detection_method) === $method)>
                    {{ config('modules.heat_detection_method_labels')[$method] }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="heat_detected_date">Heat detected date</label>
        <input type="date" name="heat_detected_date" id="heat_detected_date" max="{{ now()->format('Y-m-d') }}" value="{{ old('heat_detected_date', $breedingRecord?->heat_detected_date?->format('Y-m-d')) }}" @disabled($breedingRecord?->birthRecord)>
    </div>
    <div class="dash-form-field">
        <label for="gestation_period_days">Gestation (days)</label>
        <input type="number" name="gestation_period_days" id="gestation_period_days" min="1" data-breeding-gestation value="{{ old('gestation_period_days', $breedingRecord?->gestation_period_days) }}" @disabled($breedingRecord?->birthRecord)>
        <p class="dash-field-hint">Default by animal type; expected calving is computed automatically.</p>
    </div>
    <div class="dash-form-field" data-breeding-ai-fields>
        <label for="technician_name">Technician (AI)</label>
        <input type="text" name="technician_name" id="technician_name" value="{{ old('technician_name', $breedingRecord?->technician_name) }}" @disabled($breedingRecord?->birthRecord)>
    </div>
    <div class="dash-form-field" data-breeding-ai-fields>
        <label for="semen_batch_number">Semen batch</label>
        <input type="text" name="semen_batch_number" id="semen_batch_number" value="{{ old('semen_batch_number', $breedingRecord?->semen_batch_number) }}" @disabled($breedingRecord?->birthRecord)>
    </div>
    <div class="dash-form-field" data-breeding-ai-fields>
        <label for="semen_straw_code">Semen straw code</label>
        <input type="text" name="semen_straw_code" id="semen_straw_code" value="{{ old('semen_straw_code', $breedingRecord?->semen_straw_code) }}" @disabled($breedingRecord?->birthRecord)>
    </div>
    <div class="dash-form-field dash-form-field--full" data-breeding-ai-fields>
        <label for="semen_source">Semen source</label>
        <input type="text" name="semen_source" id="semen_source" value="{{ old('semen_source', $breedingRecord?->semen_source) }}" @disabled($breedingRecord?->birthRecord)>
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="3" @disabled($breedingRecord?->birthRecord)>{{ old('notes', $breedingRecord?->notes) }}</textarea>
    </div>
</div>

@include('modules.expenses.partials.linked-expense-fields', [
    'expense' => $breedingRecord?->expense,
    'defaultVendorName' => old('technician_name', $breedingRecord?->technician_name ?: $breedingRecord?->semen_source),
    'vendors' => $vendors ?? [],
    'sectionNumber' => '2',
])

<script>
document.querySelectorAll('[data-breeding-form]').forEach((root) => {
    const farmSelect = root.querySelector('[data-breeding-farm]');
    const animalTypeSelect = root.querySelector('[data-breeding-animal-type]');
    const gestationInput = root.querySelector('[data-breeding-gestation]');
    const breedingTypeSelect = root.querySelector('[data-breeding-type]');
    const femaleSelect = root.querySelector('[data-breeding-female]');
    const maleSelect = root.querySelector('[data-breeding-male]');
    const aiFields = root.querySelectorAll('[data-breeding-ai-fields]');

    let defaults = {};
    try {
        defaults = JSON.parse(root.dataset.gestationDefaults || '{}');
    } catch {
        defaults = {};
    }

    const filterByFarm = (select) => {
        if (!select || !farmSelect) return;
        const farmId = farmSelect.value;
        Array.from(select.options).forEach((option) => {
            if (!option.value) {
                option.hidden = false;
                return;
            }
            option.hidden = farmId !== '' && option.dataset.farmId !== farmId;
        });
        if (select.selectedOptions[0]?.hidden) select.value = '';
    };

    const syncGestationDefault = () => {
        if (!gestationInput || !animalTypeSelect || gestationInput.value) return;
        const days = defaults[animalTypeSelect.value];
        if (days) gestationInput.placeholder = String(days);
    };

    const syncAiFields = () => {
        const isAi = breedingTypeSelect?.value === 'artificial_insemination';
        aiFields.forEach((el) => { el.style.display = isAi ? '' : 'none'; });
    };

    farmSelect?.addEventListener('change', () => {
        filterByFarm(femaleSelect);
        filterByFarm(maleSelect);
    });
    animalTypeSelect?.addEventListener('change', syncGestationDefault);
    breedingTypeSelect?.addEventListener('change', syncAiFields);

    filterByFarm(femaleSelect);
    filterByFarm(maleSelect);
    syncGestationDefault();
    syncAiFields();
});
</script>
