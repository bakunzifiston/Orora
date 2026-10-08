@props(['listing'])

<div class="shop-gallery" data-shop-gallery>
    <div class="shop-gallery__main">
        @php $images = $listing->images ?? []; @endphp
        @if (count($images))
            <img src="{{ $listing->imageUrl($images[0]) }}" alt="{{ $listing->title }}" data-gallery-main>
        @else
            <div class="shop-gallery__placeholder">{{ $listing->category?->icon ?? '🐄' }}</div>
        @endif
    </div>
    @if (count($images) > 1)
        <div class="shop-gallery__thumbs">
            @foreach ($images as $index => $image)
                <button type="button" class="shop-gallery__thumb {{ $index === 0 ? 'is-active' : '' }}" data-gallery-thumb="{{ $listing->imageUrl($image) }}">
                    <img src="{{ $listing->imageUrl($image) }}" alt="">
                </button>
            @endforeach
        </div>
    @endif
</div>
