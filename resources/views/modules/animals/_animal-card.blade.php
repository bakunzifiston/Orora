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

<article class="dash-entity-card dash-entity-card--animal">
    <div class="dash-entity-card__accent" aria-hidden="true"></div>

    <div class="dash-entity-card__body">
        <div class="dash-entity-card__header dash-entity-card__header--media">
            @if ($animal->photo_url)
                <img src="{{ $animal->photo_url }}" alt="" class="dash-entity-card__avatar dash-entity-card__avatar--photo">
            @else
                <div class="dash-entity-card__avatar" aria-hidden="true">{{ $initials }}</div>
            @endif
            <div class="dash-entity-card__title-wrap">
                <h2 class="dash-entity-card__title">
                    <a href="{{ route('animals.show', $animal) }}">{{ $animal->tag_number }}</a>
                </h2>
                <p class="dash-entity-card__code">{{ $animal->name }}</p>
            </div>
            <span class="dash-entity-card__badge {{ $healthBadge }}">{{ $animal->health_status }}</span>
        </div>

        <dl class="dash-entity-card__meta">
            <div class="dash-entity-card__meta-row">
                <dt>{{ __('Farm') }}</dt>
                <dd>
                    <a href="{{ route('farms.show', $animal->farm) }}" style="color: inherit; text-decoration: none; font-weight: 600;">
                        {{ $animal->farm->name }}
                    </a>
                </dd>
            </div>
            <div class="dash-entity-card__meta-row">
                <dt>{{ __('Group') }}</dt>
                <dd>
                    @if ($animal->livestock)
                        <a href="{{ route('livestock.show', $animal->livestock) }}" style="color: inherit; text-decoration: none; font-weight: 600;">
                            {{ $animal->livestock->name }}
                        </a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div class="dash-entity-card__meta-row">
                <dt>{{ __('Gender') }}</dt>
                <dd>{{ $animal->gender_label }}</dd>
            </div>
            <div class="dash-entity-card__meta-row">
                <dt>{{ __('Lifecycle') }}</dt>
                <dd>{{ $animal->lifecycle_status }}</dd>
            </div>
        </dl>

        <div class="dash-entity-card__stats">
            <div class="dash-entity-card__stat">
                <span class="dash-entity-card__stat-value">{{ $animal->production_status ?: '—' }}</span>
                <span class="dash-entity-card__stat-label">{{ __('Production') }}</span>
            </div>
            <div class="dash-entity-card__stat">
                <span class="dash-entity-card__stat-value">{{ $animal->age_label ?: '—' }}</span>
                <span class="dash-entity-card__stat-label">{{ __('Age') }}</span>
            </div>
        </div>

        <div class="dash-entity-card__footer">
            <a href="{{ route('animals.show', $animal) }}" class="dash-entity-card__action dash-entity-card__action--primary">{{ __('View') }}</a>
            <a href="{{ route('animals.edit', $animal) }}" class="dash-entity-card__action">{{ __('Edit') }}</a>
            <form method="POST" action="{{ route('animals.destroy', $animal) }}" onsubmit="return confirm(@js(__('Delete this animal?')));">
                @csrf
                @method('DELETE')
                <button type="submit" class="dash-entity-card__action dash-entity-card__action--danger">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>
</article>
