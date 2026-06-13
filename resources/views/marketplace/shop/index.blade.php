@extends('layouts.marketplace')

@section('title', 'Shop')

@push('scripts')
    @vite('resources/js/marketplace-shop.js')
@endpush

@section('content')
    <section class="shop-header">
        <div class="mp-container shop-header__inner">
            <div>
                <h1 class="shop-header__title">Browse Farm Products</h1>
                <p class="shop-header__subtitle">Buy directly from verified farmers across Rwanda</p>
            </div>
            <form method="GET" action="{{ route('marketplace.shop') }}" class="shop-header__search">
                @if ($filters['category'] ?? false)
                    <input type="hidden" name="category" value="{{ $filters['category'] }}">
                @endif
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search listings..." aria-label="Search listings">
                <button type="submit" class="lp-btn lp-btn--primary">Search</button>
            </form>
            @auth
                <a href="{{ route('marketplace.shop.create') }}" class="lp-btn lp-btn--gold">Post a Listing</a>
            @else
                <a href="{{ route('register') }}" class="lp-btn lp-btn--gold">Post a Listing</a>
            @endauth
        </div>
    </section>

    <section class="shop-tabs-wrap">
        <div class="mp-container">
            @include('marketplace.shop.partials.category-tabs', [
                'categories' => $categories,
                'activeCategory' => $filters['category'] ?? null,
            ])
        </div>
    </section>

    <section class="shop-layout">
        <div class="mp-container shop-layout__inner">
            <button type="button" class="shop-filters-toggle lp-btn lp-btn--outline" data-shop-filters-toggle>
                Show Filters
            </button>

            @include('marketplace.shop.partials.filters-sidebar', [
                'filters' => $filters,
                'districts' => $districts,
                'sellerTypes' => $sellerTypes,
                'sortOptions' => $sortOptions,
            ])

            <div class="shop-results">
                <div class="shop-results__header">
                    <p>Showing {{ $listings->count() }} of {{ $listings->total() }} results</p>
                    <form method="GET" action="{{ route('marketplace.shop') }}" class="shop-results__sort">
                        @foreach (collect($filters)->except('sort') as $key => $value)
                            @if (is_array($value))
                                @foreach ($value as $item)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                @endforeach
                            @elseif ($value !== null && $value !== '')
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label for="sort">Sort:</label>
                        <select name="sort" id="sort" onchange="this.form.submit()">
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                @if (session('success'))
                    <div class="mp-alert mp-alert--success">{{ session('success') }}</div>
                @endif

                <div class="shop-grid">
                    @forelse ($listings as $listing)
                        @include('marketplace.shop.partials.listing-card', ['listing' => $listing])
                    @empty
                        <div class="shop-empty">
                            <p>No listings match your filters.</p>
                            <a href="{{ route('marketplace.shop') }}" class="lp-link-arrow">View all listings</a>
                        </div>
                    @endforelse
                </div>

                @if ($listings->hasPages())
                    <div class="shop-pagination">{{ $listings->links() }}</div>
                @endif
            </div>
        </div>
    </section>
@endsection
