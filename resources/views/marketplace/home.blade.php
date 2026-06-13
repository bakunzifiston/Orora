@extends('layouts.marketplace')

@section('title', 'Home')

@section('content')
    @php
        $heroImage = asset(config('branding.auth_background'));
    @endphp

    {{-- Section 2 — Hero --}}
    <section class="lp-hero">
        <div class="mp-container lp-hero__grid">
            <div class="lp-hero__copy">
                <h1 class="lp-hero__title">
                    Your Entire Farm.<br>
                    <span>One System.</span>
                </h1>
                <p class="lp-hero__lead">
                    Everything you and your team need to manage your farm
                    — animals, milk, health, sales, and finances —
                    all in one place.
                </p>
                <div class="lp-hero__actions">
                    <a href="{{ route('marketplace.trace') }}" class="lp-btn lp-btn--primary lp-btn--lg">
                        Trace an Animal <span aria-hidden="true">→</span>
                    </a>
                    <a href="{{ route('marketplace.shop') }}" class="lp-btn lp-btn--outline lp-btn--lg">Browse Marketplace</a>
                </div>
                <ul class="lp-trust-badges">
                    @foreach (config('marketplace.hero.trust_badges', []) as $badge)
                        <li><span aria-hidden="true">✓</span> {{ $badge }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="lp-hero__visual">
                <div class="lp-hero__mockup">
                    <div class="lp-mockup__chrome">
                        <span></span><span></span><span></span>
                    </div>
                    <img src="{{ $heroImage }}" alt="Orora Farm dashboard and livestock management" class="lp-hero__image" loading="eager">
                    <div class="lp-mockup__badge">
                        <span class="lp-mockup__badge-dot"></span>
                        Live farm data
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 3 — Stats Bar --}}
    <section class="lp-stats" data-lp-stats>
        <div class="mp-container lp-stats__grid">
            @foreach ($landingStats as $stat)
                <div class="lp-stats__item">
                    @if ($stat['animate'] && $stat['value'])
                        <div
                            class="lp-stats__value"
                            data-count-up
                            data-target="{{ $stat['value'] }}"
                            data-suffix="{{ $stat['suffix'] ?? '' }}"
                        >0{{ $stat['suffix'] ?? '' }}</div>
                    @else
                        <div class="lp-stats__value">{{ $stat['display'] }}</div>
                    @endif
                    <div class="lp-stats__label">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Section 4 — Problem / Solution --}}
    @php $ps = config('marketplace.problem_solution'); @endphp
    <section class="lp-section">
        <div class="mp-container">
            <div class="lp-section__header lp-section__header--center">
                <h2 class="lp-section__title">{{ $ps['title'] }}</h2>
                <p class="lp-section__subtitle">{{ $ps['subtitle'] }}</p>
            </div>

            <div class="lp-compare">
                <div class="lp-compare__col lp-compare__col--before">
                    <h3 class="lp-compare__heading">{{ $ps['before_label'] }}</h3>
                    <ul class="lp-compare__list">
                        @foreach ($ps['rows'] as $row)
                            <li><span class="lp-compare__icon lp-compare__icon--bad" aria-hidden="true">✕</span> {{ $row['before'] }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="lp-compare__col lp-compare__col--after">
                    <h3 class="lp-compare__heading">{{ $ps['after_label'] }}</h3>
                    <ul class="lp-compare__list">
                        @foreach ($ps['rows'] as $row)
                            <li><span class="lp-compare__icon lp-compare__icon--good" aria-hidden="true">✓</span> {{ $row['after'] }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 5 — Features --}}
    <section class="lp-section lp-section--white" id="features">
        <div class="mp-container">
            <div class="lp-section__header lp-section__header--center">
                <h2 class="lp-section__title">Everything your farm needs</h2>
                <p class="lp-section__subtitle">Built for how farmers actually work</p>
            </div>
            <div class="lp-feature-grid">
                @foreach (config('marketplace.features') as $feature)
                    <article class="lp-feature-card">
                        <span class="lp-feature-card__icon" aria-hidden="true">{{ $feature['icon'] }}</span>
                        <h3 class="lp-feature-card__title">{{ $feature['title'] }}</h3>
                        <p class="lp-feature-card__text">{{ $feature['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Section 6 — How It Works --}}
    <section class="lp-section lp-section--grey">
        <div class="mp-container">
            <div class="lp-section__header lp-section__header--center">
                <h2 class="lp-section__title">Get started in minutes</h2>
            </div>
            <div class="lp-flow">
                @foreach (config('marketplace.how_it_works') as $index => $step)
                    <div class="lp-flow__step">
                        <div class="lp-flow__number">{{ $step['step'] }}</div>
                        <h3 class="lp-flow__title">{{ $step['title'] }}</h3>
                        <p class="lp-flow__text">{{ $step['description'] }}</p>
                    </div>
                    @if ($index < count(config('marketplace.how_it_works')) - 1)
                        <div class="lp-flow__arrow" aria-hidden="true">→</div>
                    @endif
                @endforeach
            </div>
            <div class="lp-section__cta">
                <a href="{{ route('register') }}" class="lp-btn lp-btn--primary lp-btn--lg">
                    Create Your Farm Account <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Section 7 — Marketplace Preview --}}
    <section class="lp-section">
        <div class="mp-container">
            <div class="lp-section__header lp-section__header--center">
                <h2 class="lp-section__title">Buy and sell farm products</h2>
                <p class="lp-section__subtitle">Connect directly with verified farmers and buyers</p>
            </div>

            <div class="lp-chips">
                @forelse ($categories as $category)
                    <a href="{{ route('marketplace.shop', ['category' => $category->slug]) }}" class="lp-chip">
                        {{ $category->icon }} {{ $category->name }}
                    </a>
                @empty
                    @foreach ([
                        ['icon' => '🐄', 'name' => 'Live Animals', 'slug' => 'live-animals'],
                        ['icon' => '🥩', 'name' => 'Meat Products', 'slug' => 'meat-products'],
                        ['icon' => '🥛', 'name' => 'Milk & Dairy', 'slug' => 'milk-dairy'],
                        ['icon' => '🌾', 'name' => 'Feed & Supplies', 'slug' => 'feed-supplies'],
                    ] as $chip)
                        <a href="{{ route('marketplace.shop', ['category' => $chip['slug']]) }}" class="lp-chip">{{ $chip['icon'] }} {{ $chip['name'] }}</a>
                    @endforeach
                @endforelse
            </div>

            <div class="mp-card-grid">
                @forelse ($featuredListings as $listing)
                    @include('marketplace.shop.partials.listing-card', ['listing' => $listing])
                @empty
                    <div class="mp-empty-state">
                        <p>No listings yet. <a href="{{ route('register') }}">Register your farm</a> to start selling.</p>
                    </div>
                @endforelse
            </div>

            <div class="lp-section__cta">
                <a href="{{ route('marketplace.shop') }}" class="lp-link-arrow">Browse Marketplace</a>
            </div>
        </div>
    </section>

    {{-- Section 8 — Learning Preview --}}
    <section class="lp-section lp-section--tint">
        <div class="mp-container">
            <div class="lp-section__header lp-section__header--center">
                <h2 class="lp-section__title">Learn from experts</h2>
                <p class="lp-section__subtitle">Articles, videos, and guides for modern farmers</p>
            </div>
            <div class="mp-card-grid">
                @forelse ($latestLearning as $post)
                    @include('marketplace.learning.partials.content-card', ['post' => $post])
                @empty
                    <div class="mp-empty-state">
                        <p>Learning resources coming soon.</p>
                    </div>
                @endforelse
            </div>
            <div class="lp-section__cta">
                <a href="{{ route('marketplace.learning') }}" class="lp-link-arrow">Visit Learning Hub</a>
            </div>
        </div>
    </section>

    {{-- Section 9 — Testimonials --}}
    <section class="lp-testimonials" data-lp-testimonials>
        <div class="mp-container">
            <div class="lp-section__header lp-section__header--center">
                <h2 class="lp-section__title lp-section__title--light">Trusted by farmers across Rwanda</h2>
            </div>

            <div class="lp-testimonials__slider">
                @foreach (config('marketplace.testimonials') as $index => $testimonial)
                    <blockquote class="lp-testimonial {{ $index === 0 ? 'is-active' : '' }}" data-testimonial-slide="{{ $index }}">
                        <span class="lp-testimonial__mark" aria-hidden="true">"</span>
                        <p class="lp-testimonial__quote">{{ $testimonial['quote'] }}</p>
                        <footer class="lp-testimonial__author">
                            <div class="lp-testimonial__avatar" aria-hidden="true">{{ $testimonial['initials'] }}</div>
                            <div>
                                <cite class="lp-testimonial__name">{{ $testimonial['name'] }}</cite>
                                <p class="lp-testimonial__role">{{ $testimonial['role'] }}, {{ $testimonial['location'] }}</p>
                            </div>
                        </footer>
                    </blockquote>
                @endforeach
            </div>

            <div class="lp-testimonials__nav">
                <button type="button" class="lp-testimonials__btn" data-testimonial-prev aria-label="Previous testimonial">←</button>
                <div class="lp-testimonials__dots">
                    @foreach (config('marketplace.testimonials') as $index => $testimonial)
                        <button
                            type="button"
                            class="lp-testimonials__dot {{ $index === 0 ? 'is-active' : '' }}"
                            data-testimonial-dot="{{ $index }}"
                            aria-label="Show testimonial {{ $index + 1 }}"
                        ></button>
                    @endforeach
                </div>
                <button type="button" class="lp-testimonials__btn" data-testimonial-next aria-label="Next testimonial">→</button>
            </div>
        </div>
    </section>

    {{-- Section 10 — Pricing --}}
    <section class="lp-section" id="pricing">
        <div class="mp-container">
            <div class="lp-section__header lp-section__header--center">
                <h2 class="lp-section__title">Simple, transparent pricing</h2>
                <p class="lp-section__subtitle">Start free. Grow with us.</p>
            </div>
            <div class="lp-pricing-grid">
                @foreach (config('marketplace.pricing') as $plan)
                    <article class="lp-pricing-card {{ $plan['popular'] ? 'lp-pricing-card--popular' : '' }}">
                        @if ($plan['popular'])
                            <span class="lp-pricing-card__badge">⭐ Most Popular</span>
                        @endif
                        <h3 class="lp-pricing-card__name">{{ $plan['name'] }}</h3>
                        <div class="lp-pricing-card__price">
                            {{ $plan['price'] }}
                            @if ($plan['period'])
                                <span>/ {{ $plan['period'] }}</span>
                            @endif
                        </div>
                        <ul class="lp-pricing-card__features">
                            @foreach ($plan['features'] as $feature)
                                <li><span aria-hidden="true">✓</span> {{ $feature }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ route($plan['cta_route']) }}" class="lp-btn {{ $plan['popular'] ? 'lp-btn--primary' : 'lp-btn--outline' }} lp-btn--block">
                            {{ $plan['cta'] }}
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Section 11 — CTA Banner --}}
    <section class="lp-cta-banner">
        <div class="mp-container lp-cta-banner__inner">
            <h2 class="lp-cta-banner__title">Ready to manage your farm smarter?</h2>
            <p class="lp-cta-banner__text">Join hundreds of farmers already using Orora Farm</p>
            <div class="lp-cta-banner__actions">
                <a href="{{ route('register') }}" class="lp-btn lp-btn--dark lp-btn--lg">
                    Start Free Trial <span aria-hidden="true">→</span>
                </a>
                <a href="{{ route('marketplace.contact') }}" class="lp-btn lp-btn--ghost-dark lp-btn--lg">Talk to Us</a>
            </div>
        </div>
    </section>
@endsection
