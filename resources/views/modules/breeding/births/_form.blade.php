@php $breedingRecord = $breedingRecord ?? null; @endphp

<div class="dash-form-grid">
    <div class="dash-form-field dash-form-field--full">
        <label for="breeding_record_id">Breeding record <span class="dash-required">*</span></label>
        <select name="breeding_record_id" id="breeding_record_id" required>
            <option value="">Select breeding</option>
            @foreach ($eligibleBreedings as $record)
                <option
                    value="{{ $record->id }}"
                    data-mother-id="{{ $record->female_animal_id }}"
                    @selected(old('breeding_record_id', $breedingRecord?->id) == $record->id)
                >
                    {{ $record->breeding_code }} — {{ $record->femaleAnimal->tag_number }}
                </option>
            @endforeach
        </select>
    </div>
    <input type="hidden" name="mother_animal_id" id="mother_animal_id" value="{{ old('mother_animal_id', $breedingRecord?->female_animal_id) }}">
    <div class="dash-form-field">
        <label for="birth_date">Birth date <span class="dash-required">*</span></label>
        <input type="date" name="birth_date" id="birth_date" max="{{ now()->format('Y-m-d') }}" value="{{ old('birth_date', now()->format('Y-m-d')) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="birth_type">Birth type <span class="dash-required">*</span></label>
        <select name="birth_type" id="birth_type" required>
            @foreach (config('modules.birth_types') as $type)
                <option value="{{ $type }}" @selected(old('birth_type', 'single') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="total_offspring">Total offspring <span class="dash-required">*</span></label>
        <input type="number" name="total_offspring" id="total_offspring" min="1" value="{{ old('total_offspring', 1) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="alive_offspring">Alive <span class="dash-required">*</span></label>
        <input type="number" name="alive_offspring" id="alive_offspring" min="0" value="{{ old('alive_offspring', 1) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="stillborn_offspring">Stillborn</label>
        <input type="number" name="stillborn_offspring" id="stillborn_offspring" min="0" value="{{ old('stillborn_offspring', 0) }}">
    </div>
    <div class="dash-form-field">
        <label for="birth_difficulty">Difficulty <span class="dash-required">*</span></label>
        <select name="birth_difficulty" id="birth_difficulty" required>
            @foreach (config('modules.birth_difficulties') as $level)
                <option value="{{ $level }}" @selected(old('birth_difficulty', 'easy') === $level)>{{ config('modules.birth_difficulty_labels')[$level] }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="birth_weight_kg">Avg birth weight (kg)</label>
        <input type="number" step="0.01" name="birth_weight_kg" id="birth_weight_kg" min="0" value="{{ old('birth_weight_kg') }}">
    </div>
    <div class="dash-form-field">
        <label for="mother_condition_after">Mother after birth <span class="dash-required">*</span></label>
        <select name="mother_condition_after" id="mother_condition_after" required>
            @foreach (config('modules.mother_conditions_after_birth') as $condition)
                <option value="{{ $condition }}" @selected(old('mother_condition_after', 'good') === $condition)>{{ config('modules.mother_condition_after_labels')[$condition] }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="assisted_by">Assisted by</label>
        <input type="text" name="assisted_by" id="assisted_by" value="{{ old('assisted_by') }}">
    </div>
    <div class="dash-form-field">
        <label for="veterinarian_name">Veterinarian</label>
        <input type="text" name="veterinarian_name" id="veterinarian_name" value="{{ old('veterinarian_name') }}">
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="attachment">Attachment</label>
        <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png">
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="3">{{ old('notes') }}</textarea>
    </div>
</div>

@include('modules.expenses.partials.linked-expense-fields', [
    'defaultVendorName' => old('veterinarian_name') ?: old('assisted_by'),
    'vendors' => $vendors ?? [],
    'sectionNumber' => '2',
])

<script>
document.getElementById('breeding_record_id')?.addEventListener('change', function () {
    const opt = this.selectedOptions[0];
    document.getElementById('mother_animal_id').value = opt?.dataset.motherId || '';
});
document.getElementById('breeding_record_id')?.dispatchEvent(new Event('change'));
</script>
