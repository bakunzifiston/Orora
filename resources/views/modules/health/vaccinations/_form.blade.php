@php $vaccination = $vaccination ?? null; @endphp

<div class="vaccination-form">
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => 'Animal and vaccine',
        'description' => 'Select the animal and enter vaccine product details.',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field dash-form-field--full">
                <label for="animal_id">Animal <span class="dash-required">*</span></label>
                <select name="animal_id" id="animal_id" required>
                    <option value="">Select animal</option>
                    @foreach ($animals as $animal)
                        <option value="{{ $animal->id }}" @selected(old('animal_id', $vaccination?->animal_id) == $animal->id)>
                            {{ $animal->tag_number }} — {{ $animal->name }} ({{ $animal->farm->name }})
                        </option>
                    @endforeach
                </select>
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
</div>
