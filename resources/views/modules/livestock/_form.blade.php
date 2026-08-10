@php $livestock = $livestock ?? null; @endphp

<div class="livestock-registration" data-livestock-form>
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => __('Farm & herd'),
        'description' => __('Link this livestock group to a farm and basic herd details.'),
        'id' => 'section-livestock-farm',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field dash-form-field--full">
                <label for="farm_id">{{ __('Farm') }} <span class="dash-required">*</span></label>
                <select name="farm_id" id="farm_id" required>
                    <option value="">{{ __('Select farm') }}</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" @selected(old('farm_id', $livestock?->farm_id) == $farm->id)>{{ $farm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="breed">{{ __('Breed') }}</label>
                <input type="text" name="breed" id="breed" value="{{ old('breed', $livestock?->breed) }}">
            </div>
            <div class="dash-form-field">
                <label for="head_count">{{ __('Head count') }} <span class="dash-required">*</span></label>
                <input type="number" name="head_count" id="head_count" min="0" value="{{ old('head_count', $livestock?->head_count ?? 0) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="status">{{ __('Status') }} <span class="dash-required">*</span></label>
                <select name="status" id="status" required>
                    @foreach (config('modules.record_statuses') as $status)
                        <option value="{{ $status }}" @selected(old('status', $livestock?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="notes">{{ __('Notes') }}</label>
                <textarea name="notes" id="notes" rows="3">{{ old('notes', $livestock?->notes) }}</textarea>
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '2',
        'title' => __('Livestock types'),
        'description' => __('Select all animal types in this herd or flock.'),
        'id' => 'section-livestock-types',
    ])
        @include('modules.partials.checkbox-multi', [
            'name' => 'livestock_types',
            'label' => __('Livestock type'),
            'options' => config('modules.livestock_types'),
            'selected' => $livestock?->livestock_types,
            'otherName' => 'livestock_type_other',
            'otherValue' => $livestock?->livestock_type_other,
        ])
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '3',
        'title' => __('Group / herd'),
        'description' => __('Cattle group or herd classification — select all that apply.'),
        'id' => 'section-herd-groups',
    ])
        @include('modules.partials.checkbox-multi', [
            'name' => 'herd_groups',
            'label' => __('Group / herd'),
            'options' => config('modules.herd_groups'),
            'selected' => $livestock?->herd_groups,
            'otherName' => 'herd_group_other',
            'otherValue' => $livestock?->herd_group_other,
        ])
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '4',
        'title' => __('Production purpose'),
        'description' => __('Why this livestock is being raised.'),
        'id' => 'section-production-purpose',
    ])
        @include('modules.partials.checkbox-multi', [
            'name' => 'production_purposes',
            'label' => __('Production purpose'),
            'options' => config('modules.production_purposes'),
            'selected' => $livestock?->production_purposes,
            'otherName' => 'production_purpose_other',
            'otherValue' => $livestock?->production_purpose_other,
        ])
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '5',
        'title' => __('Farming method'),
        'description' => __('How the animals are kept and managed.'),
        'id' => 'section-farming-method',
    ])
        @include('modules.partials.checkbox-multi', [
            'name' => 'farming_methods',
            'label' => __('Farming method'),
            'options' => config('modules.farming_methods'),
            'selected' => $livestock?->farming_methods,
            'otherName' => 'farming_method_other',
            'otherValue' => $livestock?->farming_method_other,
        ])
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '6',
        'title' => __('Feeding method'),
        'description' => __('How animals are fed on this farm.'),
        'id' => 'section-feeding-method',
    ])
        @include('modules.partials.checkbox-multi', [
            'name' => 'feeding_methods',
            'label' => __('Feeding method'),
            'options' => config('modules.feeding_methods'),
            'selected' => $livestock?->feeding_methods,
            'otherName' => 'feeding_method_other',
            'otherValue' => $livestock?->feeding_method_other,
        ])
    @endcomponent
</div>
