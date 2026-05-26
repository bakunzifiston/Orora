@php
    $healthRecord = $healthRecord ?? null;
    $defaultType = $defaultType ?? null;
@endphp

<div class="health-registration" data-health-form>
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => 'Animal & record',
        'description' => 'Link this health event to a farm and animal.',
        'id' => 'section-health-animal',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="farm_id">Farm <span class="dash-required">*</span></label>
                <select name="farm_id" id="farm_id" required data-health-farm>
                    <option value="">Select farm</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" @selected(old('farm_id', $healthRecord?->farm_id) == $farm->id)>{{ $farm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="animal_id">Animal <span class="dash-required">*</span></label>
                <select name="animal_id" id="animal_id" required data-health-animal>
                    <option value="">Select animal</option>
                    @foreach ($animals as $animal)
                        <option
                            value="{{ $animal->id }}"
                            data-farm-id="{{ $animal->farm_id }}"
                            data-health-status="{{ $animal->health_status }}"
                            @selected(old('animal_id', $healthRecord?->animal_id) == $animal->id)
                        >{{ $animal->tag_number }} — {{ $animal->name }} ({{ $animal->health_status }})</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="record_type">Record type <span class="dash-required">*</span></label>
                <select name="record_type" id="record_type" required>
                    @foreach (config('modules.health_record_types') as $type)
                        <option value="{{ $type }}" @selected(old('record_type', $healthRecord?->record_type ?? $defaultType) === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="recorded_on">Recorded on <span class="dash-required">*</span></label>
                <input type="date" name="recorded_on" id="recorded_on" value="{{ old('recorded_on', $healthRecord?->recorded_on?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="health_status">Health status <span class="dash-required">*</span></label>
                <select name="health_status" id="health_status" required data-health-status>
                    @foreach (config('modules.health_statuses') as $status)
                        <option value="{{ $status }}" @selected(old('health_status', $healthRecord?->health_status ?? 'Healthy') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <p class="dash-field-hint">Saving updates the animal&apos;s current health status.</p>
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '2',
        'title' => 'Clinical details',
        'description' => 'Diagnosis, treatment, and follow-up information.',
        'id' => 'section-health-clinical',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field dash-form-field--full">
                <label for="title">Summary / diagnosis</label>
                <input type="text" name="title" id="title" value="{{ old('title', $healthRecord?->title) }}" placeholder="e.g. Annual vaccination, Mastitis treatment">
            </div>
            <div class="dash-form-field">
                <label for="treatment">Treatment</label>
                <input type="text" name="treatment" id="treatment" value="{{ old('treatment', $healthRecord?->treatment) }}">
            </div>
            <div class="dash-form-field">
                <label for="medication">Medication</label>
                <input type="text" name="medication" id="medication" value="{{ old('medication', $healthRecord?->medication) }}">
            </div>
            <div class="dash-form-field">
                <label for="veterinarian">Veterinarian</label>
                <input type="text" name="veterinarian" id="veterinarian" value="{{ old('veterinarian', $healthRecord?->veterinarian) }}">
            </div>
            <div class="dash-form-field">
                <label for="next_follow_up">Next follow-up</label>
                <input type="date" name="next_follow_up" id="next_follow_up" value="{{ old('next_follow_up', $healthRecord?->next_follow_up?->format('Y-m-d')) }}">
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="4">{{ old('notes', $healthRecord?->notes) }}</textarea>
            </div>
        </div>
    @endcomponent
</div>
