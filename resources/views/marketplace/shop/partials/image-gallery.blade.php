@props(['listing'])

<div class="shop-gallery" data-shop-gallery>
    <div class="shop-gallery__main">
        @php $images = $listing->images ?? []; @endphp
        @if (count($images))
            <img src="{{ $listing->imageUrl($images[0]) }}" alt="{{ $listing->title }}" data-gallery-main>
        @else
            <div class="shop-gallery__placeholder" aria-hidden="true">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                    <circle cx="8.5" cy="10" r="1.5" fill="currentColor"/>
                    <path d="M21 16l-5.5-5.5L7 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>No photos yet</span>
            </div>
        @endif
    </div>
    @if (count($images) > 1)
        <div class="shop-gallery__thumbs">
            @foreach ($images as $index => $image)
                <button type="button" aria-label="Show photo {{ $index + 1 }}" class="shop-gallery__thumb {{ $index === 0 ? 'is-active' : '' }}" data-gallery-thumb="{{ $listing->imageUrl($image) }}">
                    <img src="{{ $listing->imageUrl($image) }}" alt="">
                </button>
            @endforeach
        </div>
    @endif
</div>
