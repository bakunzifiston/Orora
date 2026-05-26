@php $animal = $animal ?? null; @endphp

<div class="animal-registration" data-animal-form>
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => 'Identity',
        'description' => 'Core identification linked to a farm and livestock group.',
        'id' => 'section-animal-identity',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field dash-form-field--full">
                <label for="farm_id">Farm <span class="dash-required">*</span></label>
                <select name="farm_id" id="farm_id" required data-animal-farm>
                    <option value="">Select farm</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" @selected(old('farm_id', $animal?->farm_id) == $farm->id)>{{ $farm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="livestock_id">Livestock group <span class="dash-required">*</span></label>
                <select name="livestock_id" id="livestock_id" required data-animal-livestock>
                    <option value="">Select livestock group</option>
                    @foreach ($livestockGroups as $group)
                        <option
                            value="{{ $group->id }}"
                            data-farm-id="{{ $group->farm_id }}"
                            @selected(old('livestock_id', $animal?->livestock_id) == $group->id)
                        >{{ $group->herd_groups_label }} — {{ $group->farm->name }}</option>
                    @endforeach
                </select>
                <p class="dash-field-hint">Tag numbers must be unique within the selected livestock group.</p>
            </div>
            <div class="dash-form-field">
                <label for="name">Animal name <span class="dash-required">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $animal?->name) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="tag_number">Tag number <span class="dash-required">*</span></label>
                <input type="text" name="tag_number" id="tag_number" value="{{ old('tag_number', $animal?->tag_number) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="gender">Gender <span class="dash-required">*</span></label>
                <select name="gender" id="gender" required>
                    @foreach (config('modules.animal_genders') as $value => $label)
                        <option value="{{ $value }}" @selected(old('gender', $animal?->gender ?? 'male') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="photo">Photo</label>
                <div class="dash-photo-field">
                    @if ($animal?->photo_url)
                        <img src="{{ $animal->photo_url }}" alt="" class="dash-photo-preview" data-photo-preview>
                    @else
                        <img src="" alt="" class="dash-photo-preview" data-photo-preview hidden>
                    @endif
                    <input type="file" name="photo" id="photo" accept="image/*" data-photo-input>
                    <p class="dash-field-hint">JPEG or PNG, max 2 MB. Leave empty to keep the current photo.</p>
                </div>
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '2',
        'title' => 'Physical profile',
        'description' => 'Birth date, weight, and visible characteristics.',
        'id' => 'section-animal-physical',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="date_of_birth">Birth date</label>
                <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $animal?->date_of_birth?->format('Y-m-d')) }}" data-birth-date>
            </div>
            <div class="dash-form-field">
                <label for="age_display">Age</label>
                <input type="text" id="age_display" value="{{ $animal?->age_label }}" readonly placeholder="Calculated from birth date" data-age-display>
            </div>
            <div class="dash-form-field">
                <label for="weight_kg">Weight (kg)</label>
                <input type="number" step="0.01" name="weight_kg" id="weight_kg" min="0" value="{{ old('weight_kg', $animal?->weight_kg) }}">
            </div>
            <div class="dash-form-field">
                <label for="color_markings">Color / markings</label>
                <input type="text" name="color_markings" id="color_markings" value="{{ old('color_markings', $animal?->color_markings) }}" placeholder="e.g. Black and white patches">
            </div>
            <div class="dash-form-field">
                <label for="species">Species</label>
                <select name="species" id="species">
                    <option value="">Not set</option>
                    @foreach (config('modules.livestock_types') as $species)
                        <option value="{{ $species }}" @selected(old('species', $animal?->species) === $species)>{{ $species }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="breed">Breed</label>
                <input type="text" name="breed" id="breed" value="{{ old('breed', $animal?->breed) }}">
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '3',
        'title' => 'Acquisition and lineage',
        'description' => 'How the animal was acquired and parent tag references.',
        'id' => 'section-animal-acquisition',
    ])
        <div class="dash-form-grid">
            @include('modules.partials.select-optional', [
                'name' => 'acquisition_type',
                'label' => 'Acquisition type',
                'options' => config('modules.acquisition_types'),
                'value' => $animal?->acquisition_type,
            ])
            <div class="dash-form-field">
                <label for="acquisition_date">Acquisition date</label>
                <input type="date" name="acquisition_date" id="acquisition_date" value="{{ old('acquisition_date', $animal?->acquisition_date?->format('Y-m-d')) }}">
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="source">Source</label>
                <input type="text" name="source" id="source" value="{{ old('source', $animal?->source) }}" placeholder="Seller, farm, or origin">
            </div>
            <div class="dash-form-field">
                <label for="mother_tag">Mother tag</label>
                <input type="text" name="mother_tag" id="mother_tag" value="{{ old('mother_tag', $animal?->mother_tag) }}">
            </div>
            <div class="dash-form-field">
                <label for="father_tag">Father tag</label>
                <input type="text" name="father_tag" id="father_tag" value="{{ old('father_tag', $animal?->father_tag) }}">
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '4',
        'title' => 'Status',
        'description' => 'Health, production, lifecycle, and current condition.',
        'id' => 'section-animal-status',
    ])
        <div class="dash-form-grid">
            @include('modules.partials.select-optional', [
                'name' => 'health_status',
                'label' => 'Health status',
                'options' => config('modules.health_statuses'),
                'value' => old('health_status', $animal?->health_status ?? 'Healthy'),
                'required' => true,
            ])
            @include('modules.partials.select-optional', [
                'name' => 'production_status',
                'label' => 'Production status',
                'options' => config('modules.production_statuses'),
                'value' => $animal?->production_status,
            ])
            @include('modules.partials.select-optional', [
                'name' => 'lifecycle_status',
                'label' => 'Lifecycle status',
                'options' => config('modules.lifecycle_statuses'),
                'value' => old('lifecycle_status', $animal?->lifecycle_status ?? 'Active'),
                'required' => true,
            ])
            @include('modules.partials.select-optional', [
                'name' => 'current_condition',
                'label' => 'Current condition',
                'options' => config('modules.current_conditions'),
                'value' => $animal?->current_condition,
            ])
            <div class="dash-form-field dash-form-field--full">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="4">{{ old('notes', $animal?->notes) }}</textarea>
            </div>
        </div>
    @endcomponent
</div>
