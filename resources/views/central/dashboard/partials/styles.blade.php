<style>
    .admin-dash.farm-dash {
        gap: 1rem;
    }

    .admin-dash__header {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem 1.5rem;
        margin-bottom: 0.25rem;
    }
    .admin-dash__title {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--farm-ink, #0f172a);
    }
    .admin-dash__period {
        margin: 0.25rem 0 0;
        font-size: 0.8125rem;
        color: var(--farm-muted, #64748b);
    }
    .admin-dash__toolbar {
        margin-bottom: 0;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.85rem;
    }
    .admin-dash__toolbar .admin-dash__header-text,
    .admin-dash__toolbar .dash-ops-toolbar__brand {
        flex: 0 0 auto;
        min-width: 0;
    }
    .admin-dash__toolbar .farms-page__filters {
        justify-content: flex-start;
        width: 100%;
    }

    .admin-dash .farm-kpi:not(a) {
        cursor: default;
    }
    .admin-dash .farm-kpi:not(a):hover {
        transform: none;
        box-shadow: none;
        border-color: var(--farm-border);
    }
    .admin-dash a.farm-kpi {
        cursor: pointer;
    }

    .admin-map-panel {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        min-height: 0;
    }
    .admin-farms-map {
        width: calc(100% + 2.4rem);
        margin: 0 -1.2rem -1.25rem;
        height: 300px;
        border: 0;
        border-top: 1px solid var(--farm-border, #e5e7eb);
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
        color: #3f5200;
        text-decoration: underline;
    }
    .admin-farm-popup__meta {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0.2rem 0 0;
        line-height: 1.35;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 0.5rem;
        padding: 0;
    }
    .leaflet-popup-content {
        margin: 0.65rem 0.75rem;
        line-height: 1.35;
    }
    .leaflet-popup-tip {
        box-shadow: none;
    }

    .admin-chart-ref__canvas {
        height: 210px;
    }

    .admin-donut {
        display: grid;
        grid-template-columns: minmax(120px, 1fr) minmax(0, 1.15fr);
        gap: 0.85rem 1rem;
        align-items: center;
        min-height: 180px;
    }
    .admin-donut--compact {
        grid-template-columns: 1fr;
        gap: 0.75rem;
        min-height: 0;
    }
    .admin-donut--compact .admin-donut__chart {
        height: 150px;
        max-width: 150px;
    }
    .admin-donut--compact .admin-donut__legend {
        max-height: 110px;
        overflow-y: auto;
    }
    .admin-donut__chart {
        position: relative;
        height: 180px;
        max-width: 180px;
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
        font-size: 1.25rem;
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
        color: var(--farm-muted, #64748b);
        margin-top: 0.15rem;
    }
    .admin-donut__legend {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }
    .admin-donut__item {
        display: grid;
        grid-template-columns: 0.65rem 1fr auto;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
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

    .admin-inbox-link {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--orora-sidebar, #002B2B);
        text-decoration: none;
        white-space: nowrap;
    }
    .admin-inbox-link:hover {
        text-decoration: underline;
    }

    .admin-dash .farm-activity {
        max-height: 320px;
    }

    @media (max-width: 900px) {
        .admin-dash__header {
            flex-direction: column;
            align-items: stretch;
        }
        .admin-dash__toolbar .farms-page__filters {
            justify-content: flex-start;
        }
        .admin-donut {
            grid-template-columns: 1fr;
        }
    }
</style>
