@php $treatment = $treatment ?? null; @endphp

<div class="treatment-form">
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => 'Animal and treatment',
        'description' => 'Select the animal and enter disease and medication details.',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field dash-form-field--full">
                <label for="animal_id">Animal <span class="dash-required">*</span></label>
                <select name="animal_id" id="animal_id" required>
                    <option value="">Select animal</option>
                    @foreach ($animals as $animal)
                        <option value="{{ $animal->id }}" @selected(old('animal_id', $treatment?->animal_id) == $animal->id)>
                            {{ $animal->tag_number }} — {{ $animal->name }} ({{ $animal->farm->name }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="disease_name">Disease name <span class="dash-required">*</span></label>
                <input type="text" name="disease_name" id="disease_name" value="{{ old('disease_name', $treatment?->disease_name) }}" required placeholder="e.g. Mastitis">
            </div>
            <div class="dash-form-field">
                <label for="medicine_name">Medicine name <span class="dash-required">*</span></label>
                <input type="text" name="medicine_name" id="medicine_name" value="{{ old('medicine_name', $treatment?->medicine_name) }}" required placeholder="e.g. Oxytetracycline">
            </div>
            <div class="dash-form-field">
                <label for="dosage">Dosage</label>
                <input type="text" name="dosage" id="dosage" value="{{ old('dosage', $treatment?->dosage) }}" placeholder="e.g. 10 ml twice daily">
            </div>
            @include('modules.partials.select-with-other', [
                'name' => 'treatment_method',
                'otherName' => 'treatment_method_other',
                'id' => 'treatment_method',
                'label' => 'Treatment method',
                'options' => config('modules.treatment_methods'),
                'value' => $treatment?->treatment_method,
            ])
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '2',
        'title' => 'Schedule and provider',
        'description' => 'Treatment dates, follow-up, and veterinarian.',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="start_date">Start date <span class="dash-required">*</span></label>
                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $treatment?->start_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="end_date">End date</label>
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $treatment?->end_date?->format('Y-m-d')) }}">
            </div>
            <div class="dash-form-field">
                <label for="follow_up_date">Follow-up date</label>
                <input type="date" name="follow_up_date" id="follow_up_date" value="{{ old('follow_up_date', $treatment?->follow_up_date?->format('Y-m-d')) }}">
            </div>
            <div class="dash-form-field">
                <label for="status">Status <span class="dash-required">*</span></label>
                <select name="status" id="status" required>
                    @foreach (config('modules.treatment_statuses') as $status)
                        <option value="{{ $status }}" @selected(old('status', $treatment?->status ?? 'Ongoing') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="veterinarian_name">Veterinarian name</label>
                <input type="text" name="veterinarian_name" id="veterinarian_name" value="{{ old('veterinarian_name', $treatment?->veterinarian_name) }}">
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
                <textarea name="symptoms" id="symptoms" rows="2" placeholder="Observed symptoms…">{{ old('symptoms', $treatment?->symptoms) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="diagnosis">Diagnosis</label>
                <textarea name="diagnosis" id="diagnosis" rows="2" placeholder="Clinical diagnosis…">{{ old('diagnosis', $treatment?->diagnosis) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="3">{{ old('notes', $treatment?->notes) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="attachment">Attachment</label>
                <div class="dash-photo-field">
                    @if ($treatment?->attachmentUrl())
                        <p class="dash-field-hint">
                            Current file:
                            <a href="{{ $treatment->attachmentUrl() }}" target="_blank" rel="noopener">View attachment</a>
                        </p>
                    @endif
                    <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <p class="dash-field-hint">PDF, image, or Word document up to 5 MB.</p>
                </div>
            </div>
        </div>
    @endcomponent
</div>
