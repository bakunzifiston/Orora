@php
    $statusClass = $group->status === 'active'
        ? 'dash-farm-card__badge--active'
        : 'dash-farm-card__badge--inactive';
    $initials = collect(preg_split('/\s+/', trim($group->name)) ?: [])
        ->filter()
        ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
        ->take(2)
        ->join('') ?: 'L';
    $herdLabel = ($group->herd_groups_label && $group->herd_groups_label !== '—')
        ? $group->herd_groups_label
        : null;
    $typesLabel = ($group->livestock_types_label && $group->livestock_types_label !== '—')
        ? $group->livestock_types_label
        : null;
    $productionLabel = ($group->production_purposes_label && $group->production_purposes_label !== '—')
        ? $group->production_purposes_label
        : null;
    $metaBits = collect([$group->farm?->name, $typesLabel, $group->breed])->filter()->values();
@endphp

<article class="dash-farm-card dash-farm-card--clean">
    <div class="dash-farm-card__body">
        <div class="dash-farm-card__top">
            <div class="dash-farm-card__identity">
                <div class="dash-farm-card__avatar" aria-hidden="true">{{ $initials }}</div>
                <div class="dash-farm-card__title-wrap">
                    <div class="dash-farm-card__title-row">
                        <h2 class="dash-farm-card__title">
                            <a href="{{ route('livestock.show', $group) }}">{{ $group->name }}</a>
                        </h2>
                        <span class="dash-farm-card__badge {{ $statusClass }}">{{ ucfirst($group->status) }}</span>
                    </div>
                    @if ($herdLabel)
                        <p class="dash-farm-card__code">{{ $herdLabel }}</p>
                    @endif
                    @if ($metaBits->isNotEmpty())
                        <p class="dash-farm-card__summary">
                            {{ $metaBits->implode(' · ') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="dash-farm-card__chips">
            @if ($group->farm)
                <a href="{{ route('farms.show', $group->farm) }}" class="dash-farm-card__chip dash-farm-card__chip--link">
                    <span class="dash-farm-card__chip-label">{{ __('Farm') }}</span>
                    <span class="dash-farm-card__chip-value">{{ $group->farm->name }}</span>
                </a>
            @endif
            @if ($productionLabel)
                <span class="dash-farm-card__chip">
                    <span class="dash-farm-card__chip-label">{{ __('Production') }}</span>
                    <span class="dash-farm-card__chip-value">{{ $productionLabel }}</span>
                </span>
            @endif
        </div>

        <div class="dash-farm-card__stats">
            <div class="dash-farm-card__stat">
                <span class="dash-farm-card__stat-value">{{ number_format($group->head_count) }}</span>
                <span class="dash-farm-card__stat-label">{{ __('Head count') }}</span>
            </div>
            <div class="dash-farm-card__stat">
                <span class="dash-farm-card__stat-value">{{ number_format($group->animals_count) }}</span>
                <span class="dash-farm-card__stat-label">{{ __('Animals') }}</span>
            </div>
        </div>

        <div class="dash-farm-card__footer">
            <a href="{{ route('livestock.show', $group) }}" class="dash-btn-save dash-btn-save--sm">{{ __('View') }}</a>
            <a href="{{ route('livestock.edit', $group) }}" class="dash-btn-cancel dash-farm-card__btn">{{ __('Edit') }}</a>
            <form method="POST" action="{{ route('livestock.destroy', $group) }}" onsubmit="return confirm(@js(__('Delete this livestock group?')));">
                @csrf
                @method('DELETE')
                <button type="submit" class="dash-btn-cancel dash-farm-card__btn dash-farm-card__btn--danger">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>
</article>
