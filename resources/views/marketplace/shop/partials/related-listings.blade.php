@props(['listings', 'title' => 'Related listings'])

@if ($listings->isNotEmpty())
    <section class="shop-related">
        <h2 class="shop-related__title">{{ $title }}</h2>
        <div class="shop-grid shop-grid--4">
            @foreach ($listings as $listing)
                @include('marketplace.shop.partials.listing-card', ['listing' => $listing])
            @endforeach
        </div>
    </section>
@endif
