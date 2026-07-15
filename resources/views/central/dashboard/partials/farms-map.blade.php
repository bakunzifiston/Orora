@php
    $farmMapMarkers = $farmMapMarkers ?? [];
    $markerCount = count($farmMapMarkers);
@endphp

<section class="dash-ops-row" aria-label="Farm locations map">
    <div class="dash-panel">
        <div class="dash-panel-head" style="margin-bottom: 0.75rem;">
            <h2 class="dash-panel-title" style="margin: 0;">Farm locations</h2>
            <span class="dash-field-hint" style="margin: 0;">
                {{ number_format($markerCount) }} on map
                @if ($markerCount > 0)
                    · click a pin for details
                @endif
            </span>
        </div>
        <p class="dash-field-hint" style="margin: 0 0 1rem;">
            Pins use GPS coordinates when saved on a farm; otherwise an approximate location from the registered district.
        </p>

        @if ($markerCount === 0)
            <p class="dash-empty">No farms to display on the map yet.</p>
        @else
            <div id="admin-farms-map" class="admin-farms-map" role="region" aria-label="Map of registered farms in Rwanda"></div>
        @endif
    </div>
</section>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <style>
        .admin-farms-map {
            width: 100%;
            height: 420px;
            border-radius: 0.65rem;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            z-index: 1;
        }
        .admin-farm-marker {
            background: #002B2B;
            color: #fff;
            border: 2px solid #A4D400;
            border-radius: 999px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0, 43, 43, 0.25);
        }
        .admin-farm-popup__title {
            font-weight: 700;
            color: #002B2B;
            margin: 0 0 0.25rem;
        }
        .admin-farm-popup__meta {
            font-size: 0.8125rem;
            color: #6b7280;
            margin: 0 0 0.5rem;
        }
        .admin-farm-popup__link {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #002B2B;
            text-decoration: none;
        }
        .admin-farm-popup__link:hover {
            text-decoration: underline;
        }
    </style>
@endpush
