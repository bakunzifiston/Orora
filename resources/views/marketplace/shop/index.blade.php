@extends('layouts.marketplace')

@section('title', 'Shop')
@section('meta_description', 'Browse and buy livestock and farm products from verified sellers across Rwanda on Orora Marketplace.')

@push('scripts')
    @vite('resources/js/marketplace-shop.js')
@endpush

@section('content')
    @php
        $activeFilters = collect([
            ($filters['q'] ?? null) ? ['key' => 'q', 'label' => '“'.$filters['q'].'”'] : null,
            ($filters['category'] ?? null) ? ['key' => 'category', 'label' => optional($categories->firstWhere('slug', $filters['category']))->name ?? $filters['category']] : null,
            ($filters['district'] ?? null) ? ['key' => 'district', 'label' => $filters['district']] : null,
            ($filters['price_min'] ?? null) || ($filters['price_max'] ?? null)
                ? ['key' => 'price', 'label' => trim(($filters['price_min'] ?? '0').' – '.($filters['price_max'] ?? '∞').' RWF')]
                : null,
            ($filters['verified'] ?? null) ? ['key' => 'verified', 'label' => 'Verified'] : null,
        ])->filter()->values();

        $sellerTypeLabels = collect((array) ($filters['seller_type'] ?? []))
            ->map(fn ($type) => $sellerTypes[$type] ?? $type)
            ->filter();
    @endphp

    <div class="shop-page" data-shop-page>
        <section class="shop-hero">
            <div class="mp-container shop-hero__inner">
                <div class="shop-hero__copy">
                    <p class="shop-hero__eyebrow">Orora Marketplace</p>
                    <h1 class="shop-hero__title">Find farm products</h1>
                    <p class="shop-hero__subtitle">Livestock, milk, feed, and supplies from sellers across Rwanda.</p>
                </div>

                <form method="GET" action="{{ route('marketplace.shop') }}" class="shop-search" data-shop-form>
                    @if ($filters['category'] ?? false)
                        <input type="hidden" name="category" value="{{ $filters['category'] }}">
                    @endif
                    @if ($filters['district'] ?? false)
                        <input type="hidden" name="district" value="{{ $filters['district'] }}">
                    @endif
                    @if ($filters['sort'] ?? false)
                        <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
                    @endif
                    <label class="shop-search__field" for="shop-q">
                        <svg class="shop-search__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                            <path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <input
                            id="shop-q"
                            type="search"
                            name="q"
                            value="{{ $filters['q'] ?? '' }}"
                            placeholder="Search animals, feed, milk…"
                            aria-label="Search listings"
                            autocomplete="off"
                        >
                        @if (! empty($filters['q']))
                            <a href="{{ route('marketplace.shop', request()->except('q', 'page')) }}" class="shop-search__clear" aria-label="Clear search">×</a>
                        @endif
                    </label>
                    <button type="submit" class="lp-btn lp-btn--primary shop-search__submit">Search</button>
                    @auth
                        <a href="{{ route('marketplace.shop.create') }}" class="lp-btn lp-btn--gold shop-search__sell">Sell</a>
                    @else
                        <a href="{{ route('register') }}" class="lp-btn lp-btn--gold shop-search__sell">Sell</a>
                    @endauth
                </form>
            </div>
        </section>

        <section class="shop-categories" aria-label="Categories">
            <div class="mp-container">
                @include('marketplace.shop.partials.category-tabs', [
                    'categories' => $categories,
                    'activeCategory' => $filters['category'] ?? null,
                ])
            </div>
        </section>

        <section class="shop-layout">
            <div class="mp-container shop-layout__inner">
                <div class="shop-filters-bar">
                    <button type="button" class="shop-filters-toggle lp-btn lp-btn--outline" data-shop-filters-toggle aria-expanded="false" aria-controls="shop-filters">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 6h16M7 12h10M10 18h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Filters
                    </button>

                    @if ($activeFilters->isNotEmpty() || $sellerTypeLabels->isNotEmpty())
                        <div class="shop-active-filters" data-shop-active-filters>
                            @foreach ($activeFilters as $chip)
                                <a
                                    href="{{ route('marketplace.shop', request()->except($chip['key'] === 'price' ? ['price_min', 'price_max', 'page'] : [$chip['key'], 'page'])) }}"
                                    class="shop-chip"
                                >
                                    {{ $chip['label'] }}
                                    <span aria-hidden="true">×</span>
                                </a>
                            @endforeach
                            @foreach ($sellerTypeLabels as $label)
                                <span class="shop-chip shop-chip--static">{{ $label }}</span>
                            @endforeach
                            <a href="{{ route('marketplace.shop') }}" class="shop-chip shop-chip--clear">Clear all</a>
                        </div>
                    @endif
                </div>

                <div class="shop-filters-backdrop" data-shop-filters-backdrop hidden></div>

                @include('marketplace.shop.partials.filters-sidebar', [
                    'filters' => $filters,
                    'districts' => $districts,
                    'sellerTypes' => $sellerTypes,
                    'sortOptions' => $sortOptions,
                ])

                <div class="shop-results" data-shop-results>
                    <div class="shop-results__header">
                        <p class="shop-results__count">
                            <strong>{{ number_format($listings->total()) }}</strong>
                            {{ $listings->total() === 1 ? 'listing' : 'listings' }}
                            @if ($listings->total() > 0)
                                <span class="shop-results__range">
                                    · showing {{ $listings->firstItem() }}–{{ $listings->lastItem() }}
                                </span>
                            @endif
                        </p>

                        <form method="GET" action="{{ route('marketplace.shop') }}" class="shop-results__sort" data-shop-form>
                            @foreach (collect($filters)->except('sort') as $key => $value)
                                @if (is_array($value))
                                    @foreach ($value as $item)
                                        <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                    @endforeach
                                @elseif ($value !== null && $value !== '')
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label for="sort" class="shop-results__sort-label">Sort by</label>
                            <div class="shop-select">
                                <select name="sort" id="sort" onchange="this.form.requestSubmit()">
                                    @foreach ($sortOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    @if (session('success'))
                        <div class="mp-alert mp-alert--success">{{ session('success') }}</div>
                    @endif

                    <div class="shop-skeleton" data-shop-skeleton hidden aria-hidden="true">
                        @for ($i = 0; $i < 6; $i++)
                            <div class="shop-skeleton__card">
                                <div class="shop-skeleton__media"></div>
                                <div class="shop-skeleton__body">
                                    <div class="shop-skeleton__line shop-skeleton__line--sm"></div>
                                    <div class="shop-skeleton__line"></div>
                                    <div class="shop-skeleton__line shop-skeleton__line--md"></div>
                                    <div class="shop-skeleton__footer">
                                        <div class="shop-skeleton__line shop-skeleton__line--price"></div>
                                        <div class="shop-skeleton__line shop-skeleton__line--btn"></div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div class="shop-grid" data-shop-grid>
                        @forelse ($listings as $listing)
                            @include('marketplace.shop.partials.listing-card', ['listing' => $listing])
                        @empty
                            <div class="shop-empty">
                                <div class="shop-empty__icon" aria-hidden="true">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                                        <path d="M4 7h16v12H4V7z" stroke="currentColor" stroke-width="1.75"/>
                                        <path d="M8 7V5a4 4 0 018 0v2" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                @if (empty($shopReady))
                                    <h2 class="shop-empty__title">Marketplace coming soon</h2>
                                    <p class="shop-empty__text">Listings will appear here shortly.</p>
                                @else
                                    <h2 class="shop-empty__title">No listings found</h2>
                                    <p class="shop-empty__text">Try a different search or clear your filters.</p>
                                    <a href="{{ route('marketplace.shop') }}" class="lp-btn lp-btn--primary">Browse all</a>
                                @endif
                            </div>
                        @endforelse
                    </div>

                    @if ($listings->hasPages())
                        <div class="shop-pagination">
                            {{ $listings->onEachSide(1)->links('marketplace.shop.partials.pagination') }}
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection
