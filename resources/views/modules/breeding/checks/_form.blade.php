@php $breedingRecord = $breedingRecord ?? null; @endphp

<div class="dash-form-grid">
    <div class="dash-form-field dash-form-field--full">
        <label for="breeding_record_id">Breeding record <span class="dash-required">*</span></label>
        <select name="breeding_record_id" id="breeding_record_id" required data-check-breeding>
            <option value="">Select breeding</option>
            @foreach ($eligibleBreedings as $record)
                <option
                    value="{{ $record->id }}"
                    data-female-id="{{ $record->female_animal_id }}"
                    data-breeding-date="{{ $record->breeding_date->format('Y-m-d') }}"
                    @selected(old('breeding_record_id', $breedingRecord?->id) == $record->id)
                >
                    {{ $record->breeding_code }} — {{ $record->femaleAnimal->tag_number }} ({{ $record->breeding_date->format('M j, Y') }})
                </option>
            @endforeach
        </select>
    </div>
    <input type="hidden" name="animal_id" id="animal_id" value="{{ old('animal_id', $breedingRecord?->female_animal_id) }}">
    <div class="dash-form-field">
        <label for="check_date">Check date <span class="dash-required">*</span></label>
        <input type="date" name="check_date" id="check_date" max="{{ now()->format('Y-m-d') }}" value="{{ old('check_date', now()->format('Y-m-d')) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="check_method">Method <span class="dash-required">*</span></label>
        <select name="check_method" id="check_method" required>
            @foreach (config('modules.pregnancy_check_methods') as $method)
                <option value="{{ $method }}" @selected(old('check_method') === $method)>{{ config('modules.pregnancy_check_method_labels')[$method] }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="result">Result <span class="dash-required">*</span></label>
        <select name="result" id="result" required data-check-result>
            @foreach (config('modules.pregnancy_check_results') as $result)
                <option value="{{ $result }}" @selected(old('result') === $result)>{{ config('modules.pregnancy_check_result_labels')[$result] }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="pregnancy_age_days">Pregnancy age (days)</label>
        <input type="number" name="pregnancy_age_days" id="pregnancy_age_days" min="0" value="{{ old('pregnancy_age_days') }}">
    </div>
    <div class="dash-form-field">
        <label for="expected_calving_date">Expected calving</label>
        <input type="date" name="expected_calving_date" id="expected_calving_date" value="{{ old('expected_calving_date') }}">
    </div>
    <div class="dash-form-field">
        <label for="checked_by">Checked by <span class="dash-required">*</span></label>
        <input type="text" name="checked_by" id="checked_by" value="{{ old('checked_by', auth()->user()->name) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="clinic_name">Clinic</label>
        <input type="text" name="clinic_name" id="clinic_name" value="{{ old('clinic_name') }}">
    </div>
    <div class="dash-form-field" data-check-next-wrap>
        <label for="next_check_date">Next check date</label>
        <input type="date" name="next_check_date" id="next_check_date" value="{{ old('next_check_date') }}">
        <p class="dash-field-hint">Required when result is inconclusive.</p>
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
    'defaultVendorName' => old('clinic_name') ?: old('checked_by', auth()->user()->name),
    'vendors' => $vendors ?? [],
    'sectionNumber' => '2',
])

<script>
document.getElementById('breeding_record_id')?.addEventListener('change', function () {
    const opt = this.selectedOptions[0];
    document.getElementById('animal_id').value = opt?.dataset.femaleId || '';
    const checkDate = document.getElementById('check_date');
    if (opt?.dataset.breedingDate) {
        checkDate.min = opt.dataset.breedingDate;
    }
});
document.getElementById('result')?.addEventListener('change', function () {
    const wrap = document.querySelector('[data-check-next-wrap]');
    wrap.style.display = this.value === 'inconclusive' ? '' : 'none';
});
document.getElementById('breeding_record_id')?.dispatchEvent(new Event('change'));
document.getElementById('result')?.dispatchEvent(new Event('change'));
</script>
