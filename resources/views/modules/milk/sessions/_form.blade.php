@php $milkSession = $milkSession ?? null; @endphp

@component('modules.farms._form-section', [
    'number' => '1',
    'title' => 'Session details',
    'description' => 'Farm, herd, date, and shift for this milking event.',
])
    <div class="dash-form-grid">
        <div class="dash-form-field">
            <label for="farm_id">Farm <span class="dash-required">*</span></label>
            <select name="farm_id" id="farm_id" required @disabled($milkSession && ! $milkSession->isOpen())>
                <option value="">Select farm</option>
                @foreach ($farms as $farm)
                    <option value="{{ $farm->id }}" @selected(old('farm_id', $milkSession?->farm_id) == $farm->id)>{{ $farm->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="dash-form-field">
            <label for="livestock_id">Herd / group <span class="dash-required">*</span></label>
            <select name="livestock_id" id="livestock_id" required @disabled($milkSession && ! $milkSession->isOpen())>
                <option value="">Select herd</option>
                @foreach ($livestockGroups as $group)
                    <option value="{{ $group->id }}" @selected(old('livestock_id', $milkSession?->livestock_id) == $group->id)>{{ $group->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="dash-form-field">
            <label for="session_date">Date <span class="dash-required">*</span></label>
            <input type="date" name="session_date" id="session_date" max="{{ now()->format('Y-m-d') }}" value="{{ old('session_date', $milkSession?->session_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required @disabled($milkSession && ! $milkSession->isOpen())>
        </div>
        <div class="dash-form-field">
            <label for="session_shift">Shift <span class="dash-required">*</span></label>
            <select name="session_shift" id="session_shift" required @disabled($milkSession && ! $milkSession->isOpen())>
                @foreach (config('modules.milk_session_shifts') as $shift)
                    <option value="{{ $shift }}" @selected(old('session_shift', $milkSession?->session_shift ?? 'morning') === $shift)>
                        {{ config('modules.milk_session_shift_labels')[$shift] ?? ucfirst($shift) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="dash-form-field">
            <label for="milked_by">Milked by <span class="dash-required">*</span></label>
            <input type="text" name="milked_by" id="milked_by" value="{{ old('milked_by', $milkSession?->milked_by ?? auth()->user()->name) }}" required @disabled($milkSession && ! $milkSession->isOpen())>
        </div>
        <div class="dash-form-field">
            <label for="milking_method">Method <span class="dash-required">*</span></label>
            <select name="milking_method" id="milking_method" required @disabled($milkSession && ! $milkSession->isOpen())>
                @foreach (config('modules.milking_methods') as $method)
                    <option value="{{ $method }}" @selected(old('milking_method', $milkSession?->milking_method ?? 'manual') === $method)>
                        {{ config('modules.milking_method_labels')[$method] ?? ucfirst($method) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="dash-form-field dash-form-field--full">
            <label for="destination_storage_id">Destination tank (on complete)</label>
            <select name="destination_storage_id" id="destination_storage_id" @disabled($milkSession && ! $milkSession->isOpen())>
                <option value="">Select later</option>
                @foreach ($storageTanks as $tank)
                    <option value="{{ $tank->id }}" @selected(old('destination_storage_id', $milkSession?->destination_storage_id) == $tank->id)>
                        {{ $tank->container_name }} ({{ $tank->current_quantity_liters }}/{{ $tank->capacity_liters }} L)
                    </option>
                @endforeach
            </select>
        </div>
        <div class="dash-form-field dash-form-field--full">
            <label for="notes">Notes</label>
            <textarea name="notes" id="notes" rows="2" @disabled($milkSession && ! $milkSession->isOpen())>{{ old('notes', $milkSession?->notes) }}</textarea>
        </div>
    </div>
@endcomponent
