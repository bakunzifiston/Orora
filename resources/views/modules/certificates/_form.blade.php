@php $certificate = $certificate ?? null; @endphp

<div class="dash-form-grid">
    <div class="dash-form-field">
        <label for="farm_id">Farm</label>
        <select name="farm_id" id="farm_id" required>
            <option value="">Select farm</option>
            @foreach ($farms as $farm)
                <option value="{{ $farm->id }}" @selected(old('farm_id', $certificate?->farm_id) == $farm->id)>{{ $farm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="animal_id">Animal (optional)</label>
        <select name="animal_id" id="animal_id">
            <option value="">None</option>
            @foreach ($animals as $animal)
                <option value="{{ $animal->id }}" @selected(old('animal_id', $certificate?->animal_id) == $animal->id)>{{ $animal->tag_number }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="certificate_type">Certificate type</label>
        <select name="certificate_type" id="certificate_type" required>
            @foreach (config('modules.certificate_types') as $type)
                <option value="{{ $type }}" @selected(old('certificate_type', $certificate?->certificate_type) === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="certificate_number">Certificate number</label>
        <input type="text" name="certificate_number" id="certificate_number" value="{{ old('certificate_number', $certificate?->certificate_number) }}">
    </div>
    <div class="dash-form-field">
        <label for="issuing_authority">Issuing authority</label>
        <input type="text" name="issuing_authority" id="issuing_authority" value="{{ old('issuing_authority', $certificate?->issuing_authority) }}">
    </div>
    <div class="dash-form-field">
        <label for="issued_on">Issued on</label>
        <input type="date" name="issued_on" id="issued_on" value="{{ old('issued_on', $certificate?->issued_on?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="expires_on">Expires on</label>
        <input type="date" name="expires_on" id="expires_on" value="{{ old('expires_on', $certificate?->expires_on?->format('Y-m-d')) }}">
    </div>
    <div class="dash-form-field">
        <label for="status">Status</label>
        <select name="status" id="status" required>
            @foreach (config('modules.certificate_statuses') as $status)
                <option value="{{ $status }}" @selected(old('status', $certificate?->status ?? 'valid') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field dash-form-field--full">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="3">{{ old('notes', $certificate?->notes) }}</textarea>
    </div>
</div>
