@props([
    'activeType' => null,
    'baseRoute' => 'marketplace.learning',
    'routeParams' => [],
])

@php
    $query = request()->except('type', 'page');
@endphp

<nav class="learn-type-tabs" aria-label="Content type">
    <a
        href="{{ route($baseRoute, array_merge($routeParams, $query)) }}"
        class="learn-type-tabs__tab {{ empty($activeType) ? 'is-active' : '' }}"
    >All</a>
    @foreach (config('marketplace.learning.content_types', []) as $value => $label)
        <a
            href="{{ route($baseRoute, array_merge($routeParams, $query, ['type' => $value])) }}"
            class="learn-type-tabs__tab {{ ($activeType ?? '') === $value ? 'is-active' : '' }}"
        >{{ $label }}</a>
    @endforeach
</nav>
