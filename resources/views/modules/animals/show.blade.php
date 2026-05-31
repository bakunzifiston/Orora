@extends('layouts.dashboard')

@section('title', $animal->tag_number)

@section('content')
    @php
        $healthBadge = match ($animal->health_status) {
            'Healthy' => 'dash-entity-card__badge--active',
            'Pregnant' => 'dash-entity-card__badge--pending',
            'Sick', 'Under treatment', 'Quarantined' => 'dash-entity-card__badge--suspended',
            default => 'dash-entity-card__badge--inactive',
        };
        $initials = collect(preg_split('/\s+/', trim($animal->name)) ?: [])
            ->filter()
            ->map(fn ($w) => strtoupper(substr($w, 0, 1)))
            ->take(2)
            ->join('') ?: strtoupper(substr($animal->tag_number, 0, 2));
    @endphp

    @include('modules.partials.header', [
        'title' => $animal->tag_number,
        'subtitle' => $animal->name,
        'backRoute' => 'animals.index',
    ])
    @include('modules.partials.flash')

    <div style="margin: -0.75rem 0 1.25rem; display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
        <span class="dash-entity-card__badge {{ $healthBadge }}">{{ $animal->health_status }}</span>
        <span class="dash-entity-card__badge dash-entity-card__badge--inactive">{{ $animal->lifecycle_status }}</span>
        <a href="{{ route('animals.edit', $animal) }}" class="dash-btn-save">Edit animal</a>
    </div>

    <div class="dash-animal-show-hero dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-animal-show-hero__media">
            @if ($animal->photo_url)
                <img src="{{ $animal->photo_url }}" alt="" class="dash-animal-show-hero__photo">
            @else
                <div class="dash-animal-show-hero__photo dash-animal-show-hero__photo--placeholder">{{ $initials }}</div>
            @endif
        </div>
        <dl class="dash-entity-detail dash-animal-show-hero__details">
            @include('modules.farms._detail-row', ['label' => 'Tag number', 'value' => $animal->tag_number])
            @include('modules.farms._detail-row', ['label' => 'Name', 'value' => $animal->name])
            @include('modules.farms._detail-row', ['label' => 'Gender', 'value' => $animal->gender_label])
            @include('modules.farms._detail-row', ['label' => 'Farm', 'value' => $animal->farm->name])
            @include('modules.farms._detail-row', ['label' => 'Livestock group', 'value' => $animal->livestock?->name])
        </dl>
    </div>

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div class="dash-stat-label">Age</div>
            <div class="dash-stat-value">{{ $animal->age_label ?: '—' }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Weight</div>
            <div class="dash-stat-value accent">{{ $animal->weight_kg !== null ? number_format($animal->weight_kg, 1).' kg' : '—' }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Production</div>
            <div class="dash-stat-value" style="font-size: 1rem;">{{ $animal->production_status ?: '—' }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Condition</div>
            <div class="dash-stat-value" style="font-size: 1rem;">{{ $animal->current_condition ?: '—' }}</div>
        </div>
    </div>

    <div class="dash-health-grid">
        <div class="dash-panel">
            <div class="dash-panel-title">Physical profile</div>
            <dl class="dash-entity-detail">
                @include('modules.farms._detail-row', ['label' => 'Species', 'value' => $animal->species])
                @include('modules.farms._detail-row', ['label' => 'Breed', 'value' => $animal->breed])
                @include('modules.farms._detail-row', ['label' => 'Date of birth', 'value' => $animal->date_of_birth?->format('M j, Y')])
                @include('modules.farms._detail-row', ['label' => 'Color / markings', 'value' => $animal->color_markings])
            </dl>
        </div>

        <div class="dash-panel">
            <div class="dash-panel-title">Acquisition & lineage</div>
            <dl class="dash-entity-detail">
                @include('modules.farms._detail-row', ['label' => 'Acquisition', 'value' => $animal->acquisition_type])
                @include('modules.farms._detail-row', ['label' => 'Acquired on', 'value' => $animal->acquisition_date?->format('M j, Y')])
                @include('modules.farms._detail-row', ['label' => 'Source', 'value' => $animal->source])
                @include('modules.farms._detail-row', ['label' => 'Mother tag', 'value' => $animal->mother_tag])
                @include('modules.farms._detail-row', ['label' => 'Father tag', 'value' => $animal->father_tag])
            </dl>
        </div>
    </div>

    <div class="dash-panel" style="margin-top: 1rem;">
        <div class="dash-panel-title">Quick links</div>
        <p style="margin: 0; font-size: 0.875rem; display: flex; flex-wrap: wrap; gap: 0.75rem;">
            <a href="{{ route('farms.show', $animal->farm) }}">View farm</a>
            @if ($animal->livestock)
                <a href="{{ route('livestock.show', $animal->livestock) }}">View livestock group</a>
            @endif
        </p>
    </div>

    @if ($animal->notes)
        <div class="dash-panel" style="margin-top: 1rem;">
            <div class="dash-panel-title">Notes</div>
            <p style="margin: 0; font-size: 0.875rem; color: #374151; white-space: pre-wrap;">{{ $animal->notes }}</p>
        </div>
    @endif
@endsection
