@php
    $statusClass = match ($farm->status) {
        'active' => 'dash-farm-card__badge--active',
        'pending' => 'dash-farm-card__badge--pending',
        'suspended' => 'dash-farm-card__badge--suspended',
        default => 'dash-farm-card__badge--inactive',
    };
    $ownershipLabel = config('modules.ownership_types')[$farm->ownership_type] ?? ucfirst(str_replace('_', ' ', (string) $farm->ownership_type));
    $speciesLabel = ucfirst((string) ($farm->primary_species ?: 'cattle'));
    $initials = collect(preg_split('/\s+/', trim($farm->name)) ?: [])
        ->filter()
        ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
        ->take(2)
        ->join('') ?: 'F';
    $location = collect([$farm->district, $farm->province])->filter()->implode(', ');
    $size = $farm->farm_size_hectares !== null
        ? number_format((float) $farm->farm_size_hectares, 1).' ha'
        : null;
    $stockLabel = $farm->isPoultry() ? __('Flocks') : __('Animals');
    $stockCount = $farm->isPoultry() ? ($farm->flocks_count ?? 0) : ($farm->animals_count ?? 0);
    $metaBits = collect([$location, $speciesLabel, $size])->filter()->values();
@endphp

<article class="dash-farm-card dash-farm-card--clean">
    <div class="dash-farm-card__body">
        <div class="dash-farm-card__top">
            <div class="dash-farm-card__identity">
                <div class="dash-farm-card__avatar" aria-hidden="true">{{ $initials }}</div>
                <div class="dash-farm-card__title-wrap">
                    <div class="dash-farm-card__title-row">
                        <h2 class="dash-farm-card__title">
                            <a href="{{ route('farms.show', $farm) }}">{{ $farm->name }}</a>
                        </h2>
                        <span class="dash-farm-card__badge {{ $statusClass }}">{{ ucfirst($farm->status) }}</span>
                    </div>
                    @if ($farm->registration_number)
                        <p class="dash-farm-card__code">{{ $farm->registration_number }}</p>
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
            @if ($farm->owner_full_name)
                <span class="dash-farm-card__chip">
                    <span class="dash-farm-card__chip-label">{{ __('Owner') }}</span>
                    <span class="dash-farm-card__chip-value">{{ $farm->owner_full_name }}</span>
                </span>
            @endif
            @if ($ownershipLabel)
                <span class="dash-farm-card__chip">
                    <span class="dash-farm-card__chip-label">{{ __('Ownership') }}</span>
                    <span class="dash-farm-card__chip-value">{{ $ownershipLabel }}</span>
                </span>
            @endif
        </div>

        <div class="dash-farm-card__stats">
            <div class="dash-farm-card__stat">
                <span class="dash-farm-card__stat-value">{{ number_format($farm->livestock_count) }}</span>
                <span class="dash-farm-card__stat-label">{{ __('Groups') }}</span>
            </div>
            <div class="dash-farm-card__stat">
                <span class="dash-farm-card__stat-value">{{ number_format($stockCount) }}</span>
                <span class="dash-farm-card__stat-label">{{ $stockLabel }}</span>
            </div>
        </div>

        <div class="dash-farm-card__footer">
            <a href="{{ route('farms.show', $farm) }}" class="dash-btn-save dash-btn-save--sm">{{ __('View') }}</a>
            <a href="{{ route('farms.edit', $farm) }}" class="dash-btn-cancel dash-farm-card__btn">{{ __('Edit') }}</a>
            <form method="POST" action="{{ route('farms.destroy', $farm) }}" onsubmit="return confirm(@js(__('Delete this farm?')));">
                @csrf
                @method('DELETE')
                <button type="submit" class="dash-btn-cancel dash-farm-card__btn dash-farm-card__btn--danger">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>
</article>
