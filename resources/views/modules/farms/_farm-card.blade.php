@php
    $statusClass = match ($farm->status) {
        'active' => 'dash-farm-card__badge--active',
        'pending' => 'dash-farm-card__badge--pending',
        'suspended' => 'dash-farm-card__badge--suspended',
        default => 'dash-farm-card__badge--inactive',
    };
    $ownershipLabel = config('modules.ownership_types')[$farm->ownership_type] ?? ucfirst(str_replace('_', ' ', (string) $farm->ownership_type));
@endphp

<article class="dash-farm-card">
    <div class="dash-farm-card__accent" aria-hidden="true"></div>

    <div class="dash-farm-card__body">
        <div class="dash-farm-card__header">
            <div class="dash-farm-card__title-wrap">
                <h2 class="dash-farm-card__title">
                    <a href="{{ route('farms.show', $farm) }}">{{ $farm->name }}</a>
                </h2>
                @if ($farm->registration_number)
                    <p class="dash-farm-card__code">{{ $farm->registration_number }}</p>
                @endif
            </div>
            <span class="dash-farm-card__badge {{ $statusClass }}">{{ ucfirst($farm->status) }}</span>
        </div>

        <dl class="dash-farm-card__meta">
            <div class="dash-farm-card__meta-row">
                <dt>{{ __('Location') }}</dt>
                <dd>{{ $farm->district ?: $farm->province ?: '—' }}{{ $farm->village ? ', '.$farm->village : '' }}</dd>
            </div>
            <div class="dash-farm-card__meta-row">
                <dt>{{ __('Size') }}</dt>
                <dd>{{ $farm->farm_size_hectares !== null ? number_format($farm->farm_size_hectares, 2).' ha' : '—' }}</dd>
            </div>
            <div class="dash-farm-card__meta-row">
                <dt>{{ __('Owner') }}</dt>
                <dd>{{ $farm->owner_full_name ?: '—' }}</dd>
            </div>
            <div class="dash-farm-card__meta-row">
                <dt>{{ __('Ownership') }}</dt>
                <dd>{{ $ownershipLabel ?: '—' }}</dd>
            </div>
        </dl>

        <div class="dash-farm-card__stats">
            <div class="dash-farm-card__stat">
                <span class="dash-farm-card__stat-value">{{ number_format($farm->livestock_count) }}</span>
                <span class="dash-farm-card__stat-label">{{ __('Livestock groups') }}</span>
            </div>
            <div class="dash-farm-card__stat">
                <span class="dash-farm-card__stat-value">{{ number_format($farm->animals_count) }}</span>
                <span class="dash-farm-card__stat-label">{{ __('Animals') }}</span>
            </div>
        </div>

        <div class="dash-farm-card__footer">
            <a href="{{ route('farms.show', $farm) }}" class="dash-farm-card__action dash-farm-card__action--primary">{{ __('View') }}</a>
            <a href="{{ route('farms.edit', $farm) }}" class="dash-farm-card__action">{{ __('Edit') }}</a>
            <form method="POST" action="{{ route('farms.destroy', $farm) }}" onsubmit="return confirm(@js(__('Delete this farm?')));">
                @csrf
                @method('DELETE')
                <button type="submit" class="dash-farm-card__action dash-farm-card__action--danger">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>
</article>
