@props(['listing'])

@php
    $details = array_filter([
        $listing->breed,
        $listing->age,
        $listing->weight_kg ? number_format((float) $listing->weight_kg, 0).' kg' : null,
    ]);

    $priceUnit = match ($listing->price_type) {
        'per_kg' => '/ kg',
        'per_liter' => '/ litre',
        default => null,
    };
    $priceNote = match ($listing->price_type) {
        'negotiable' => 'Negotiable',
        'fixed' => 'Fixed price',
        'per_kg', 'per_liter' => null,
        default => $listing->priceTypeLabel(),
    };
@endphp

<article class="shop-card">
    <div class="shop-card__media">
        @if ($listing->mainImage())
            <img
                src="{{ $listing->imageUrl() }}"
                alt="{{ $listing->title }}"
                loading="lazy"
                class="shop-card__img"
                data-shop-img
            >
        @else
            <div class="shop-card__placeholder" aria-hidden="true">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                    <circle cx="8.5" cy="10" r="1.5" fill="currentColor"/>
                    <path d="M21 16l-5.5-5.5L7 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        @endif

        @if ($listing->is_featured || $listing->is_verified)
            <div class="shop-card__badges">
                @if ($listing->is_featured)
                    <span class="shop-card__badge shop-card__badge--featured">Featured</span>
                @endif
                @if ($listing->is_verified)
                    <span class="shop-card__badge shop-card__badge--verified">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Verified
                    </span>
                @endif
            </div>
        @endif
    </div>

    <div class="shop-card__body">
        <p class="shop-card__meta">
            @if ($listing->category)
                <span>{{ $listing->category->name }}</span>
            @endif
            <span>{{ $listing->location_district ?: $listing->locationLabel() }}</span>
        </p>

        <h3 class="shop-card__title">
            <a href="{{ route('marketplace.shop.show', $listing) }}">{{ $listing->title }}</a>
        </h3>

        @if ($details)
            <p class="shop-card__details">{{ implode(' · ', $details) }}</p>
        @endif

        <div class="shop-card__footer">
            <p class="shop-card__price">
                <span class="shop-card__amount">{{ number_format((float) $listing->price, 0) }}</span>
                <span class="shop-card__currency">{{ $listing->currency }}{{ $priceUnit ? ' '.$priceUnit : '' }}</span>
            </p>
            @if ($priceNote)
                <span class="shop-card__price-type {{ $listing->price_type === 'negotiable' ? 'is-negotiable' : '' }}">{{ $priceNote }}</span>
            @endif
        </div>
    </div>
</article>
