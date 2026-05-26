@php $vetVisit = $vetVisit ?? null; @endphp

<div class="vet-visit-form">
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => 'Animal and visit',
        'description' => 'Select the animal and enter disease and medication details.',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field dash-form-field--full">
                <label for="animal_id">Animal <span class="dash-required">*</span></label>
                <select name="animal_id" id="animal_id" required>
                    <option value="">Select animal</option>
                    @foreach ($animals as $animal)
                        <option value="{{ $animal->id }}" @selected(old('animal_id', $vetVisit?->animal_id) == $animal->id)>
                            {{ $animal->tag_number }} — {{ $animal->name }} ({{ $animal->farm->name }})
                        </option>
                    @endforeach
                </select>
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
</div>
