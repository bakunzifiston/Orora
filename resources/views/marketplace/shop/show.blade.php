@extends('layouts.marketplace')

@section('title', $listing->title)

@push('scripts')
    @vite('resources/js/marketplace-shop.js')
@endpush

@section('content')
    <section class="shop-detail">
        <div class="mp-container">
            <nav class="shop-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('marketplace.home') }}">Home</a>
                <span>/</span>
                <a href="{{ route('marketplace.shop') }}">Shop</a>
                @if ($listing->category)
                    <span>/</span>
                    <a href="{{ route('marketplace.shop', ['category' => $listing->category->slug]) }}">{{ $listing->category->name }}</a>
                @endif
                <span>/</span>
                <span aria-current="page">{{ $listing->title }}</span>
            </nav>

            @if (session('success'))
                <div class="mp-alert mp-alert--success">{{ session('success') }}</div>
            @endif

            <div class="shop-detail__grid">
                @include('marketplace.shop.partials.image-gallery', ['listing' => $listing])

                <div class="shop-detail__info">
                    <div class="shop-detail__badges">
                        @if ($listing->is_verified)
                            <span class="shop-card__badge shop-card__badge--verified">Verified</span>
                        @endif
                        @if ($listing->is_featured)
                            <span class="shop-card__badge shop-card__badge--featured">Featured</span>
                        @endif
                        @if ($listing->category)
                            <span class="shop-card__badge">{{ $listing->category->name }}</span>
                        @endif
                    </div>

                    <h1 class="shop-detail__title">{{ $listing->title }}</h1>
                    <p class="shop-detail__price">{{ $listing->formattedPrice() }}</p>

                    <dl class="shop-detail__specs">
                        @if ($listing->breed)
                            <div><dt>Breed</dt><dd>{{ $listing->breed }}</dd></div>
                        @endif
                        @if ($listing->age)
                            <div><dt>Age</dt><dd>{{ $listing->age }}</dd></div>
                        @endif
                        @if ($listing->weight_kg)
                            <div><dt>Weight</dt><dd>{{ number_format((float) $listing->weight_kg, 0) }} kg</dd></div>
                        @endif
                        @if ($listing->quantityLabel())
                            <div><dt>Quantity</dt><dd>{{ $listing->quantityLabel() }}</dd></div>
                        @endif
                        <div><dt>Location</dt><dd>{{ $listing->locationLabel() }}</dd></div>
                    </dl>

                    <div class="shop-detail__seller">
                        <h2>Seller</h2>
                        <p class="shop-detail__seller-name">{{ $listing->seller_name }}</p>
                        <p class="shop-detail__seller-type">{{ $listing->sellerTypeLabel() }}</p>
                        <p class="shop-detail__seller-location">{{ $listing->location_district }}</p>
                    </div>

                    <div class="shop-detail__actions">
                        <a href="tel:{{ preg_replace('/\s+/', '', $listing->seller_phone) }}" class="lp-btn lp-btn--primary">Call</a>
                        <a href="#inquiry" class="lp-btn lp-btn--outline">Inquire</a>
                        <button type="button" class="lp-btn lp-btn--outline" data-share-url="{{ url()->current() }}">Share</button>
                        @if ($canEdit ?? false)
                            <a href="{{ route('marketplace.shop.edit', $listing) }}" class="lp-btn lp-btn--outline">Edit</a>
                        @endif
                    </div>

                    <p class="shop-detail__meta">
                        {{ number_format($listing->views_count) }} views
                        · {{ $listing->created_at->format('M j, Y') }}
                        · {{ $listing->listing_code }}
                    </p>
                </div>
            </div>

            @if ($listing->description)
                <section class="shop-detail__description">
                    <h2>Description</h2>
                    <div class="mp-prose">{!! nl2br(e($listing->description)) !!}</div>
                </section>
            @endif

            <section class="shop-detail__inquiry">
                @include('marketplace.shop.partials.inquiry-form', ['listing' => $listing])
            </section>

            @include('marketplace.shop.partials.related-listings', [
                'listings' => $relatedListings,
                'title' => $listing->category?->name ?? 'Related',
            ])
        </div>
    </section>
@endsection
