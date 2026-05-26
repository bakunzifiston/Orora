@php $mortality = $mortality ?? null; @endphp

<div class="mortality-form">
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => 'Animal and death details',
        'description' => 'Record the animal and when death occurred.',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field dash-form-field--full">
                <label for="animal_id">Animal <span class="dash-required">*</span></label>
                <select name="animal_id" id="animal_id" required>
                    <option value="">Select animal</option>
                    @foreach ($animals as $animal)
                        <option value="{{ $animal->id }}" @selected(old('animal_id', $mortality?->animal_id) == $animal->id)>
                            {{ $animal->tag_number }} — {{ $animal->name }} ({{ $animal->farm->name }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="death_date">Death date <span class="dash-required">*</span></label>
                <input type="date" name="death_date" id="death_date" value="{{ old('death_date', $mortality?->death_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="cause_of_death">Cause of death</label>
                <input type="text" name="cause_of_death" id="cause_of_death" value="{{ old('cause_of_death', $mortality?->cause_of_death) }}" placeholder="e.g. Disease, injury, unknown">
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '2',
        'title' => 'Reporting and disposal',
        'description' => 'Who reported the death, veterinarian, and how remains were handled.',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="reported_by">Reported by</label>
                <input type="text" name="reported_by" id="reported_by" value="{{ old('reported_by', $mortality?->reported_by) }}" placeholder="Staff member name">
            </div>
            <div class="dash-form-field">
                <label for="veterinarian_name">Veterinarian name</label>
                <input type="text" name="veterinarian_name" id="veterinarian_name" value="{{ old('veterinarian_name', $mortality?->veterinarian_name) }}">
            </div>
            @include('modules.partials.select-with-other', [
                'name' => 'disposal_method',
                'otherName' => 'disposal_method_other',
                'id' => 'disposal_method',
                'label' => 'Disposal method',
                'options' => config('modules.disposal_methods'),
                'value' => $mortality?->disposal_method,
            ])
            <div class="dash-form-field dash-form-field--full">
                <label class="dash-checkbox">
                    <input
                        type="checkbox"
                        name="postmortem_done"
                        id="postmortem_done"
                        value="1"
                        @checked(old('postmortem_done', $mortality?->postmortem_done))
                    >
                    <span>Postmortem done</span>
                </label>
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '3',
        'title' => 'Notes and files',
        'description' => 'Additional notes and supporting documents.',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field dash-form-field--full">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="3">{{ old('notes', $mortality?->notes) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="attachment">Attachment</label>
                <div class="dash-photo-field">
                    @if ($mortality?->attachmentUrl())
                        <p class="dash-field-hint">
                            Current file:
                            <a href="{{ $mortality->attachmentUrl() }}" target="_blank" rel="noopener">View attachment</a>
                        </p>
                    @endif
                    <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <p class="dash-field-hint">PDF, image, or Word document up to 5 MB.</p>
                </div>
            </div>
        </div>
    @endcomponent
</div>
