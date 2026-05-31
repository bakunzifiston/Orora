@php $milkRecord = $milkRecord ?? null; @endphp

@component('modules.farms._form-section', [
    'number' => '1',
    'title' => 'Animal and date',
    'description' => 'Select the animal and when milk was collected.',
])
    <div class="dash-form-grid">
        <div class="dash-form-field dash-form-field--full">
            <label for="animal_id">Animal <span class="dash-required">*</span></label>
            <select name="animal_id" id="animal_id" required>
                <option value="">Select animal</option>
                @foreach ($animals as $animal)
                    <option value="{{ $animal->id }}" @selected(old('animal_id', $milkRecord?->animal_id) == $animal->id)>
                        {{ $animal->tag_number }} — {{ $animal->name ?: 'Unnamed' }} ({{ $animal->farm->name }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="dash-form-field">
            <label for="recorded_on">Date <span class="dash-required">*</span></label>
            <input type="date" name="recorded_on" id="recorded_on" value="{{ old('recorded_on', $milkRecord?->recorded_on?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
        </div>
        <div class="dash-form-field">
            <label for="session">Milking session</label>
            <select name="session" id="session">
                <option value="">Not set</option>
                @foreach (config('modules.milk_sessions') as $session)
                    <option value="{{ $session }}" @selected(old('session', $milkRecord?->session) === $session)>{{ $session }}</option>
                @endforeach
            </select>
        </div>
        <div class="dash-form-field">
            <label for="livestock_id">Livestock group (optional)</label>
            <select name="livestock_id" id="livestock_id">
                <option value="">None</option>
                @foreach ($livestockGroups as $group)
                    <option value="{{ $group->id }}" @selected(old('livestock_id', $milkRecord?->livestock_id) == $group->id)>{{ $group->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
@endcomponent

@component('modules.farms._form-section', [
    'number' => '2',
    'title' => 'Quantity and quality',
    'description' => 'Volume collected and optional quality details.',
])
    <div class="dash-form-grid">
        <div class="dash-form-field">
            <label for="quantity">Quantity <span class="dash-required">*</span></label>
            <input type="number" step="0.01" min="0.01" name="quantity" id="quantity" value="{{ old('quantity', $milkRecord?->quantity) }}" required placeholder="e.g. 12.5">
        </div>
        <div class="dash-form-field">
            <label for="unit">Unit <span class="dash-required">*</span></label>
            <select name="unit" id="unit" required>
                @foreach (config('modules.milk_units') as $unit)
                    <option value="{{ $unit }}" @selected(old('unit', $milkRecord?->unit ?? 'L') === $unit)>{{ $unit }}</option>
                @endforeach
            </select>
        </div>
        <div class="dash-form-field">
            <label for="fat_percentage">Fat %</label>
            <input type="number" step="0.01" min="0" max="100" name="fat_percentage" id="fat_percentage" value="{{ old('fat_percentage', $milkRecord?->fat_percentage) }}" placeholder="e.g. 4.2">
        </div>
        <div class="dash-form-field">
            <label for="quality_grade">Quality grade</label>
            <select name="quality_grade" id="quality_grade">
                <option value="">Not set</option>
                @foreach (config('modules.milk_quality_grades') as $grade)
                    <option value="{{ $grade }}" @selected(old('quality_grade', $milkRecord?->quality_grade) === $grade)>{{ $grade }}</option>
                @endforeach
            </select>
        </div>
        <div class="dash-form-field dash-form-field--full">
            <label for="notes">Notes</label>
            <textarea name="notes" id="notes" rows="3" placeholder="Observations about this milking…">{{ old('notes', $milkRecord?->notes) }}</textarea>
        </div>
    </div>
@endcomponent
