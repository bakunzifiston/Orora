@php
    $eggCollection = $eggCollection ?? null;
    $selectedFarmId = $selectedFarmId ?? null;
    $selectedFlockId = $selectedFlockId ?? null;
@endphp

<div class="animal-registration" data-egg-collection-form>
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => __('Flock'),
        'description' => __('Which farm and flock this collection belongs to.'),
        'id' => 'section-egg-flock',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="farm_id">{{ __('Farm') }} <span class="dash-required">*</span></label>
                <select name="farm_id" id="farm_id" required data-egg-farm>
                    <option value="">{{ __('Select farm') }}</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" @selected(old('farm_id', $eggCollection?->farm_id ?? $selectedFarmId) == $farm->id)>{{ $farm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="flock_id">{{ __('Flock') }} <span class="dash-required">*</span></label>
                <select name="flock_id" id="flock_id" required data-egg-flock>
                    <option value="">{{ __('Select flock') }}</option>
                    @foreach ($flocks as $flock)
                        <option
                            value="{{ $flock->id }}"
                            data-farm-id="{{ $flock->farm_id }}"
                            @selected(old('flock_id', $eggCollection?->flock_id ?? $selectedFlockId) == $flock->id)
                        >
                            {{ $flock->name }} ({{ number_format($flock->current_count) }} birds)
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="collected_on">{{ __('Date') }} <span class="dash-required">*</span></label>
                <input type="date" name="collected_on" id="collected_on" value="{{ old('collected_on', $eggCollection?->collected_on?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="shift">{{ __('Shift') }}</label>
                <select name="shift" id="shift">
                    <option value="">{{ __('All day') }}</option>
                    @foreach (config('modules.egg_collection_shifts') as $key => $label)
                        <option value="{{ $key }}" @selected(old('shift', $eggCollection?->shift) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '2',
        'title' => __('Collection'),
        'description' => __('Eggs collected, cracked count, and optional weight.'),
        'id' => 'section-egg-counts',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="eggs_count">{{ __('Eggs collected') }} <span class="dash-required">*</span></label>
                <input type="number" name="eggs_count" id="eggs_count" min="0" value="{{ old('eggs_count', $eggCollection?->eggs_count) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="cracked_count">{{ __('Cracked') }}</label>
                <input type="number" name="cracked_count" id="cracked_count" min="0" value="{{ old('cracked_count', $eggCollection?->cracked_count ?? 0) }}">
            </div>
            <div class="dash-form-field">
                <label for="weight_kg">{{ __('Weight (kg)') }}</label>
                <input type="number" step="0.01" name="weight_kg" id="weight_kg" min="0" value="{{ old('weight_kg', $eggCollection?->weight_kg) }}">
            </div>
            <div class="dash-form-field">
                <label for="collected_by">{{ __('Collected by') }}</label>
                <input type="text" name="collected_by" id="collected_by" value="{{ old('collected_by', $eggCollection?->collected_by) }}">
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="notes">{{ __('Notes') }}</label>
                <textarea name="notes" id="notes" rows="3">{{ old('notes', $eggCollection?->notes) }}</textarea>
            </div>
        </div>
    @endcomponent
</div>

@once
    @push('scripts')
        <script>
            document.querySelectorAll('[data-egg-collection-form]').forEach((root) => {
                const farmSelect = root.querySelector('[data-egg-farm]');
                const flockSelect = root.querySelector('[data-egg-flock]');

                if (! farmSelect || ! flockSelect) {
                    return;
                }

                function filterFlocks() {
                    const farmId = farmSelect.value;
                    let hasVisibleSelection = false;

                    Array.from(flockSelect.options).forEach((option) => {
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
                        flockSelect.value = '';
                    }
                }

                farmSelect.addEventListener('change', filterFlocks);
                filterFlocks();
            });
        </script>
    @endpush
@endonce
