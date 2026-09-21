@php
    $farmMapMarkers = $farmMapMarkers ?? [];
    $markerCount = count($farmMapMarkers);
@endphp

<article class="farm-panel admin-map-panel">
    <header class="farm-panel__head">
        <div>
            <h2 class="farm-panel__title">{{ __('Farms map') }}</h2>
            <p class="farm-panel__desc">
                @if ($markerCount > 0)
                    {{ __(':count with location', ['count' => number_format($markerCount)]) }}
                @else
                    {{ __('Locations across the platform') }}
                @endif
            </p>
        </div>
        <a href="{{ route('central.farms.index') }}" class="admin-inbox-link">{{ __('View farms') }}</a>
    </header>

    @if ($markerCount === 0)
        <p class="dash-empty">{{ __('No farms on the map yet.') }}</p>
    @else
        <div id="admin-farms-map" class="admin-farms-map" role="region" aria-label="{{ __('Farm locations') }}"></div>
    @endif
</article>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@endpush
