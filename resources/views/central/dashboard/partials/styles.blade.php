<style>
    .admin-dash .dash-stats { margin-bottom: 0; }
    .admin-kpis .dash-stat-value { font-size: 1.5rem; }
    .admin-kpis .dash-stat-label { font-size: 0.75rem; letter-spacing: 0.02em; }

    .admin-panel-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }
    .admin-panel-head .dash-panel-title { margin: 0; }
    .admin-panel-meta {
        font-size: 0.8125rem;
        color: var(--orora-gray);
        font-weight: 500;
    }

    .admin-map-panel {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        padding-bottom: 0;
    }
    .admin-map-panel__head {
        margin-bottom: 0.85rem;
    }
    .admin-farms-map {
        width: calc(100% + 2.5rem);
        margin: 0 -1.25rem;
        height: 320px;
        border: 0;
        border-top: 1px solid #e5e7eb;
        border-radius: 0;
        overflow: hidden;
        background: #f8faf9;
    }
    .admin-farms-map .leaflet-control-attribution {
        font-size: 9px;
        background: rgba(255, 255, 255, 0.75);
        color: #9ca3af;
        border: 0;
        box-shadow: none;
        padding: 0 4px;
    }
    .admin-farms-map .leaflet-control-zoom a {
        width: 28px;
        height: 28px;
        line-height: 28px;
        color: #002B2B;
        border-color: #e5e7eb;
    }
    .admin-farm-marker {
        background: #002B2B;
        border: 2px solid #A4D400;
        border-radius: 999px;
        width: 12px;
        height: 12px;
        box-shadow: 0 1px 4px rgba(0, 43, 43, 0.2);
    }
    .admin-panel-title-link {
        color: inherit;
        text-decoration: none;
    }
    .admin-panel-title-link:hover {
        color: #002B2B;
        text-decoration: underline;
    }
    .admin-panel-meta--link {
        text-decoration: none;
        color: var(--orora-gray);
    }
    .admin-panel-meta--link:hover {
        color: #002B2B;
        text-decoration: underline;
    }
    .admin-farm-row-link {
        font-weight: 700;
        color: #002B2B;
        text-decoration: none;
    }
    .admin-farm-row-link:hover {
        color: #A4D400;
        text-decoration: underline;
    }
    .admin-farm-popup__title {
        font-weight: 700;
        color: #002B2B;
        margin: 0;
        font-size: 0.875rem;
        line-height: 1.3;
        text-decoration: none;
        display: block;
    }
    .admin-farm-popup__title:hover {
        color: #A4D400;
        text-decoration: underline;
    }
    .admin-farm-popup__meta {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0.2rem 0 0;
        line-height: 1.35;
    }
    .admin-farm-popup__link {
        display: inline-block;
        margin-top: 0.45rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #002B2B;
        text-decoration: none;
    }
    .admin-farm-popup__link:hover {
        color: #A4D400;
        text-decoration: underline;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 0.5rem;
        box-shadow: 0 4px 16px rgba(0, 43, 43, 0.12);
        padding: 0;
    }
    .leaflet-popup-content {
        margin: 0.65rem 0.75rem;
        line-height: 1.35;
    }
    .leaflet-popup-tip {
        box-shadow: none;
    }

    .admin-chart-panel .dash-home-chart-wrap { height: 280px; }
    .admin-chart-ref__canvas { height: 260px; }

    .admin-chart-panel--groups .admin-donut--compact {
        grid-template-columns: 1fr;
        gap: 0.75rem;
        min-height: 0;
    }
    .admin-chart-panel--groups .admin-donut__chart {
        height: 150px;
        max-width: 150px;
    }
    .admin-chart-panel--groups .admin-donut__legend {
        max-height: 110px;
        overflow-y: auto;
    }
    .admin-chart-panel--groups .admin-donut__item {
        font-size: 0.75rem;
        gap: 0.4rem;
    }

    .admin-donut {
        display: grid;
        grid-template-columns: minmax(140px, 1fr) minmax(0, 1.15fr);
        gap: 1rem 1.25rem;
        align-items: center;
        min-height: 220px;
    }
    .admin-donut__chart {
        position: relative;
        height: 200px;
        max-width: 200px;
        margin-inline: auto;
    }
    .admin-donut__chart canvas {
        width: 100% !important;
        height: 100% !important;
    }
    .admin-donut__center {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        text-align: center;
    }
    .admin-donut__total {
        font-size: 1.35rem;
        font-weight: 800;
        color: #002B2B;
        line-height: 1.1;
        letter-spacing: -0.02em;
    }
    .admin-donut__label {
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--orora-gray);
        margin-top: 0.15rem;
    }
    .admin-donut__legend {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
    }
    .admin-donut__item {
        display: grid;
        grid-template-columns: 0.65rem 1fr auto;
        align-items: center;
        gap: 0.55rem;
        font-size: 0.8125rem;
    }
    .admin-donut__swatch {
        width: 0.65rem;
        height: 0.65rem;
        border-radius: 999px;
        flex-shrink: 0;
    }
    .admin-donut__name {
        color: #374151;
        line-height: 1.35;
        min-width: 0;
    }
    .admin-donut__value {
        font-weight: 700;
        color: #002B2B;
        font-variant-numeric: tabular-nums;
    }
    @media (max-width: 520px) {
        .admin-donut {
            grid-template-columns: 1fr;
        }
        .admin-donut__legend {
            padding-top: 0.25rem;
        }
    }

    .admin-inbox-link {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--orora-sidebar);
        text-decoration: none;
    }
    .admin-inbox-link:hover { text-decoration: underline; }
</style>
