@props([
    'categories',
    'activeCategory' => null,
])

@php
    $icons = [
        'all' => '<path d="M4 5h6v6H4zM14 5h6v6h-6zM4 15h6v6H4zM14 15h6v6h-6z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>',
        'live-animals' => '<path d="M5 9c0-2 1.5-3 3-3h8c1.5 0 3 1 3 3v3a5 5 0 01-5 5h-4a5 5 0 01-5-5V9z" stroke="currentColor" stroke-width="1.8"/><path d="M5 9L3 6M19 9l2-3M10 13h.01M14 13h.01M10 17v3M14 17v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
        'meat-products' => '<path d="M14.5 4.5a6 6 0 00-8.5 8.5l2 2a3 3 0 004 0l4.5-4.5a4.2 4.2 0 00-2-6z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M8 15l-3.5 3.5a1.5 1.5 0 102.1 2.1L10 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
        'milk-dairy' => '<path d="M9 3h6v3l2 3v11a1 1 0 01-1 1H8a1 1 0 01-1-1V9l2-3V3z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M7 12h10" stroke="currentColor" stroke-width="1.8"/>',
        'feed-supplies' => '<path d="M6 8l1.5-4h9L18 8v11a2 2 0 01-2 2H8a2 2 0 01-2-2V8z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M6 8h12M12 12v5M10 14.5h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
    ];
    $fallback = '<rect x="4" y="4" width="16" height="16" rx="3" stroke="currentColor" stroke-width="1.8"/>';
@endphp

<nav class="shop-tiles" aria-label="Product categories">
    <a
        href="{{ route('marketplace.shop', request()->except('category', 'page')) }}"
        class="shop-tiles__tile {{ empty($activeCategory) ? 'is-active' : '' }}"
        @if (empty($activeCategory)) aria-current="page" @endif
        data-shop-nav
    >
        <span class="shop-tiles__icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">{!! $icons['all'] !!}</svg></span>
        <span class="shop-tiles__label">All products</span>
    </a>
    @foreach ($categories as $category)
        @php($isActive = ($activeCategory ?? '') === $category->slug)
        <a
            href="{{ route('marketplace.shop', array_merge(request()->except('page'), ['category' => $category->slug])) }}"
            class="shop-tiles__tile {{ $isActive ? 'is-active' : '' }}"
            @if ($isActive) aria-current="page" @endif
            data-shop-nav
        >
            <span class="shop-tiles__icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">{!! $icons[$category->slug] ?? $fallback !!}</svg></span>
            <span class="shop-tiles__label">{{ $category->name }}</span>
        </a>
    @endforeach
</nav>
