@props(['listings', 'title' => 'Related listings', 'moreUrl' => null])

@if ($listings->isNotEmpty())
    <section class="shop-related">
        <div class="shop-related__head">
            <h2 class="shop-related__title">{{ $title }}</h2>
            @if ($moreUrl)
                <a href="{{ $moreUrl }}" class="shop-related__more">View all</a>
            @endif
        </div>
        <div class="shop-grid shop-grid--4">
            @foreach ($listings as $listing)
                @include('marketplace.shop.partials.listing-card', ['listing' => $listing])
            @endforeach
        </div>
    </section>
@endif
