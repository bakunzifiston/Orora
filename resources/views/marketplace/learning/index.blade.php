@extends('layouts.marketplace')

@section('title', 'Learning')

@push('scripts')
    @vite('resources/js/marketplace-learning.js')
@endpush

@section('content')
    <section class="learn-header">
        <div class="mp-container learn-header__inner">
            <h1 class="learn-header__title">Learn. Grow. Farm Better.</h1>
            <p class="learn-header__subtitle">Expert articles, videos, and guides for modern farmers</p>
            <form method="GET" action="{{ route('marketplace.learning') }}" class="learn-header__search">
                <span aria-hidden="true">🔍</span>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search resources..." aria-label="Search learning resources">
                <button type="submit" class="lp-btn lp-btn--primary">Search</button>
            </form>
        </div>
    </section>

    <section class="learn-type-tabs-wrap">
        <div class="mp-container">
            @include('marketplace.learning.partials.type-tabs', ['activeType' => $filters['type'] ?? null])
        </div>
    </section>

    @if ($featuredPost)
        <section class="learn-featured-wrap">
            <div class="mp-container">
                @include('marketplace.learning.partials.featured-post', ['post' => $featuredPost])
            </div>
        </section>
    @endif

    <section class="learn-categories-wrap">
        <div class="mp-container">
            <h2 class="learn-section-title">Browse by category</h2>
            @include('marketplace.learning.partials.category-chips', ['categories' => $categories])
        </div>
    </section>

    <section class="learn-layout">
        <div class="mp-container learn-layout__inner">
            <button type="button" class="learn-filters-toggle lp-btn lp-btn--outline" data-learn-filters-toggle>
                Show Filters
            </button>

            @include('marketplace.learning.partials.filters-sidebar', [
                'filters' => $filters,
                'categories' => $categories,
                'difficultyLevels' => $difficultyLevels,
                'languages' => $languages,
                'contentTypes' => $contentTypes,
            ])

            <div class="learn-results">
                <div class="learn-results__header">
                    <h2 class="learn-section-title">All resources</h2>
                    <p>Showing {{ $posts->total() }} results</p>
                    <form method="GET" action="{{ route('marketplace.learning') }}" class="learn-results__sort">
                        @foreach (collect($filters)->except('sort') as $key => $value)
                            @if (is_array($value))
                                @foreach ($value as $item)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                @endforeach
                            @elseif ($value !== null && $value !== '')
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label for="learn-sort">Sort:</label>
                        <select name="sort" id="learn-sort" onchange="this.form.submit()">
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="learn-grid">
                    @forelse ($posts as $post)
                        @include('marketplace.learning.partials.content-card', ['post' => $post])
                    @empty
                        <div class="learn-empty">
                            <p>No resources match your filters.</p>
                            <a href="{{ route('marketplace.learning') }}" class="lp-link-arrow">View all resources</a>
                        </div>
                    @endforelse
                </div>

                @if ($posts->hasPages())
                    <div class="learn-pagination">{{ $posts->links() }}</div>
                @endif
            </div>
        </div>
    </section>

    @include('marketplace.learning.partials.newsletter')
@endsection
