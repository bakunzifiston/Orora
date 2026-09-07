@php
    $typeLabel = config('modules.flock_production_types.'.$flock->production_type, $flock->production_type);
    $badge = $flock->isActive() ? 'dash-entity-card__badge--active' : 'dash-entity-card__badge--inactive';
    $logsEggs = in_array($flock->production_type, ['layer', 'pullet', 'breeder'], true);
@endphp

<article class="dash-entity-card">
    <div class="dash-entity-card__accent" aria-hidden="true"></div>
    <div class="dash-entity-card__body">
        <div class="dash-entity-card__header">
            <div class="dash-entity-card__title-wrap">
                <h2 class="dash-entity-card__title">
                    <a href="{{ route('flocks.show', $flock) }}">{{ $flock->name }}</a>
                </h2>
                <p class="dash-entity-card__code">{{ $flock->flock_code }}</p>
            </div>
            <span class="dash-entity-card__badge {{ $badge }}">{{ $flock->lifecycle_status }}</span>
        </div>

        <dl class="dash-entity-card__meta">
            <div class="dash-entity-card__meta-row">
                <dt>{{ __('Farm') }}</dt>
                <dd>
                    @if ($flock->farm)
                        <a href="{{ route('farms.show', $flock->farm) }}" style="color: inherit; text-decoration: none; font-weight: 600;">
                            {{ $flock->farm->name }}
                        </a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div class="dash-entity-card__meta-row">
                <dt>{{ __('Type') }}</dt>
                <dd>{{ $typeLabel }}</dd>
            </div>
            <div class="dash-entity-card__meta-row">
                <dt>{{ __('House / group') }}</dt>
                <dd>
                    @if ($flock->livestock)
                        <a href="{{ route('livestock.show', $flock->livestock) }}" style="color: inherit; text-decoration: none; font-weight: 600;">
                            {{ $flock->livestock->name }}
                        </a>
                    @else
                        {{ $flock->house_or_pen ?: '—' }}
                    @endif
                </dd>
            </div>
            <div class="dash-entity-card__meta-row">
                <dt>{{ __('Placed') }}</dt>
                <dd>{{ $flock->placed_on?->format('M j, Y') ?: '—' }}</dd>
            </div>
        </dl>

        <div class="dash-entity-card__stats">
            <div class="dash-entity-card__stat">
                <span class="dash-entity-card__stat-value">{{ number_format($flock->current_count) }}</span>
                <span class="dash-entity-card__stat-label">{{ __('Birds on hand') }}</span>
            </div>
            <div class="dash-entity-card__stat">
                <span class="dash-entity-card__stat-value">{{ number_format($flock->placed_count) }}</span>
                <span class="dash-entity-card__stat-label">{{ __('Placed') }}</span>
            </div>
            <div class="dash-entity-card__stat">
                <span class="dash-entity-card__stat-value">{{ $flock->mortalityPercent() }}%</span>
                <span class="dash-entity-card__stat-label">{{ __('Mortality') }}</span>
            </div>
        </div>

        <div class="dash-entity-card__footer">
            <a href="{{ route('flocks.show', $flock) }}" class="dash-entity-card__action dash-entity-card__action--primary">{{ __('View') }}</a>
            <a href="{{ route('flocks.edit', $flock) }}" class="dash-entity-card__action">{{ __('Edit') }}</a>
            @if ($logsEggs)
                <a href="{{ route('eggs.collections.create', ['farm_id' => $flock->farm_id, 'flock_id' => $flock->id]) }}" class="dash-entity-card__action">{{ __('Log eggs') }}</a>
            @endif
            <form method="POST" action="{{ route('flocks.destroy', $flock) }}" onsubmit="return confirm(@js(__('Remove this flock? This cannot be undone.')));">
                @csrf
                @method('DELETE')
                <button type="submit" class="dash-entity-card__action dash-entity-card__action--danger">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>
</article>
