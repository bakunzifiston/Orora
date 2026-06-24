<style>
    .admin-app .dash-welcome,
    .admin-app .dash-page-header .dash-welcome {
        color: var(--orora-sidebar);
        margin-bottom: 0.35rem;
    }
    .admin-app .dash-panel-title,
    .admin-app .dash-panel-head .dash-panel-title {
        color: var(--orora-sidebar);
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
    }
    .admin-app .dash-panel-title::before,
    .admin-app .dash-panel-head .dash-panel-title::before {
        content: '';
        width: 3px;
        height: 1rem;
        border-radius: 9999px;
        background: linear-gradient(180deg, var(--orora-button) 0%, rgba(164, 212, 0, 0.45) 100%);
        flex-shrink: 0;
    }
    .admin-app .dash-data-table thead {
        background: linear-gradient(180deg, rgba(0, 43, 43, 0.06) 0%, rgba(0, 43, 43, 0.03) 100%);
    }
    .admin-app .dash-data-table th {
        color: var(--orora-sidebar);
        font-weight: 700;
    }
    .dash-panel--flush {
        padding: 0;
        overflow: hidden;
    }
    .dash-panel--flush .dash-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f0f1f4;
    }
    .dash-panel--flush .dash-panel-head .dash-panel-title {
        margin: 0;
    }
    .dash-data-table-wrap {
        overflow-x: auto;
    }
    .dash-data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
    .dash-data-table thead {
        background: #f8f9fb;
    }
    .dash-data-table th {
        padding: 0.65rem 1.15rem;
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--orora-gray);
        text-align: left;
        border-bottom: 1px solid #e8eaef;
        white-space: nowrap;
    }
    .dash-data-table th.dash-data-table__num,
    .dash-data-table th.dash-data-table__action {
        text-align: right;
    }
    .dash-data-table td.dash-data-table__action {
        text-align: right;
        white-space: nowrap;
    }
    .dash-data-table__view {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.35rem 0.75rem;
        border-radius: 0.45rem;
        border: 1px solid rgba(164, 212, 0, 0.45);
        background: rgba(164, 212, 0, 0.12);
        color: var(--orora-sidebar);
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.15s, border-color 0.15s, color 0.15s;
    }
    .dash-data-table__view:hover {
        background: var(--orora-button);
        border-color: var(--orora-button);
        color: var(--orora-black);
    }
    .dash-data-table td {
        padding: 0.9rem 1.15rem;
        border-bottom: 1px solid #f0f1f4;
        vertical-align: middle;
        color: #111;
    }
    .dash-data-table tbody tr:last-child td {
        border-bottom: none;
    }
    .dash-data-table tbody tr:hover {
        background: #fafbfc;
    }
    .dash-data-table__primary {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        min-width: 0;
    }
    .dash-data-table__title-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .dash-data-table__link {
        font-weight: 600;
        color: #111;
        text-decoration: none;
        line-height: 1.3;
    }
    .dash-data-table__link:hover {
        color: var(--orora-sidebar);
    }
    .dash-data-table__text {
        font-weight: 600;
        color: #111;
    }
    .dash-data-table__meta {
        font-size: 0.75rem;
        color: var(--orora-gray);
        line-height: 1.35;
    }
    .dash-data-table__muted {
        color: #6b7280;
        font-size: 0.8125rem;
    }
    .dash-data-table__num {
        text-align: right;
        font-variant-numeric: tabular-nums;
        font-weight: 600;
        color: #374151;
        white-space: nowrap;
    }
    .dash-data-table__chip {
        display: inline-block;
        padding: 0.2rem 0.5rem;
        border-radius: 0.375rem;
        background: #f0f1f4;
        font-size: 0.6875rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        color: #4b5563;
        letter-spacing: 0.01em;
    }
    .dash-data-table__badge {
        display: inline-flex;
        align-items: center;
        padding: 0.15rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 600;
        line-height: 1.2;
        text-transform: capitalize;
    }
    .dash-data-table__badge--active {
        background: rgba(164, 212, 0, 0.18);
        color: #3d5a00;
    }
    .dash-data-table__badge--pending {
        background: #fff7ed;
        color: #c2410c;
    }
    .dash-data-table__badge--suspended,
    .dash-data-table__badge--inactive {
        background: #f3f4f6;
        color: #6b7280;
    }
    .dash-data-table__empty {
        padding: 2rem 1.25rem;
        text-align: center;
        color: var(--orora-gray);
        font-size: 0.9375rem;
    }
    .dash-data-table-panel .dash-pagination {
        padding: 0.85rem 1.15rem 1rem;
        border-top: 1px solid #f0f1f4;
        margin-top: 0;
    }
</style>
