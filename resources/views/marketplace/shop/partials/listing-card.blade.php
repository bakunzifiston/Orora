@props(['listing'])

@php
    $views = (int) ($listing->views_count ?? 0);
    $interest = match (true) {
        $views >= 100 => 5,
        $views >= 40 => 4,
        $views >= 15 => 3,
        $views >= 5 => 2,
        $views >= 1 => 1,
        default => 0,
    };
@endphp

<article class="shop-card">
    <a href="{{ route('marketplace.shop.show', $listing) }}" class="shop-card__media">
        @if ($listing->mainImage())
            <img
                src="{{ asset($listing->mainImage()) }}"
                alt="{{ $listing->title }}"
                loading="lazy"
                class="shop-card__img"
                data-shop-img
            >
        @else
            <div class="shop-card__placeholder" aria-hidden="true">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                    <circle cx="8.5" cy="10" r="1.5" fill="currentColor"/>
                    <path d="M21 16l-5.5-5.5L7 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        @endif

        <div class="shop-card__badges">
            @if ($listing->is_featured)
                <span class="shop-card__badge shop-card__badge--featured">Featured</span>
            @endif
            @if ($listing->is_verified)
                <span class="shop-card__badge shop-card__badge--verified">Verified</span>
            @endif
        </div>

        @if ($listing->category)
            <span class="shop-card__category">{{ $listing->category->name }}</span>
        @endif

        <span class="shop-card__media-overlay" aria-hidden="true"></span>
    </a>

    <div class="shop-card__body">
        <div class="shop-card__top">
            <p class="shop-card__meta">{{ $listing->location_district ?: $listing->locationLabel() }}</p>
            @if ($interest > 0)
                <div class="shop-card__rating" title="{{ number_format($views) }} views" aria-label="{{ number_format($views) }} views">
                    @for ($i = 1; $i <= 5; $i++)
                        <span class="shop-card__star {{ $i <= $interest ? 'is-on' : '' }}" aria-hidden="true">★</span>
                    @endfor
                    <span class="shop-card__views">{{ number_format($views) }}</span>
                </div>
            @else
                <span class="shop-card__new">New</span>
            @endif
        </div>

        <h3 class="shop-card__title">
            <a href="{{ route('marketplace.shop.show', $listing) }}">{{ $listing->title }}</a>
        </h3>

        @if ($listing->breed || $listing->age || $listing->weight_kg)
            <div class="shop-card__details">
                @if ($listing->breed)
                    <span>{{ $listing->breed }}</span>
                @endif
                @if ($listing->age)
                    <span>{{ $listing->age }}</span>
                @endif
                @if ($listing->weight_kg)
                    <span>{{ number_format((float) $listing->weight_kg, 0) }} kg</span>
                @endif
            </div>
        @endif

        <div class="shop-card__footer">
            <div class="shop-card__pricing">
                <p class="shop-card__price">
                    <span class="shop-card__amount">{{ number_format((float) $listing->price, 0) }}</span>
                    <span class="shop-card__currency">{{ $listing->currency }}</span>
                </p>
                <p class="shop-card__price-type">{{ $listing->priceTypeLabel() }}</p>
            </div>
            <a href="{{ route('marketplace.shop.show', $listing) }}" class="shop-card__cta">
                View
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>
    </div>
</article>
