@php
    $query = ! empty($section) ? ['section' => $section] : [];
@endphp

<div class="dash-table-actions">
    <a href="{{ route($editRoute, $model) }}{{ $query ? '?'.http_build_query($query) : '' }}">Edit</a>
    <form method="POST" action="{{ route($destroyRoute, $model) }}{{ $query ? '?'.http_build_query($query) : '' }}" onsubmit="return confirm('Delete this record?');">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form>
</div>
