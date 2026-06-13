@props(['listing'])

<article class="shop-card">
    <a href="{{ route('marketplace.shop.show', $listing) }}" class="shop-card__media">
        @if ($listing->mainImage())
            <img src="{{ asset($listing->mainImage()) }}" alt="{{ $listing->title }}" loading="lazy">
        @else
            <div class="shop-card__placeholder" aria-hidden="true">{{ $listing->category?->icon ?? '🐄' }}</div>
        @endif

        <div class="shop-card__badges">
            @if ($listing->is_verified)
                <span class="shop-card__badge shop-card__badge--verified">✅ Verified</span>
            @endif
            @if ($listing->is_featured)
                <span class="shop-card__badge shop-card__badge--featured">⭐ Featured</span>
            @endif
        </div>

        @if ($listing->category)
            <span class="shop-card__category">{{ $listing->category->icon }} {{ $listing->category->name }}</span>
        @endif
    </a>

    <div class="shop-card__body">
        <h3 class="shop-card__title">
            <a href="{{ route('marketplace.shop.show', $listing) }}">{{ $listing->title }}</a>
        </h3>

        <p class="shop-card__meta">📍 {{ $listing->locationLabel() }}</p>

        <div class="shop-card__details">
            @if ($listing->breed)
                <span>🐄 {{ $listing->breed }}</span>
            @endif
            @if ($listing->age)
                <span>🗓 {{ $listing->age }}</span>
            @endif
            @if ($listing->weight_kg)
                <span>⚖ {{ number_format((float) $listing->weight_kg, 0) }} kg</span>
            @endif
        </div>

        @if ($listing->quantityLabel())
            <p class="shop-card__qty">Qty: {{ $listing->quantityLabel() }}</p>
        @endif

        <div class="shop-card__footer">
            <div>
                <p class="shop-card__price">{{ number_format((float) $listing->price, 0) }} {{ $listing->currency }}</p>
                <p class="shop-card__price-type">{{ $listing->priceTypeLabel() }}</p>
            </div>
            <a href="{{ route('marketplace.shop.show', $listing) }}" class="shop-card__link">View Details →</a>
        </div>
    </div>
</article>
