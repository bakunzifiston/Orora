@php
    $farmMapMarkers = $farmMapMarkers ?? [];
    $markerCount = count($farmMapMarkers);
@endphp

<div class="dash-panel admin-map-panel">
    <div class="admin-panel-head admin-map-panel__head">
        <h2 class="dash-panel-title">Farms</h2>
        @if ($markerCount > 0)
            <span class="admin-panel-meta">{{ number_format($markerCount) }}</span>
        @endif
    </div>

    @if ($markerCount === 0)
        <p class="dash-empty">No farms on the map yet.</p>
    @else
        <div id="admin-farms-map" class="admin-farms-map" role="region" aria-label="Farm locations"></div>
    @endif
</div>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@endpush
