@php
    $statusClass = $group->status === 'active'
        ? 'dash-entity-card__badge--active'
        : 'dash-entity-card__badge--inactive';
@endphp

<article class="dash-entity-card">
    <div class="dash-entity-card__accent" aria-hidden="true"></div>

    <div class="dash-entity-card__body">
        <div class="dash-entity-card__header">
            <div class="dash-entity-card__title-wrap">
                <h2 class="dash-entity-card__title">
                    <a href="{{ route('livestock.show', $group) }}">{{ $group->name }}</a>
                </h2>
                @if ($group->herd_groups_label && $group->herd_groups_label !== '—')
                    <p class="dash-entity-card__code">{{ $group->herd_groups_label }}</p>
                @endif
            </div>
            <span class="dash-entity-card__badge {{ $statusClass }}">{{ ucfirst($group->status) }}</span>
        </div>

        <dl class="dash-entity-card__meta">
            <div class="dash-entity-card__meta-row">
                <dt>Farm</dt>
                <dd>
                    <a href="{{ route('farms.show', $group->farm) }}" style="color: inherit; text-decoration: none; font-weight: 600;">
                        {{ $group->farm->name }}
                    </a>
                </dd>
            </div>
            <div class="dash-entity-card__meta-row">
                <dt>Types</dt>
                <dd title="{{ $group->livestock_types_label }}">{{ $group->livestock_types_label }}</dd>
            </div>
            <div class="dash-entity-card__meta-row">
                <dt>Production</dt>
                <dd title="{{ $group->production_purposes_label }}">{{ $group->production_purposes_label }}</dd>
            </div>
            <div class="dash-entity-card__meta-row">
                <dt>Breed</dt>
                <dd>{{ $group->breed ?: '—' }}</dd>
            </div>
        </dl>

        <div class="dash-entity-card__stats">
            <div class="dash-entity-card__stat">
                <span class="dash-entity-card__stat-value">{{ number_format($group->head_count) }}</span>
                <span class="dash-entity-card__stat-label">Head count</span>
            </div>
            <div class="dash-entity-card__stat">
                <span class="dash-entity-card__stat-value">{{ number_format($group->animals_count) }}</span>
                <span class="dash-entity-card__stat-label">Animals registered</span>
            </div>
        </div>

        <div class="dash-entity-card__footer">
            <a href="{{ route('livestock.show', $group) }}" class="dash-entity-card__action dash-entity-card__action--primary">View</a>
            <a href="{{ route('livestock.edit', $group) }}" class="dash-entity-card__action">Edit</a>
            <form method="POST" action="{{ route('livestock.destroy', $group) }}" onsubmit="return confirm('Delete this livestock group?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="dash-entity-card__action dash-entity-card__action--danger">Delete</button>
            </form>
        </div>
    </div>
</article>
