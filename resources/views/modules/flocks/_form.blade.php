@php $flock = $flock ?? null; @endphp

<div class="animal-registration" data-flock-form>
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => __('Farm & identity'),
        'description' => __('Which farm this batch belongs to, and how it should appear in lists.'),
        'id' => 'section-flock-identity',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="farm_id">{{ __('Farm') }} <span class="dash-required">*</span></label>
                <select name="farm_id" id="farm_id" required data-flock-farm>
                    <option value="">{{ __('Select poultry farm') }}</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" @selected(old('farm_id', $flock?->farm_id) == $farm->id)>{{ $farm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="livestock_id">{{ __('House / group') }}</label>
                <select name="livestock_id" id="livestock_id" data-flock-livestock>
                    <option value="">{{ __('None') }}</option>
                    @foreach ($livestockGroups as $group)
                        <option
                            value="{{ $group->id }}"
                            data-farm-id="{{ $group->farm_id }}"
                            @selected(old('livestock_id', $flock?->livestock_id) == $group->id)
                        >{{ $group->name }}@if ($group->farm) — {{ $group->farm->name }}@endif</option>
                    @endforeach
                </select>
                <p class="dash-field-hint">{{ __('Optional livestock group used for housing this flock.') }}</p>
            </div>
            <div class="dash-form-field">
                <label for="name">{{ __('Flock name') }} <span class="dash-required">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $flock?->name) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="production_type">{{ __('Production type') }} <span class="dash-required">*</span></label>
                <select name="production_type" id="production_type" required>
                    @foreach (config('modules.flock_production_types') as $key => $label)
                        <option value="{{ $key }}" @selected(old('production_type', $flock?->production_type) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="breed">{{ __('Breed') }}</label>
                <input type="text" name="breed" id="breed" value="{{ old('breed', $flock?->breed) }}">
            </div>
            <div class="dash-form-field">
                <label for="source">{{ __('Source') }}</label>
                <select name="source" id="source">
                    <option value="">{{ __('Select') }}</option>
                    @foreach (config('modules.flock_sources') as $key => $label)
                        <option value="{{ $key }}" @selected(old('source', $flock?->source) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '2',
        'title' => __('Placement'),
        'description' => __('When birds arrived and how many were placed.'),
        'id' => 'section-flock-placement',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="placed_on">{{ __('Placement date') }} <span class="dash-required">*</span></label>
                <input type="date" name="placed_on" id="placed_on" value="{{ old('placed_on', $flock?->placed_on?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="placed_count">{{ __('Birds placed') }} <span class="dash-required">*</span></label>
                <input type="number" name="placed_count" id="placed_count" min="1" value="{{ old('placed_count', $flock?->placed_count) }}" required>
            </div>
            @if ($flock)
                <div class="dash-form-field">
                    <label for="current_count">{{ __('Current count') }}</label>
                    <input type="number" name="current_count" id="current_count" min="0" value="{{ old('current_count', $flock->current_count) }}">
                    <p class="dash-field-hint">{{ __('Use this for corrections. Mortality and sales update the count automatically.') }}</p>
                </div>
            @endif
            <div class="dash-form-field">
                <label for="male_count">{{ __('Males') }}</label>
                <input type="number" name="male_count" id="male_count" min="0" value="{{ old('male_count', $flock?->male_count) }}">
            </div>
            <div class="dash-form-field">
                <label for="female_count">{{ __('Females') }}</label>
                <input type="number" name="female_count" id="female_count" min="0" value="{{ old('female_count', $flock?->female_count) }}">
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '3',
        'title' => __('Housing & status'),
        'description' => __('Where the flock is kept and its current lifecycle.'),
        'id' => 'section-flock-housing',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="house_or_pen">{{ __('House / pen') }}</label>
                <input type="text" name="house_or_pen" id="house_or_pen" value="{{ old('house_or_pen', $flock?->house_or_pen) }}">
            </div>
            <div class="dash-form-field">
                <label for="expected_end_on">{{ __('Expected harvest / end of lay') }}</label>
                <input type="date" name="expected_end_on" id="expected_end_on" value="{{ old('expected_end_on', $flock?->expected_end_on?->format('Y-m-d')) }}">
            </div>
            <div class="dash-form-field">
                <label for="lifecycle_status">{{ __('Status') }} <span class="dash-required">*</span></label>
                <select name="lifecycle_status" id="lifecycle_status" required>
                    @foreach (config('modules.lifecycle_statuses') as $status)
                        <option value="{{ $status }}" @selected(old('lifecycle_status', $flock?->lifecycle_status ?? 'Active') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="notes">{{ __('Notes') }}</label>
                <textarea name="notes" id="notes" rows="3">{{ old('notes', $flock?->notes) }}</textarea>
            </div>
        </div>
    @endcomponent
</div>

@once
    @push('scripts')
        <script>
            document.querySelectorAll('[data-flock-form]').forEach((root) => {
                const farmSelect = root.querySelector('[data-flock-farm]');
                const groupSelect = root.querySelector('[data-flock-livestock]');

                if (! farmSelect || ! groupSelect) {
                    return;
                }

                function filterGroups() {
                    const farmId = farmSelect.value;
                    let hasVisibleSelection = false;

                    Array.from(groupSelect.options).forEach((option) => {
                        if (! option.value) {
                            option.hidden = false;
                            return;
                        }

                        const matches = ! farmId || option.dataset.farmId === farmId;
                        option.hidden = ! matches;

                        if (option.selected && matches) {
                            hasVisibleSelection = true;
                        }
                    });

                    if (! hasVisibleSelection) {
                        groupSelect.value = '';
                    }
                }

                farmSelect.addEventListener('change', filterGroups);
                filterGroups();
            });
        </script>
    @endpush
@endonce
