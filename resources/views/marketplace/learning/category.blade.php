@extends('layouts.marketplace')

@section('title', $category->name)

@push('scripts')
    @vite('resources/js/marketplace-learning.js')
@endpush

@section('content')
    <section class="learn-category-header">
        <div class="mp-container">
            <p class="learn-category-header__icon">{{ $category->icon }}</p>
            <h1 class="learn-category-header__title">{{ $category->name }}</h1>
            @if ($category->description)
                <p class="learn-category-header__desc">{{ $category->description }}</p>
            @endif
            <p class="learn-category-header__counts">
                {{ $counts['articles'] }} articles · {{ $counts['videos'] }} videos · {{ $counts['pdfs'] }} PDFs
            </p>
        </div>
    </section>

    <section class="learn-type-tabs-wrap">
        <div class="mp-container">
            @include('marketplace.learning.partials.type-tabs', [
                'activeType' => $filters['type'] ?? null,
                'baseRoute' => 'marketplace.learning.category',
                'routeParams' => ['category' => $category],
            ])
        </div>
    </section>

    <section class="learn-layout">
        <div class="mp-container learn-layout__inner">
            <button type="button" class="learn-filters-toggle lp-btn lp-btn--outline" data-learn-filters-toggle>
                Show Filters
            </button>

            @include('marketplace.learning.partials.filters-sidebar', [
                'filters' => array_merge($filters, ['category' => $category->slug]),
                'categories' => $categories,
                'difficultyLevels' => $difficultyLevels,
                'languages' => $languages,
                'contentTypes' => $contentTypes,
                'baseRoute' => 'marketplace.learning.category',
                'routeParams' => ['category' => $category],
            ])

            <div class="learn-results">
                <div class="learn-results__header">
                    <p>Showing {{ $posts->total() }} results in {{ $category->name }}</p>
                </div>

                <div class="learn-grid">
                    @forelse ($posts as $post)
                        @include('marketplace.learning.partials.content-card', ['post' => $post])
                    @empty
                        <div class="learn-empty">
                            <p>No resources in this category yet.</p>
                            <a href="{{ route('marketplace.learning') }}" class="lp-link-arrow">Browse all resources</a>
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
