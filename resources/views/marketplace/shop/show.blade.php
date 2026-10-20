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
                <span aria-current="page" class="shop-breadcrumb__current">{{ $listing->title }}</span>
            </nav>

            @if (session('success'))
                <div class="mp-alert mp-alert--success">{{ session('success') }}</div>
            @endif

            @php
                $priceUnit = match ($listing->price_type) {
                    'per_kg' => '/ kg',
                    'per_liter' => '/ litre',
                    default => null,
                };
                $priceNote = match ($listing->price_type) {
                    'negotiable' => 'Price negotiable',
                    'fixed' => 'Fixed price',
                    default => null,
                };
                $specs = array_filter([
                    'Species' => $listing->animal_species ? ucfirst($listing->animal_species) : null,
                    'Breed' => $listing->breed,
                    'Sex' => $listing->animal_sex ? ucfirst($listing->animal_sex) : null,
                    'Age' => $listing->age,
                    'Weight' => $listing->weight_kg ? number_format((float) $listing->weight_kg, 0).' kg' : null,
                    'Quantity' => $listing->quantityLabel() ?: null,
                ]);
                $sellerInitials = collect(preg_split('/\s+/', trim((string) $listing->seller_name)))
                    ->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
                $phone = preg_replace('/\s+/', '', (string) $listing->seller_phone);
            @endphp

            <div class="shop-detail__grid">
                <div class="shop-detail__media">
                    @include('marketplace.shop.partials.image-gallery', ['listing' => $listing])

                    @if ($listing->description)
                        <section class="shop-detail__section shop-detail__description">
                            <h2 class="shop-detail__heading">Description</h2>
                            <div class="shop-detail__prose">{!! nl2br(e($listing->description)) !!}</div>
                        </section>
                    @endif
                </div>

                <aside class="shop-detail__info">
                    <div class="shop-detail__badges">
                        @if ($listing->category)
                            <a href="{{ route('marketplace.shop', ['category' => $listing->category->slug]) }}" class="shop-detail__category">{{ $listing->category->name }}</a>
                        @endif
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

                    <h1 class="shop-detail__title">{{ $listing->title }}</h1>
                    <p class="shop-detail__location">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 21s-7-6.2-7-11.5a7 7 0 0114 0C19 14.8 12 21 12 21z" stroke="currentColor" stroke-width="1.8"/>
                            <circle cx="12" cy="9.5" r="2.5" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                        {{ $listing->locationLabel() }}
                    </p>

                    <div class="shop-detail__price">
                        <p class="shop-detail__amount">
                            {{ number_format((float) $listing->price, 0) }}
                            <span>{{ $listing->currency }}{{ $priceUnit ? ' '.$priceUnit : '' }}</span>
                        </p>
                        @if ($priceNote)
                            <span class="shop-detail__price-note {{ $listing->price_type === 'negotiable' ? 'is-negotiable' : '' }}">{{ $priceNote }}</span>
                        @endif
                    </div>

                    @if ($specs)
                        <dl class="shop-detail__specs">
                            @foreach ($specs as $label => $value)
                                <div>
                                    <dt>{{ $label }}</dt>
                                    <dd>{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif

                    <div class="shop-detail__seller">
                        <span class="shop-detail__avatar" aria-hidden="true">{{ $sellerInitials ?: '?' }}</span>
                        <div class="shop-detail__seller-body">
                            <p class="shop-detail__seller-label">Seller</p>
                            <p class="shop-detail__seller-name">{{ $listing->seller_name }}</p>
                            <p class="shop-detail__seller-type">
                                {{ $listing->sellerTypeLabel() }}
                                @if ($listing->location_district)
                                    · {{ $listing->location_district }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="shop-detail__actions">
                        @if ($phone !== '')
                            <a href="tel:{{ $phone }}" class="lp-btn lp-btn--primary shop-detail__call">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                </svg>
                                Call seller
                            </a>
                        @endif
                        <a href="#inquiry" class="lp-btn lp-btn--outline">Send inquiry</a>
                        <button type="button" class="lp-btn lp-btn--outline" data-share-url="{{ url()->current() }}">Share</button>
                        @if ($canEdit ?? false)
                            <a href="{{ route('marketplace.shop.edit', $listing) }}" class="lp-btn lp-btn--outline">Edit</a>
                        @endif
                    </div>

                    <p class="shop-detail__meta">
                        {{ number_format($listing->views_count) }} views
                        · Listed {{ $listing->created_at->format('M j, Y') }}
                        · {{ $listing->listing_code }}
                    </p>
                </aside>
            </div>

            <section class="shop-detail__inquiry">
                @include('marketplace.shop.partials.inquiry-form', ['listing' => $listing])
            </section>

            @include('marketplace.shop.partials.related-listings', [
                'listings' => $relatedListings,
                'title' => $listing->category ? 'More in '.$listing->category->name : 'Related listings',
                'moreUrl' => $listing->category ? route('marketplace.shop', ['category' => $listing->category->slug]) : route('marketplace.shop'),
            ])
        </div>
    </section>
@endsection
