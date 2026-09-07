@php
    $record = $record ?? null;
    $animals = $animals ?? collect();
    $flocks = $flocks ?? collect();
    $required = $required ?? true;
    $showAnimals = $showAnimals ?? $animals->isNotEmpty() || $flocks->isEmpty();
    $showFlocks = $showFlocks ?? $flocks->isNotEmpty();
    $selectedAnimalId = old('animal_id', $record?->animal_id);
    $selectedFlockId = old('flock_id', $record?->flock_id);
    $animalRequired = $required && $showAnimals && ! $showFlocks;
    $flockRequired = $required && $showFlocks && ! $showAnimals;
    $fullWidth = $fullWidth ?? true;
    $animalFarmAttr = $animalFarmAttr ?? false;
@endphp

@if ($showAnimals)
    <div class="dash-form-field @if ($fullWidth && ! $showFlocks) dash-form-field--full @endif">
        <label for="animal_id">{{ __('Animal') }} @if ($animalRequired || $required)<span class="dash-required">*</span>@endif</label>
        <select name="animal_id" id="animal_id" @if ($animalRequired) required @endif data-stock-animal>
            <option value="">{{ $showFlocks ? __('None — use a flock') : __('Select animal') }}</option>
            @foreach ($animals as $animal)
                <option
                    value="{{ $animal->id }}"
                    data-farm-id="{{ $animal->farm_id }}"
                    @if ($animalFarmAttr)
                        data-health-status="{{ $animal->health_status }}"
                    @endif
                    @selected((string) $selectedAnimalId === (string) $animal->id)
                >{{ $animal->tag_number }}@if($animal->name) — {{ $animal->name }}@endif @if($animal->farm) ({{ $animal->farm->name }}) @endif</option>
            @endforeach
        </select>
        @if ($showFlocks)
            <p class="dash-field-hint">{{ __('Choose an animal or a flock, not both.') }}</p>
        @endif
    </div>
@else
    <input type="hidden" name="animal_id" value="">
@endif

@if ($showFlocks)
    <div class="dash-form-field @if ($fullWidth && ! $showAnimals) dash-form-field--full @endif">
        <label for="flock_id">{{ __('Flock') }} @if ($flockRequired || $required)<span class="dash-required">*</span>@endif</label>
        <select name="flock_id" id="flock_id" @if ($flockRequired) required @endif data-stock-flock>
            <option value="">{{ $showAnimals ? __('None — use an animal') : __('Select flock') }}</option>
            @foreach ($flocks as $flock)
                <option
                    value="{{ $flock->id }}"
                    data-farm-id="{{ $flock->farm_id }}"
                    @selected((string) $selectedFlockId === (string) $flock->id)
                >{{ $flock->label() }} ({{ $flock->farm?->name }} · {{ number_format($flock->current_count) }} {{ __('birds') }})</option>
            @endforeach
        </select>
    </div>
@else
    <input type="hidden" name="flock_id" value="">
@endif
