@php
    $query = ! empty($section) ? ['section' => $section] : [];
    $queryString = $query ? '?'.http_build_query($query) : '';
    $showDelete = ! empty($destroyRoute) && ($canDelete ?? true);
@endphp

<div class="dash-table-actions">
    @if (! empty($showRoute))
        <a href="{{ route($showRoute, $model).$queryString }}">{{ $showLabel ?? 'View' }}</a>
    @endif
    @if (! empty($editRoute))
        <a href="{{ route($editRoute, $model).$queryString }}">{{ $editLabel ?? 'Edit' }}</a>
    @endif
    @if ($showDelete)
        <form method="POST" action="{{ route($destroyRoute, $model).$queryString }}" onsubmit="return confirm('{{ $deleteConfirm ?? 'Delete this record?' }}');">
            @csrf
            @method('DELETE')
            <button type="submit">{{ $deleteLabel ?? 'Delete' }}</button>
        </form>
    @endif
</div>
