@php
    $typeLabel = config('modules.flock_production_types.'.$flock->production_type, $flock->production_type);
    $statusClass = $flock->isActive()
        ? 'dash-farm-card__badge--active'
        : 'dash-farm-card__badge--inactive';
    $initials = collect(preg_split('/\s+/', trim((string) $flock->name)) ?: [])
        ->filter()
        ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
        ->take(2)
        ->join('') ?: strtoupper(substr((string) $flock->flock_code, 0, 2));
    $logsEggs = in_array($flock->production_type, ['layer', 'pullet', 'breeder'], true);
    $metaBits = collect([
        $flock->farm?->name,
        $typeLabel,
        $flock->livestock?->name ?: $flock->house_or_pen,
    ])->filter()->values();
@endphp

<article class="dash-farm-card dash-farm-card--clean">
    <div class="dash-farm-card__body">
        <div class="dash-farm-card__top">
            <div class="dash-farm-card__identity">
                <div class="dash-farm-card__avatar" aria-hidden="true">{{ $initials }}</div>
                <div class="dash-farm-card__title-wrap">
                    <div class="dash-farm-card__title-row">
                        <h2 class="dash-farm-card__title">
                            <a href="{{ route('flocks.show', $flock) }}">{{ $flock->name }}</a>
                        </h2>
                        <span class="dash-farm-card__badge {{ $statusClass }}">{{ $flock->lifecycle_status }}</span>
                    </div>
                    <p class="dash-farm-card__code">{{ $flock->flock_code }}</p>
                    @if ($metaBits->isNotEmpty())
                        <p class="dash-farm-card__summary">{{ $metaBits->implode(' · ') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="dash-farm-card__chips">
            @if ($flock->farm)
                <a href="{{ route('farms.show', $flock->farm) }}" class="dash-farm-card__chip dash-farm-card__chip--link">
                    <span class="dash-farm-card__chip-label">{{ __('Farm') }}</span>
                    <span class="dash-farm-card__chip-value">{{ $flock->farm->name }}</span>
                </a>
            @endif
            <span class="dash-farm-card__chip">
                <span class="dash-farm-card__chip-label">{{ __('Type') }}</span>
                <span class="dash-farm-card__chip-value">{{ $typeLabel }}</span>
            </span>
            @if ($flock->placed_on)
                <span class="dash-farm-card__chip">
                    <span class="dash-farm-card__chip-label">{{ __('Placed') }}</span>
                    <span class="dash-farm-card__chip-value">{{ $flock->placed_on->format('M j, Y') }}</span>
                </span>
            @endif
        </div>

        <div class="dash-farm-card__stats">
            <div class="dash-farm-card__stat">
                <span class="dash-farm-card__stat-value">{{ number_format($flock->current_count) }}</span>
                <span class="dash-farm-card__stat-label">{{ __('On hand') }}</span>
            </div>
            <div class="dash-farm-card__stat">
                <span class="dash-farm-card__stat-value">{{ number_format($flock->placed_count) }}</span>
                <span class="dash-farm-card__stat-label">{{ __('Placed') }}</span>
            </div>
            <div class="dash-farm-card__stat">
                <span class="dash-farm-card__stat-value">{{ $flock->mortalityPercent() }}%</span>
                <span class="dash-farm-card__stat-label">{{ __('Mortality') }}</span>
            </div>
        </div>

        <div class="dash-farm-card__footer">
            <a href="{{ route('flocks.show', $flock) }}" class="dash-btn-save dash-btn-save--sm">{{ __('View') }}</a>
            <a href="{{ route('flocks.edit', $flock) }}" class="dash-farm-card__btn">{{ __('Edit') }}</a>
            @if ($logsEggs)
                <a href="{{ route('eggs.collections.create', ['farm_id' => $flock->farm_id, 'flock_id' => $flock->id]) }}" class="dash-farm-card__btn">{{ __('Log eggs') }}</a>
            @endif
            <form method="POST" action="{{ route('flocks.destroy', $flock) }}" onsubmit="return confirm(@js(__('Remove this flock? This cannot be undone.')));">
                @csrf
                @method('DELETE')
                <button type="submit" class="dash-farm-card__btn dash-farm-card__btn--danger">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>
</article>
