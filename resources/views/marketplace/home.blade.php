@extends('layouts.marketplace')

@section('title', 'Home')
@section('meta_description', 'Orora Farm — livestock and farm management for Rwanda. Track animals, milk, health, sales, and finance in one place.')

@section('content')
    @php
        $heroImage = asset(config('branding.auth_background'));
        $ps = config('marketplace.problem_solution');
    @endphp

    <div class="lp-home">
        <section class="lp-hero lp-hero--home" style="--lp-hero-image: url('{{ $heroImage }}')">
            <div class="lp-hero__media" aria-hidden="true"></div>
            <div class="lp-hero__shade" aria-hidden="true"></div>
            <div class="mp-container lp-hero__frame">
                <div class="lp-hero__copy is-visible" data-lp-reveal>
                    <p class="lp-hero__brand">Orora Farm</p>
                    <h1 class="lp-hero__title">
                        Farm management,<br>
                        <span>made simple.</span>
                    </h1>
                    <p class="lp-hero__lead">
                        Track animals, milk, health, and sales in one place.
                    </p>
                    <div class="lp-hero__actions">
                        <a href="{{ route('register') }}" class="lp-btn lp-btn--accent lp-btn--lg">Get started</a>
                        <a href="{{ route('marketplace.shop') }}" class="lp-btn lp-btn--ghost-light lp-btn--lg">Marketplace</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="lp-stats lp-stats--home" data-lp-stats data-lp-reveal>
            <div class="mp-container">
                <div class="lp-stats__grid lp-stats__grid--three">
                    @foreach (array_slice($landingStats, 0, 3) as $stat)
                        <div class="lp-stats__item">
                            @if (($stat['animate'] ?? false) && ($stat['value'] ?? null))
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
            </div>
        </section>

        <section class="lp-section lp-section--home" data-lp-reveal>
            <div class="mp-container">
                <div class="lp-section__header lp-section__header--center">
                    <p class="lp-eyebrow">The shift</p>
                    <h2 class="lp-section__title">{{ $ps['title'] }}</h2>
                    <p class="lp-section__subtitle">{{ $ps['subtitle'] }}</p>
                </div>

                <div class="lp-compare lp-compare--home">
                    <div class="lp-compare__col lp-compare__col--before">
                        <h3 class="lp-compare__heading">{{ $ps['before_label'] }}</h3>
                        <ul class="lp-compare__list">
                            @foreach ($ps['rows'] as $row)
                                <li>
                                    <span class="lp-compare__icon lp-compare__icon--bad" aria-hidden="true">
                                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M3 3l6 6M9 3L3 9" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>
                                    </span>
                                    <span>{{ $row['before'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="lp-compare__col lp-compare__col--after">
                        <h3 class="lp-compare__heading">{{ $ps['after_label'] }}</h3>
                        <ul class="lp-compare__list">
                            @foreach ($ps['rows'] as $row)
                                <li>
                                    <span class="lp-compare__icon lp-compare__icon--good" aria-hidden="true">
                                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2.5 6.2L4.8 8.5 9.5 3.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <span>{{ $row['after'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section class="lp-section lp-section--home lp-section--soft" id="features" data-lp-reveal>
            <div class="mp-container">
                <div class="lp-section__header lp-section__header--center">
                    <p class="lp-eyebrow">Platform</p>
                    <h2 class="lp-section__title">Everything your farm needs</h2>
                    <p class="lp-section__subtitle">Modules designed for day-to-day livestock operations.</p>
                </div>
                <div class="lp-feature-grid lp-feature-grid--home">
                    @foreach (config('marketplace.features') as $index => $feature)
                        <article class="lp-feature-card lp-feature-card--home">
                            <span class="lp-feature-card__index" aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <h3 class="lp-feature-card__title">{{ $feature['title'] }}</h3>
                            <p class="lp-feature-card__text">{{ $feature['description'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="lp-section lp-section--home" data-lp-reveal>
            <div class="mp-container">
                <div class="lp-section__header lp-section__header--center">
                    <p class="lp-eyebrow">Onboarding</p>
                    <h2 class="lp-section__title">Get started in minutes</h2>
                    <p class="lp-section__subtitle">Three clear steps from signup to daily farm records.</p>
                </div>
                <div class="lp-flow lp-flow--home">
                    @foreach (config('marketplace.how_it_works') as $step)
                        <div class="lp-flow__step">
                            <div class="lp-flow__number">{{ $step['step'] }}</div>
                            <h3 class="lp-flow__title">{{ $step['title'] }}</h3>
                            <p class="lp-flow__text">{{ $step['description'] }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="lp-section__cta">
                    <a href="{{ route('register') }}" class="lp-btn lp-btn--primary lp-btn--lg">Create your farm account</a>
                </div>
            </div>
        </section>

        <section class="lp-section lp-section--home lp-section--soft" data-lp-reveal>
            <div class="mp-container">
                <div class="lp-section__header lp-section__header--center">
                    <p class="lp-eyebrow">Marketplace</p>
                    <h2 class="lp-section__title">Buy and sell farm products</h2>
                    <p class="lp-section__subtitle">Connect directly with verified farmers and buyers across Rwanda.</p>
                </div>

                @if ($categories->isNotEmpty())
                    <div class="lp-category-links">
                        @foreach ($categories->take(6) as $category)
                            <a href="{{ route('marketplace.shop', ['category' => $category->slug]) }}" class="lp-category-link">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="mp-card-grid lp-listing-grid">
                    @forelse ($featuredListings as $listing)
                        @include('marketplace.shop.partials.listing-card', ['listing' => $listing])
                    @empty
                        <div class="mp-empty-state">
                            <p>No listings yet. <a href="{{ route('register') }}">Register your farm</a> to start selling.</p>
                        </div>
                    @endforelse
                </div>

                <div class="lp-section__cta">
                    <a href="{{ route('marketplace.shop') }}" class="lp-link-arrow">Browse marketplace</a>
                </div>
            </div>
        </section>

        <section class="lp-testimonials lp-testimonials--home" data-lp-testimonials data-lp-reveal>
            <div class="mp-container">
                <div class="lp-section__header lp-section__header--center">
                    <p class="lp-eyebrow lp-eyebrow--on-dark">Voices</p>
                    <h2 class="lp-section__title lp-section__title--light">Trusted by farmers across Rwanda</h2>
                </div>

                <div class="lp-testimonials__slider">
                    @foreach (config('marketplace.testimonials') as $index => $testimonial)
                        <blockquote class="lp-testimonial {{ $index === 0 ? 'is-active' : '' }}" data-testimonial-slide="{{ $index }}">
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
                    <button type="button" class="lp-testimonials__btn" data-testimonial-prev aria-label="Previous testimonial">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
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
                    <button type="button" class="lp-testimonials__btn" data-testimonial-next aria-label="Next testimonial">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
            </div>
        </section>

        <section class="lp-section lp-section--home" id="pricing" data-lp-reveal>
            <div class="mp-container">
                <div class="lp-section__header lp-section__header--center">
                    <p class="lp-eyebrow">Plans</p>
                    <h2 class="lp-section__title">Simple, transparent pricing</h2>
                    <p class="lp-section__subtitle">Start free. Upgrade when your operation grows.</p>
                </div>
                <div class="lp-pricing-grid lp-pricing-grid--home">
                    @foreach (config('marketplace.pricing') as $plan)
                        <article class="lp-pricing-card {{ $plan['popular'] ? 'lp-pricing-card--popular' : '' }}">
                            @if ($plan['popular'])
                                <span class="lp-pricing-card__badge">Most popular</span>
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

        <section class="lp-cta-banner lp-cta-banner--home" data-lp-reveal>
            <div class="mp-container lp-cta-banner__inner">
                <h2 class="lp-cta-banner__title">Start free today</h2>
                <p class="lp-cta-banner__text">Your farm. One system.</p>
                <div class="lp-cta-banner__actions">
                    <a href="{{ route('register') }}" class="lp-btn lp-btn--dark lp-btn--lg">Create account</a>
                    <a href="{{ route('marketplace.contact') }}" class="lp-btn lp-btn--ghost-dark lp-btn--lg">Contact us</a>
                </div>
            </div>
        </section>
    </div>
@endsection
