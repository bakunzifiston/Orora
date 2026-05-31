@extends('layouts.sales-module')

@section('title', 'Sales — Dispatch to abattoir')

@section('sales-content')
    @include('modules.partials.header', [
        'title' => 'Dispatch to abattoir',
        'backRoute' => 'sales.abattoir',
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('sales.abattoir.store') }}" class="dash-farm-form" id="abattoir-dispatch-form">
        @csrf
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="farm_id">Farm <span class="dash-required">*</span></label>
                <select name="farm_id" id="farm_id" required>
                    <option value="">Select farm</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" @selected(old('farm_id') == $farm->id)>{{ $farm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="dispatch_date">Dispatch date <span class="dash-required">*</span></label>
                <input type="date" name="dispatch_date" id="dispatch_date" value="{{ old('dispatch_date', now()->format('Y-m-d')) }}" required>
            </div>
            <div class="dash-form-field">
                <label for="abattoir_name">Abattoir name <span class="dash-required">*</span></label>
                <input type="text" name="abattoir_name" id="abattoir_name" value="{{ old('abattoir_name') }}" required>
            </div>
            <div class="dash-form-field">
                <label for="abattoir_location">Location</label>
                <input type="text" name="abattoir_location" id="abattoir_location" value="{{ old('abattoir_location') }}">
            </div>
            <div class="dash-form-field">
                <label for="contact_person">Contact person</label>
                <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person') }}">
            </div>
            <div class="dash-form-field">
                <label for="transport_method">Transport</label>
                <input type="text" name="transport_method" id="transport_method" value="{{ old('transport_method') }}">
            </div>
            <div class="dash-form-field">
                <label for="vehicle_plate">Vehicle plate</label>
                <input type="text" name="vehicle_plate" id="vehicle_plate" value="{{ old('vehicle_plate') }}">
            </div>
            <div class="dash-form-field">
                <label for="driver_name">Driver</label>
                <input type="text" name="driver_name" id="driver_name" value="{{ old('driver_name') }}">
            </div>
            <div class="dash-form-field">
                <label for="expected_return_date">Expected return</label>
                <input type="date" name="expected_return_date" id="expected_return_date" value="{{ old('expected_return_date') }}">
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="dash-panel" style="margin: 1.25rem 0;">
            <div class="dash-panel-title">Select animals</div>
            <p class="dash-empty" id="animals-empty" style="display: none;">Select a farm to see active animals.</p>
            <div id="animals-list" class="dash-form-grid"></div>
        </div>

        <div class="dash-form-actions">
            <button type="submit" class="dash-btn-save">Dispatch animals</button>
            <a href="{{ route('sales.abattoir') }}" class="dash-btn-cancel">Cancel</a>
        </div>
    </form>

    @php
        $animalsJson = $animals->map(fn ($a) => [
            'id' => $a->id,
            'farm_id' => $a->farm_id,
            'tag' => $a->tag_number,
            'weight' => $a->weight_kg,
        ])->values();
    @endphp
    <script>
        (function () {
            const animals = @json($animalsJson);
            const farmSelect = document.getElementById('farm_id');
            const list = document.getElementById('animals-list');
            const empty = document.getElementById('animals-empty');

            function render() {
                const farmId = farmSelect.value;
                list.innerHTML = '';
                const filtered = animals.filter(a => String(a.farm_id) === String(farmId));
                empty.style.display = farmId && filtered.length === 0 ? 'block' : 'none';
                if (!farmId) {
                    empty.style.display = 'block';
                    empty.textContent = 'Select a farm to see active animals.';
                    return;
                }
                if (filtered.length === 0) {
                    empty.textContent = 'No active animals on this farm.';
                    return;
                }
                filtered.forEach((animal) => {
                    const wrap = document.createElement('label');
                    wrap.className = 'dash-checkbox';
                    wrap.style.display = 'block';
                    wrap.innerHTML = `
                        <input type="checkbox" name="animal_ids[]" value="${animal.id}">
                        <span>${animal.tag} (${animal.weight ?? '?'} kg)</span>
                        <input type="hidden" name="live_weights[${animal.id}]" value="${animal.weight ?? ''}">
                    `;
                    list.appendChild(wrap);
                });
            }

            farmSelect.addEventListener('change', render);
            render();
        })();
    </script>
@endsection
