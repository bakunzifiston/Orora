@props([
    'categories',
    'activeCategory' => null,
])

<nav class="shop-tabs" aria-label="Product categories" data-shop-tabs>
    <div class="shop-tabs__track">
        <a
            href="{{ route('marketplace.shop', request()->except('category', 'page')) }}"
            class="shop-tabs__tab {{ empty($activeCategory) ? 'is-active' : '' }}"
            data-shop-nav
        >
            All
        </a>
        @foreach ($categories as $category)
            <a
                href="{{ route('marketplace.shop', array_merge(request()->except('page'), ['category' => $category->slug])) }}"
                class="shop-tabs__tab {{ ($activeCategory ?? '') === $category->slug ? 'is-active' : '' }}"
                data-shop-nav
            >
                {{ $category->name }}
            </a>
        @endforeach
    </div>
</nav>
