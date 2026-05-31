@extends('layouts.breeding-module')

@section('title', 'Breeding — '.$birthRecord->birth_code)

@section('breeding-content')
    @include('modules.partials.header', [
        'title' => $birthRecord->birth_code,
        'subtitle' => $birthRecord->motherAnimal->tag_number.' · '.$birthRecord->birth_date->format('M j, Y'),
        'backRoute' => 'breeding.births',
    ])
    @include('modules.partials.flash')

    @if ($errors->has('offspring') || $errors->has('register'))
        <div class="dash-flash dash-flash--error" style="margin-bottom: 1rem;">
            {{ $errors->first('offspring') ?: $errors->first('register') }}
        </div>
    @endif

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div class="dash-stat-label">Birth type</div>
            <div class="dash-stat-value">{{ ucfirst($birthRecord->birth_type) }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Alive</div>
            <div class="dash-stat-value accent">{{ $birthRecord->alive_offspring }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Stillborn</div>
            <div class="dash-stat-value">{{ $birthRecord->stillborn_offspring }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Mother condition</div>
            <div class="dash-stat-value">{{ config('modules.mother_condition_after_labels')[$birthRecord->mother_condition_after] ?? $birthRecord->mother_condition_after }}</div>
        </div>
    </div>

    <div class="dash-panel">
        <div class="dash-panel-title">Offspring</div>
        <p style="font-size: 0.8125rem; color: #6b7280; margin-bottom: 1rem;">
            Update details for each offspring, then register them as animals in your herd.
        </p>

        @if ($birthRecord->offspring->isEmpty())
            <p class="dash-empty">No offspring rows — adjust alive count on birth record if needed.</p>
        @else
            @foreach ($birthRecord->offspring as $offspring)
                <div class="dash-panel" style="margin-bottom: 1rem; background: #fafafa;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <strong>{{ $offspring->offspring_code }}</strong>
                        @if ($offspring->is_registered)
                            <span style="font-size: 0.8125rem; color: #059669;">
                                Registered — <a href="{{ route('animals.edit', $offspring->animal) }}">{{ $offspring->animal->tag_number }}</a>
                            </span>
                        @endif
                    </div>

                    @if (! $offspring->is_registered)
                        <form method="POST" action="{{ route('breeding.births.offspring.update', [$birthRecord, $offspring]) }}" class="dash-form-grid" style="margin-bottom: 1rem;">
                            @csrf
                            @method('PUT')
                            <div class="dash-form-field">
                                <label>Gender</label>
                                <select name="gender" required>
                                    @foreach (config('modules.animal_genders') as $value => $label)
                                        <option value="{{ $value }}" @selected($offspring->gender === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="dash-form-field">
                                <label>Birth weight (kg)</label>
                                <input type="number" step="0.01" name="birth_weight_kg" value="{{ $offspring->birth_weight_kg }}">
                            </div>
                            <div class="dash-form-field">
                                <label>Health at birth</label>
                                <select name="health_status_at_birth" required>
                                    @foreach (config('modules.offspring_health_at_birth') as $status)
                                        <option value="{{ $status }}" @selected($offspring->health_status_at_birth === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="dash-form-field">
                                <label>Color / markings</label>
                                <input type="text" name="color_markings" value="{{ $offspring->color_markings }}">
                            </div>
                            <div class="dash-form-field dash-form-field--full">
                                <label>Notes</label>
                                <textarea name="notes" rows="2">{{ $offspring->notes }}</textarea>
                            </div>
                            <div class="dash-form-field">
                                <button type="submit" class="dash-btn-save">Save details</button>
                            </div>
                        </form>

                        @if ($offspring->health_status_at_birth !== 'stillborn')
                            <form method="POST" action="{{ route('breeding.births.offspring.register', [$birthRecord, $offspring]) }}" class="dash-form-grid" style="border-top: 1px solid #e5e7eb; padding-top: 1rem;">
                                @csrf
                                <div class="dash-form-field">
                                    <label>Tag number <span class="dash-required">*</span></label>
                                    <input type="text" name="tag_number" required placeholder="e.g. CALF-001">
                                </div>
                                <div class="dash-form-field">
                                    <label>Name <span class="dash-required">*</span></label>
                                    <input type="text" name="name" required placeholder="Animal name">
                                </div>
                                <div class="dash-form-field">
                                    <label>Breed</label>
                                    <input type="text" name="breed" value="{{ $birthRecord->motherAnimal->breed }}">
                                </div>
                                <div class="dash-form-field">
                                    <button type="submit" class="dash-btn-save">Register as animal</button>
                                </div>
                            </form>
                        @endif
                    @endif
                </div>
            @endforeach
        @endif
    </div>
@endsection
