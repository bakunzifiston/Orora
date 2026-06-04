<style>
    :root {
        --orora-black: #000000;
        --orora-surface: #ffffff;
        --orora-button: #A4D400;
        --orora-gray: #808080;
        --orora-gray-light: #cccccc;
        --orora-main-bg: #f0f1f4;
        --orora-sidebar: #002B2B;
        --orora-sidebar-active: rgba(164, 212, 0, 0.15);
        --orora-sidebar-border: rgba(255, 255, 255, 0.1);
    }
    .dash-app { display: flex; min-height: 100vh; background: var(--orora-main-bg); font-family: inherit; }
    .dash-sidebar {
        width: 260px; flex-shrink: 0; background: var(--orora-sidebar); color: var(--orora-gray-light);
        display: flex; flex-direction: column; border-right: 1px solid var(--orora-sidebar-border);
        position: sticky; top: 0; overflow-x: hidden; min-height: 100vh; height: 100vh;
    }
    .dash-sidebar::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 120px;
        background: radial-gradient(ellipse at 50% 0%, rgba(164, 212, 0, 0.1) 0%, transparent 70%);
        pointer-events: none;
    }
    .dash-sidebar::after {
        content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 80px;
        background: radial-gradient(ellipse at 50% 100%, rgba(0, 0, 0, 0.2) 0%, transparent 70%);
        pointer-events: none;
    }
    .dash-sidebar-inner {
        position: relative; z-index: 1; display: flex; flex-direction: column;
        flex: 1; min-height: 0; height: 100%; padding: 1.25rem 0 1rem;
    }
    .dash-sidebar-footer {
        flex-shrink: 0; margin-top: auto; padding: 0.75rem 0.75rem 0;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        background: linear-gradient(0deg, rgba(0, 0, 0, 0.15) 0%, transparent 100%);
    }
    .dash-logo-wrap {
        padding: 1.25rem 1rem 1.15rem;
        margin: 0 0.75rem 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.06) 0%, rgba(255, 255, 255, 0.01) 55%, transparent 100%);
        border-radius: 0.75rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
    }
    .dash-brand {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        text-decoration: none;
        transition: opacity 0.2s ease, transform 0.2s ease;
    }
    .dash-brand:hover { opacity: 0.95; }
    .dash-brand__mark {
        flex-shrink: 0;
        width: 2.75rem;
        height: 2.75rem;
        display: block;
    }
    .dash-brand__mark svg { width: 100%; height: 100%; display: block; }
    .dash-brand__text {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        min-width: 0;
    }
    .dash-brand__name {
        font-size: 1.125rem;
        font-weight: 700;
        letter-spacing: 0.22em;
        color: #ffffff;
        line-height: 1;
    }
    .dash-brand__rule {
        display: block;
        width: 2rem;
        height: 2px;
        border-radius: 9999px;
        background: linear-gradient(90deg, #A4D400 0%, rgba(164, 212, 0, 0.35) 100%);
    }
    .dash-brand__tagline {
        font-size: 0.5625rem;
        font-weight: 500;
        letter-spacing: 0.28em;
        text-transform: uppercase;
        color: #8fa3a3;
        line-height: 1;
    }
    .dash-nav { flex: 1; min-height: 0; padding: 0 0.75rem; overflow-y: auto; }
    .dash-nav-group { margin-bottom: 0.35rem; }
    .dash-nav-group--solo { margin-bottom: 0.65rem; }
    .dash-nav-group__label {
        font-size: 0.625rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: #7a9494;
        padding: 0.85rem 1rem 0.35rem;
        line-height: 1.2;
    }
    .dash-nav-group--solo .dash-nav-group__label { display: none; }
    .dash-nav a, .dash-nav span {
        display: flex; align-items: center; gap: 0.75rem; padding: 0.65rem 1rem; border-radius: 0.5rem;
        font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: background 0.15s, color 0.15s;
        margin-bottom: 0.15rem;
    }
    .dash-nav a { color: var(--orora-gray-light); }
    .dash-nav a:hover { background: rgba(255, 255, 255, 0.05); color: #fff; }
    .dash-nav a.active { background: var(--orora-sidebar-active); color: #fff; border: 1px solid rgba(164, 212, 0, 0.2); }
    .dash-nav span.disabled { color: var(--orora-gray); opacity: 0.7; cursor: not-allowed; }
    .dash-nav-icon { width: 1.25rem; height: 1.25rem; flex-shrink: 0; opacity: 0.85; }
    .dash-nav a.active .dash-nav-icon { color: var(--orora-button); opacity: 1; }
    .dash-user {
        margin: 0 0 0.65rem; padding: 0.85rem 0.75rem; border-radius: 0.5rem;
        background: rgba(255, 255, 255, 0.05); border: 1px solid var(--orora-sidebar-border);
        display: flex; align-items: center; gap: 0.75rem;
        text-decoration: none; transition: background 0.15s, border-color 0.15s;
    }
    .dash-user:hover {
        background: rgba(164, 212, 0, 0.1); border-color: rgba(164, 212, 0, 0.35);
    }
    .dash-user.is-active {
        background: var(--orora-sidebar-active);
        border-color: rgba(164, 212, 0, 0.2);
    }
    .dash-user-info { min-width: 0; flex: 1; }
    .dash-user-avatar {
        width: 2.5rem; height: 2.5rem; flex-shrink: 0; border-radius: 9999px; background: var(--orora-button);
        color: var(--orora-black); display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 0.875rem;
    }
    .dash-user-name {
        font-size: 0.875rem; font-weight: 600; color: #fff;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .dash-user-role {
        font-size: 0.6875rem; color: var(--orora-gray);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        margin-top: 0.125rem;
    }
    .dash-logout-form { margin: 0; padding: 0 0.75rem; }
    .dash-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
    .dash-topbar {
        background: var(--orora-surface); border-bottom: 1px solid #e5e7eb; padding: 0.85rem 1.5rem;
        display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
    }
    .dash-search {
        flex: 1; max-width: 320px; display: flex; align-items: center; gap: 0.5rem;
        background: var(--orora-main-bg); border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.5rem 0.85rem;
    }
    .dash-search input {
        border: none; background: transparent; outline: none; font-size: 0.875rem; width: 100%; color: #111;
    }
    .dash-topbar-actions { display: flex; align-items: center; gap: 0.75rem; }
    .dash-icon-btn {
        width: 2.25rem; height: 2.25rem; border-radius: 0.5rem; border: 1px solid #e5e7eb;
        background: #fff; display: flex; align-items: center; justify-content: center; color: #374151;
        position: relative; cursor: pointer;
    }
    .dash-icon-btn .dot {
        position: absolute; top: 6px; right: 6px; width: 7px; height: 7px; border-radius: 9999px;
        background: var(--orora-button); border: 1px solid #fff;
    }
    .dash-content { flex: 1; padding: 1.5rem; overflow-y: auto; }
    .dash-welcome { font-size: 1.5rem; font-weight: 700; color: #111; margin-bottom: 1.25rem; }
    .dash-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
    @media (max-width: 640px) { .dash-stats { grid-template-columns: 1fr; } .dash-sidebar { display: none; } }
    .dash-stat-card {
        background: #fff; border-radius: 0.75rem; border: 1px solid #e5e7eb; padding: 1.15rem 1.4rem;
        display: flex; justify-content: space-between; align-items: flex-start;
        min-width: 0;
    }
    a.dash-stat-card {
        text-decoration: none;
        color: inherit;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    a.dash-stat-card:hover {
        border-color: rgba(164, 212, 0, 0.45);
        box-shadow: 0 2px 8px rgba(0, 43, 43, 0.06);
    }
    .dash-stat-label { font-size: 0.75rem; color: var(--orora-gray); font-weight: 500; }
    .dash-stat-value { font-size: 1.5rem; font-weight: 700; color: #111; margin-top: 0.25rem; }
    .dash-stat-value.accent { color: var(--orora-button); }
    .dash-stat-value.alert { color: #dc2626; }
    .dash-stat-icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.5rem;
        background: var(--orora-sidebar);
        border: 1px solid rgba(164, 212, 0, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--orora-button);
        flex-shrink: 0;
    }
    .dash-stat-icon .dash-nav-icon {
        width: 1.125rem;
        height: 1.125rem;
        opacity: 1;
    }
    .dash-grid { display: grid; grid-template-columns: 1fr 340px; gap: 1.25rem; }
    @media (max-width: 1100px) { .dash-grid { grid-template-columns: 1fr; } }
    .dash-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-top: 1.25rem; }
    @media (max-width: 900px) { .dash-grid-2 { grid-template-columns: 1fr; } }
    .dash-panel {
        background: #fff; border-radius: 0.75rem; border: 1px solid #e5e7eb; padding: 1.25rem;
    }
    .dash-panel-title { font-size: 0.9375rem; font-weight: 600; color: #111; margin-bottom: 1rem; }
    .dash-map {
        height: 220px; border-radius: 0.5rem; background: linear-gradient(135deg, #e8eaef 0%, #d1d5db 100%);
        position: relative; overflow: hidden;
    }
    .dash-map-pin {
        position: absolute; width: 12px; height: 12px; background: var(--orora-button);
        border: 2px solid #fff; border-radius: 9999px; box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
    .dash-donut { display: flex; align-items: center; gap: 1.5rem; }
    .dash-donut-chart {
        width: 100px; height: 100px; border-radius: 9999px;
        background: conic-gradient(var(--orora-button) 0 55%, #e5e7eb 55% 80%, #9ca3af 80% 100%);
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .dash-donut-chart::after {
        content: '55%'; position: absolute; width: 64px; height: 64px; background: #fff; border-radius: 9999px;
        display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: #111;
    }
    .dash-legend { font-size: 0.75rem; color: var(--orora-gray); }
    .dash-legend span { display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.35rem; }
    .dash-legend i { width: 8px; height: 8px; border-radius: 2px; display: inline-block; }
    .dash-module-card {
        border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1rem; margin-bottom: 0.75rem;
    }
    .dash-module-card:last-child { margin-bottom: 0; }
    .dash-badge-green { background: rgba(164, 212, 0, 0.2); color: #3d5a00; font-size: 0.6875rem; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; }
    .dash-badge-orange { background: #fff7ed; color: #c2410c; font-size: 0.6875rem; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; }
    .dash-alert-item {
        display: flex; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f3f4f6;
        font-size: 0.8125rem;
    }
    .dash-alert-item:last-child { border-bottom: none; }
    .dash-gauge {
        width: 120px; height: 120px; margin: 0 auto 1rem; border-radius: 9999px;
        background: conic-gradient(var(--orora-button) 0 94%, #e5e7eb 94% 100%);
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .dash-gauge::after {
        content: '94'; font-size: 1.25rem; font-weight: 700; color: #111;
        width: 88px; height: 88px; background: #fff; border-radius: 9999px;
        display: flex; align-items: center; justify-content: center;
    }
    .dash-logout {
        display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        width: 100%; padding: 0.6rem 1rem; font-size: 0.8125rem; font-weight: 600;
        background: rgba(255, 255, 255, 0.04); border: 1px solid var(--orora-sidebar-border);
        border-radius: 0.5rem; cursor: pointer; color: var(--orora-gray-light);
        transition: background 0.15s, border-color 0.15s, color 0.15s;
    }
    .dash-logout:hover {
        background: rgba(164, 212, 0, 0.12); border-color: rgba(164, 212, 0, 0.45); color: #fff;
    }
    .dash-topbar-profile {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.35rem 0.35rem 0.35rem 0.5rem;
        border: 1px solid #e5e7eb; border-radius: 0.65rem; background: #fff;
    }
    .dash-topbar-profile__info {
        display: flex; align-items: center; gap: 0.65rem;
        text-decoration: none; color: inherit; border-radius: 0.45rem;
        transition: background 0.15s;
    }
    .dash-topbar-profile__info:hover { background: var(--orora-main-bg); }
    .dash-topbar-profile__avatar {
        width: 2.25rem; height: 2.25rem; border-radius: 9999px; background: var(--orora-button);
        color: var(--orora-black); font-size: 0.75rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
    }
    .dash-topbar-profile__text { display: flex; flex-direction: column; line-height: 1.2; }
    .dash-topbar-profile__name { font-size: 0.8125rem; font-weight: 600; color: #111; }
    .dash-topbar-profile__role { font-size: 0.6875rem; color: var(--orora-gray); }
    .dash-topbar-logout-form { margin: 0; }
    .dash-topbar-logout {
        padding: 0.45rem 0.85rem; font-size: 0.8125rem; font-weight: 600;
        color: #111; background: var(--orora-main-bg); border: 1px solid #e5e7eb;
        border-radius: 0.45rem; cursor: pointer; transition: background 0.15s, border-color 0.15s;
    }
    .dash-topbar-logout:hover {
        background: var(--orora-button); border-color: var(--orora-button); color: var(--orora-black);
    }
    @media (max-width: 768px) {
        .dash-topbar-profile__text { display: none; }
        .dash-topbar-logout { padding: 0.45rem 0.65rem; }
    }
    .dash-page-header {
        display: flex; align-items: flex-start; justify-content: space-between;
        gap: 1rem; margin-bottom: 1.25rem; flex-wrap: wrap;
    }
    .dash-page-header__actions {
        display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;
    }
    .dash-table-wrap { overflow-x: auto; }
    .dash-table {
        width: 100%; border-collapse: collapse; font-size: 0.875rem;
    }
    .dash-table th, .dash-table td {
        padding: 0.75rem 0.85rem; text-align: left; border-bottom: 1px solid #f0f1f4;
    }
    .dash-table th {
        font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
        letter-spacing: 0.04em; color: var(--orora-gray);
    }
    .dash-table tbody tr:hover { background: #fafafa; }
    .dash-table-actions {
        display: flex; align-items: center; gap: 0.65rem; justify-content: flex-end;
    }
    .dash-table-actions a {
        font-size: 0.8125rem; font-weight: 600; color: #111; text-decoration: none;
    }
    .dash-table-actions a:hover { color: var(--orora-button); }
    .dash-table-actions form { margin: 0; }
    .dash-table-actions button {
        font-size: 0.8125rem; font-weight: 600; color: #b91c1c; background: none;
        border: none; cursor: pointer; padding: 0;
    }
    .dash-table-actions button:hover { text-decoration: underline; }
    .dash-badge {
        display: inline-block; padding: 0.2rem 0.55rem; font-size: 0.75rem;
        font-weight: 600; border-radius: 9999px; background: #f3f4f6; color: #374151;
    }
    .dash-sale-status--draft {
        background: #fff7ed;
        color: #c2410c;
    }
    .dash-sale-status--confirmed {
        background: #eff6ff;
        color: #1d4ed8;
    }
    .dash-sale-status--completed {
        background: rgba(164, 212, 0, 0.22);
        color: #3d5a00;
    }
    .dash-sale-status--cancelled {
        background: #fef2f2;
        color: #b91c1c;
    }
    .dash-sale-status--refunded {
        background: #f5f3ff;
        color: #6d28d9;
    }
    .dash-sale-status--default {
        background: #f3f4f6;
        color: #374151;
    }
    .dash-sale-payment--unpaid {
        background: #fff7ed;
        color: #c2410c;
    }
    .dash-sale-payment--partial {
        background: #fef9c3;
        color: #a16207;
    }
    .dash-sale-payment--paid {
        background: rgba(164, 212, 0, 0.22);
        color: #3d5a00;
    }
    .dash-sale-payment--overdue {
        background: #fef2f2;
        color: #b91c1c;
    }
    .dash-sale-payment--default {
        background: #f3f4f6;
        color: #374151;
    }
    .dash-module-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.75rem;
    }
    .dash-module-card {
        display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
        padding: 1rem 0.75rem; background: #fff; border: 1px solid #e5e7eb;
        border-radius: 0.65rem; text-decoration: none; color: #111;
        font-size: 0.8125rem; font-weight: 600; transition: border-color 0.15s, box-shadow 0.15s;
    }
    .dash-module-card:hover {
        border-color: var(--orora-button);
        box-shadow: 0 2px 8px rgba(164, 212, 0, 0.2);
    }
    .dash-module-card .dash-nav-icon { color: var(--orora-sidebar); width: 1.5rem; height: 1.5rem; }
    .dash-empty { color: var(--orora-gray); font-size: 0.9375rem; margin: 0; }
    .dash-empty a { color: #111; font-weight: 600; }
    .dash-pagination { margin-top: 1rem; }
    .dash-form-field select,
    .dash-form-field textarea {
        padding: 0.65rem 0.85rem; border: 1px solid #e5e7eb; border-radius: 0.5rem;
        font-size: 0.9375rem; color: #111; background: #fff; width: 100%;
        font-family: inherit;
    }
    .dash-form-field select:focus,
    .dash-form-field textarea:focus {
        outline: none; border-color: var(--orora-button);
        box-shadow: 0 0 0 3px rgba(164, 212, 0, 0.25);
    }
    .dash-back-link {
        font-size: 0.875rem; font-weight: 600; color: var(--orora-gray);
        text-decoration: none; white-space: nowrap;
    }
    .dash-back-link:hover { color: #111; }
    .dash-alert {
        padding: 0.85rem 1rem; border-radius: 0.5rem; margin-bottom: 1rem; font-size: 0.875rem;
    }
    .dash-alert--success {
        background: rgba(164, 212, 0, 0.15); border: 1px solid rgba(164, 212, 0, 0.45); color: #1a2e00;
    }
    .dash-alert--error {
        background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
    }
    .dash-alert--warning {
        background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412;
    }
    .dash-profile-panel { max-width: 720px; }
    .dash-farm-form {
        max-width: 920px;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .dash-farm-form .farm-registration,
    .dash-farm-form .livestock-registration,
    .dash-farm-form .animal-registration,
    .dash-farm-form .health-registration {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .dash-field-hint {
        font-size: 0.75rem;
        color: var(--orora-gray);
        margin: 0.35rem 0 0;
        line-height: 1.4;
    }
    .dash-photo-field { display: flex; flex-direction: column; gap: 0.75rem; }
    .dash-photo-preview {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 0.65rem;
        border: 1px solid #e5e7eb;
        background: #f3f4f6;
    }
    .dash-checkbox-group {
        border: none;
        margin: 0;
        padding: 0;
    }
    .dash-checkbox-group__legend {
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        padding: 0;
        margin-bottom: 0.35rem;
    }
    .dash-checkbox-group__hint {
        font-size: 0.8125rem;
        color: var(--orora-gray);
        margin: 0 0 0.85rem;
    }
    .dash-checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0.5rem 0.75rem;
    }
    .dash-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 0.55rem 0.65rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background: #fafafa;
        cursor: pointer;
        font-size: 0.8125rem;
        line-height: 1.35;
        transition: border-color 0.15s, background 0.15s;
    }
    .dash-checkbox:hover { border-color: #d1d5db; background: #fff; }
    .dash-checkbox:has(input:checked) {
        border-color: var(--orora-button);
        background: rgba(164, 212, 0, 0.12);
    }
    .dash-checkbox input {
        margin-top: 0.15rem;
        flex-shrink: 0;
        accent-color: var(--orora-button);
    }
    .dash-other-field { margin-top: 1rem; max-width: 420px; }
    .dash-table-cell-wrap {
        max-width: 220px;
        font-size: 0.8125rem;
        line-height: 1.4;
        color: #374151;
    }
    .dash-health-subnav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-bottom: 1.25rem;
        padding: 0.35rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
    }
    .dash-health-subnav__link {
        padding: 0.5rem 0.85rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #374151;
        text-decoration: none;
        border-radius: 0.5rem;
        transition: background 0.15s, color 0.15s;
    }
    .dash-health-subnav__link:hover { background: #f3f4f6; color: #111; }
    .dash-health-subnav__link.is-active {
        background: var(--orora-button);
        color: var(--orora-black);
    }
    .dash-health-stats {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .dash-index-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        gap: 0;
        padding: 0;
        margin-bottom: 1.25rem;
        overflow: hidden;
    }
    .dash-index-toolbar__stats {
        flex: 1 1 520px;
        min-width: 0;
        padding: 0.85rem;
    }
    .dash-index-toolbar__stats .dash-health-stats {
        margin-bottom: 0;
        gap: 0.85rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .dash-index-toolbar__stats .dash-stat-card {
        padding: 0.95rem 1.15rem;
    }
    .dash-index-toolbar__filters {
        flex: 0 0 auto;
        width: min(100%, 250px);
        padding: 0.75rem 1rem;
        border-left: 1px solid #e5e7eb;
        background: #fafafa;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .dash-index-toolbar__filters .dash-form-grid {
        grid-template-columns: 1fr;
        gap: 0.5rem;
        margin: 0;
    }
    .dash-index-toolbar__filters .dash-form-field {
        gap: 0.25rem;
    }
    .dash-index-toolbar__filters .dash-form-field label {
        font-size: 0.75rem;
    }
    .dash-index-toolbar__filters .dash-form-field select {
        padding: 0.5rem 0.65rem;
        font-size: 0.8125rem;
    }
    @media (min-width: 768px) {
        .dash-index-toolbar__stats .dash-health-stats {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    @media (min-width: 1100px) {
        .dash-index-toolbar__filters--wide {
            width: min(100%, 300px);
        }
        .dash-index-toolbar__filters--wide .dash-form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 767px) {
        .dash-index-toolbar__filters {
            width: 100%;
            border-left: none;
            border-top: 1px solid #e5e7eb;
        }
    }
    .dash-health-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }
    .dash-health-charts {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .dash-health-charts .dash-panel--wide {
        grid-column: 1 / -1;
    }
    @media (min-width: 1100px) {
        .dash-health-charts {
            grid-template-columns: 1.4fr 1fr;
        }
        .dash-health-charts .dash-panel--wide {
            grid-column: auto;
        }
    }
    .dash-chart-wrap {
        position: relative;
        height: 260px;
    }
    .dash-chart-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 260px;
    }
    .dash-health-activity,
    .dash-health-breakdown {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .dash-health-activity li,
    .dash-health-breakdown li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.65rem 0;
        border-bottom: 1px solid #f0f1f4;
        font-size: 0.875rem;
    }
    .dash-health-activity li:last-child,
    .dash-health-breakdown li:last-child { border-bottom: none; }
    .dash-health-timeline { display: flex; flex-direction: column; gap: 1rem; }
    .dash-health-timeline__item {
        display: grid;
        grid-template-columns: 110px 1fr;
        gap: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .dash-health-timeline__item:last-child { border-bottom: none; padding-bottom: 0; }
    .dash-health-timeline__date {
        display: flex;
        flex-direction: column;
        font-size: 0.8125rem;
        color: var(--orora-gray);
    }
    .dash-health-timeline__date strong { color: #111; font-size: 0.9375rem; }
    .dash-health-timeline__body {
        background: #fafafa;
        border: 1px solid #e5e7eb;
        border-radius: 0.65rem;
        padding: 1rem;
    }
    .dash-health-timeline__body h3 {
        margin: 0.35rem 0;
        font-size: 0.9375rem;
        font-weight: 600;
    }
    .dash-health-timeline__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }
    @media (max-width: 640px) {
        .dash-health-timeline__item { grid-template-columns: 1fr; }
    }
    .dash-form-section {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    .dash-form-section__head {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.1rem 1.25rem;
        background: linear-gradient(90deg, rgba(0, 43, 43, 0.06) 0%, transparent 100%);
        border-bottom: 1px solid #e5e7eb;
    }
    .dash-form-section__head--split {
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
    }
    .dash-form-section__head-main {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        flex: 1;
        min-width: 0;
    }
    .dash-form-section__number {
        flex-shrink: 0;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--orora-black);
        background: var(--orora-button);
        border-radius: 9999px;
    }
    .dash-form-section__titles { min-width: 0; }
    .dash-form-section__body { padding: 1.25rem; }
    .dash-form-section--actions {
        border-style: dashed;
        background: #fafafa;
    }
    .dash-form-section--actions .dash-form-section__body {
        padding: 1rem 1.25rem;
    }
    .dash-form-section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111;
        margin: 0 0 0.25rem;
    }
    .dash-form-section-hint {
        font-size: 0.8125rem;
        color: var(--orora-gray);
        margin: 0;
        line-height: 1.45;
    }
    .dash-required { color: #dc2626; }
    .dash-form-section-header {
        display: flex; align-items: flex-start; justify-content: space-between;
        gap: 1rem; flex-wrap: wrap; margin-bottom: 0.75rem;
    }
    .dash-btn-save--sm { padding: 0.5rem 0.9rem; font-size: 0.8125rem; }
    .dash-member-card {
        padding: 1rem; margin-bottom: 0.75rem; border: 1px solid #e5e7eb;
        border-radius: 0.5rem; background: #fafafa;
    }
    .dash-member-card__header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 0.75rem;
    }
    .dash-member-remove {
        background: none; border: none; font-size: 1.25rem; line-height: 1;
        cursor: pointer; color: #b91c1c; padding: 0 0.35rem;
    }
    .dash-profile-form { display: flex; flex-direction: column; gap: 1.25rem; }
    .dash-profile-avatar-row {
        display: flex; align-items: center; gap: 1rem;
        padding-bottom: 0.5rem; border-bottom: 1px solid #f0f1f4;
    }
    .dash-profile-avatar {
        width: 4rem; height: 4rem; border-radius: 9999px; background: var(--orora-button);
        color: var(--orora-black); font-size: 1.125rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .dash-profile-avatar-label { font-size: 0.9375rem; font-weight: 600; color: #111; margin: 0 0 0.2rem; }
    .dash-profile-avatar-hint { font-size: 0.8125rem; color: var(--orora-gray); margin: 0; }
    .dash-form-grid {
        display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;
    }
    .dash-form-field { display: flex; flex-direction: column; gap: 0.35rem; }
    .dash-form-field--full { grid-column: 1 / -1; }
    .dash-form-field label { font-size: 0.8125rem; font-weight: 600; color: #374151; }
    .dash-form-field input {
        padding: 0.65rem 0.85rem; border: 1px solid #e5e7eb; border-radius: 0.5rem;
        font-size: 0.9375rem; color: #111; background: #fff;
    }
    .dash-form-field input:focus {
        outline: none; border-color: var(--orora-button);
        box-shadow: 0 0 0 3px rgba(164, 212, 0, 0.25);
    }
    .dash-form-divider { border: none; border-top: 1px solid #e5e7eb; margin: 0; }
    .dash-form-actions { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
    .dash-btn-save {
        padding: 0.65rem 1.25rem; font-size: 0.875rem; font-weight: 600;
        background: var(--orora-button); color: var(--orora-black);
        border: none; border-radius: 0.5rem; cursor: pointer;
    }
    .dash-btn-save:hover { filter: brightness(0.95); }
    .dash-btn-cancel {
        font-size: 0.875rem; font-weight: 600; color: var(--orora-gray); text-decoration: none;
    }
    .dash-btn-cancel:hover { color: #111; }
    @media (max-width: 640px) {
        .dash-form-grid { grid-template-columns: 1fr; }
    }
    .dash-entity-grid,
    .dash-farm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
    }
    .dash-entity-card,
    .dash-farm-card {
        position: relative;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.85rem;
        overflow: hidden;
        display: flex;
        transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
    }
    .dash-entity-card:hover,
    .dash-farm-card:hover {
        border-color: rgba(164, 212, 0, 0.55);
        box-shadow: 0 8px 24px rgba(0, 43, 43, 0.08);
        transform: translateY(-1px);
    }
    .dash-entity-card__accent,
    .dash-farm-card__accent {
        width: 4px;
        flex-shrink: 0;
        background: linear-gradient(180deg, var(--orora-button) 0%, rgba(0, 43, 43, 0.85) 100%);
    }
    .dash-entity-card__body,
    .dash-farm-card__body {
        flex: 1;
        min-width: 0;
        padding: 1.1rem 1.15rem 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .dash-entity-card__header,
    .dash-farm-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .dash-entity-card__title,
    .dash-farm-card__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.3;
    }
    .dash-entity-card__title a,
    .dash-farm-card__title a {
        color: #111;
        text-decoration: none;
    }
    .dash-entity-card__title a:hover,
    .dash-farm-card__title a:hover { color: #002b2b; }
    .dash-entity-card__code,
    .dash-farm-card__code {
        margin: 0.2rem 0 0;
        font-size: 0.75rem;
        color: var(--orora-gray);
    }
    .dash-entity-card__badge,
    .dash-farm-card__badge {
        flex-shrink: 0;
        display: inline-block;
        padding: 0.2rem 0.55rem;
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-radius: 9999px;
    }
    .dash-entity-card__badge--active,
    .dash-farm-card__badge--active {
        background: rgba(164, 212, 0, 0.2);
        color: #3d5a00;
    }
    .dash-entity-card__badge--pending,
    .dash-farm-card__badge--pending {
        background: #fff7ed;
        color: #c2410c;
    }
    .dash-entity-card__badge--suspended,
    .dash-farm-card__badge--suspended {
        background: #fef2f2;
        color: #b91c1c;
    }
    .dash-entity-card__badge--inactive,
    .dash-farm-card__badge--inactive {
        background: #f3f4f6;
        color: #6b7280;
    }
    .dash-entity-card__meta,
    .dash-farm-card__meta {
        margin: 0;
        display: grid;
        gap: 0.45rem;
    }
    .dash-entity-card__meta-row,
    .dash-farm-card__meta-row {
        display: grid;
        grid-template-columns: 5.5rem 1fr;
        gap: 0.5rem;
        font-size: 0.8125rem;
        line-height: 1.4;
    }
    .dash-entity-card__meta-row dt,
    .dash-farm-card__meta-row dt {
        margin: 0;
        color: var(--orora-gray);
        font-weight: 500;
    }
    .dash-entity-card__meta-row dd,
    .dash-farm-card__meta-row dd {
        margin: 0;
        color: #374151;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .dash-entity-card__stats,
    .dash-farm-card__stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        padding: 0.65rem 0.75rem;
        background: #f8faf9;
        border: 1px solid #eef1ef;
        border-radius: 0.55rem;
    }
    .dash-entity-card__stat,
    .dash-farm-card__stat {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
        text-align: center;
    }
    .dash-entity-card__stat-value,
    .dash-farm-card__stat-value {
        font-size: 1.125rem;
        font-weight: 700;
        color: #111;
    }
    .dash-entity-card__stat-label,
    .dash-farm-card__stat-label {
        font-size: 0.6875rem;
        font-weight: 600;
        color: var(--orora-gray);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .dash-entity-card__footer,
    .dash-farm-card__footer {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        flex-wrap: wrap;
        padding-top: 0.35rem;
        border-top: 1px solid #f0f1f4;
    }
    .dash-entity-card__footer form,
    .dash-farm-card__footer form { margin: 0; }
    .dash-entity-card__action,
    .dash-farm-card__action {
        font-size: 0.8125rem;
        font-weight: 600;
        text-decoration: none;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
    }
    .dash-entity-card__action--primary,
    .dash-farm-card__action--primary { color: #002b2b; }
    .dash-entity-card__action--primary:hover,
    .dash-farm-card__action--primary:hover { color: var(--orora-button); }
    .dash-entity-card__action:not(.dash-entity-card__action--primary):not(.dash-entity-card__action--danger),
    .dash-farm-card__action:not(.dash-farm-card__action--primary):not(.dash-farm-card__action--danger) { color: #374151; }
    .dash-entity-card__action:not(.dash-entity-card__action--primary):not(.dash-entity-card__action--danger):hover,
    .dash-farm-card__action:not(.dash-farm-card__action--primary):not(.dash-farm-card__action--danger):hover { color: #111; }
    .dash-entity-card__action--danger,
    .dash-farm-card__action--danger { color: #b91c1c; }
    .dash-entity-card__action--danger:hover,
    .dash-farm-card__action--danger:hover { text-decoration: underline; }
    .dash-entity-empty,
    .dash-farm-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 2.5rem 1.5rem;
        gap: 0.85rem;
    }
    .dash-entity-empty__icon,
    .dash-farm-empty__icon {
        width: 4rem;
        height: 4rem;
        border-radius: 9999px;
        background: var(--orora-sidebar);
        border: 1px solid rgba(164, 212, 0, 0.25);
        color: var(--orora-button);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .dash-entity-empty__icon .dash-nav-icon,
    .dash-farm-empty__icon .dash-nav-icon {
        width: 1.75rem;
        height: 1.75rem;
        opacity: 1;
    }
    .dash-entity-detail,
    .dash-farm-detail {
        margin: 0;
        display: grid;
        gap: 0.65rem;
    }
    .dash-entity-detail__row,
    .dash-farm-detail__row {
        display: grid;
        grid-template-columns: 8.5rem 1fr;
        gap: 0.75rem;
        font-size: 0.875rem;
        line-height: 1.45;
        padding-bottom: 0.65rem;
        border-bottom: 1px solid #f0f1f4;
    }
    .dash-entity-detail__row:last-child,
    .dash-farm-detail__row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .dash-entity-detail__row dt,
    .dash-farm-detail__row dt {
        margin: 0;
        color: var(--orora-gray);
        font-weight: 500;
    }
    .dash-entity-detail__row dd,
    .dash-farm-detail__row dd {
        margin: 0;
        color: #111;
        font-weight: 500;
    }
    @media (max-width: 640px) {
        .dash-entity-detail__row,
        .dash-farm-detail__row { grid-template-columns: 1fr; gap: 0.15rem; }
    }
    .dash-entity-card__header--media {
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: center;
        gap: 0.75rem;
    }
    .dash-entity-card__header--media .dash-entity-card__title-wrap {
        min-width: 0;
    }
    .dash-entity-card__avatar {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.65rem;
        background: var(--orora-sidebar);
        border: 1px solid rgba(164, 212, 0, 0.25);
        color: var(--orora-button);
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .dash-entity-card__avatar--photo {
        object-fit: cover;
        background: #f3f4f6;
    }
    .dash-animal-show-hero {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 1.25rem;
        align-items: start;
    }
    .dash-animal-show-hero__photo {
        width: 140px;
        height: 140px;
        border-radius: 0.85rem;
        object-fit: cover;
        border: 1px solid #e5e7eb;
        background: #f3f4f6;
    }
    .dash-animal-show-hero__photo--placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        color: var(--orora-button);
        background: var(--orora-sidebar);
        border: 1px solid rgba(164, 212, 0, 0.25);
    }
    @media (max-width: 640px) {
        .dash-animal-show-hero { grid-template-columns: 1fr; }
        .dash-animal-show-hero__photo { width: 100%; max-width: 180px; height: 180px; }
    }

    /* —— Main dashboard (home) —— */
    .dash-home-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .dash-home-subtitle {
        font-size: 0.875rem;
        color: var(--orora-gray);
        margin: 0;
    }
    .dash-home-alert-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.85rem;
        border-radius: 9999px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
        font-size: 0.8125rem;
        font-weight: 600;
    }
    .dash-home-alert-pill__dot {
        width: 8px;
        height: 8px;
        border-radius: 9999px;
        background: #dc2626;
        animation: dash-pulse 2s ease-in-out infinite;
    }
    @keyframes dash-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
    .dash-home-section { margin-bottom: 1.5rem; }
    .dash-home-section__title {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--orora-gray);
        margin: 0 0 0.75rem;
    }
    .dash-home-section__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .dash-home-section__meta {
        font-size: 0.75rem;
        color: var(--orora-gray);
        font-weight: 500;
    }
    .dash-home-stats { margin-bottom: 0; }
    .dash-home-stat { min-height: 100px; }
    .dash-home-stat__suffix {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--orora-gray);
        margin-left: 0.15rem;
    }
    .dash-home-stat__hint {
        font-size: 0.6875rem;
        color: var(--orora-gray);
        margin-top: 0.35rem;
        line-height: 1.3;
    }
    .dash-home-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 1200px) {
        .dash-home-layout { grid-template-columns: 1fr; }
    }
    .dash-home-main { display: flex; flex-direction: column; gap: 1.25rem; min-width: 0; }
    .dash-home-aside { display: flex; flex-direction: column; gap: 1.25rem; min-width: 0; }
    .dash-home-chart-panel { margin-bottom: 0; }
    .dash-home-chart-wrap { height: 240px; position: relative; }
    .dash-home-alerts {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }
    .dash-home-alert {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 0.75rem;
        padding: 0.85rem;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        background: #fafafa;
    }
    .dash-home-alert--critical { border-color: #fecaca; background: #fef2f2; }
    .dash-home-alert--warning { border-color: #fed7aa; background: #fff7ed; }
    .dash-home-alert--info { border-color: #bfdbfe; background: #eff6ff; }
    .dash-home-alert__icon {
        width: 2rem;
        height: 2rem;
        border-radius: 0.45rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #374151;
    }
    .dash-home-alert__icon .dash-nav-icon { width: 1rem; height: 1rem; }
    .dash-home-alert__content strong {
        display: block;
        font-size: 0.8125rem;
        color: #111;
        margin-bottom: 0.2rem;
    }
    .dash-home-alert__content p {
        font-size: 0.75rem;
        color: var(--orora-gray);
        margin: 0 0 0.35rem;
        line-height: 1.4;
    }
    .dash-home-alert__link {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--orora-sidebar);
        text-decoration: none;
    }
    .dash-home-alert__link:hover { color: var(--orora-button); }
    .dash-home-activity {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .dash-home-activity__item {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .dash-home-activity__item:last-child { border-bottom: none; padding-bottom: 0; }
    .dash-home-activity__icon {
        width: 2rem;
        height: 2rem;
        border-radius: 0.45rem;
        background: var(--orora-sidebar);
        border: 1px solid rgba(164, 212, 0, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--orora-button);
        flex-shrink: 0;
    }
    .dash-home-activity__icon .dash-nav-icon { width: 1rem; height: 1rem; opacity: 1; }
    .dash-home-activity__title {
        display: block;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #111;
        text-decoration: none;
        margin-bottom: 0.15rem;
    }
    a.dash-home-activity__title:hover { color: var(--orora-sidebar); }
    .dash-home-activity__meta {
        display: block;
        font-size: 0.6875rem;
        color: var(--orora-gray);
        line-height: 1.35;
    }
    .dash-home-activity__time {
        display: block;
        font-size: 0.6875rem;
        color: #9ca3af;
        margin-top: 0.2rem;
    }

    /* —— Operations dashboard layout —— */
    .dash-ops-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem 1.5rem;
        padding: 1.15rem 1.35rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        margin-bottom: 1rem;
    }
    .dash-ops-toolbar__controls {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 0.75rem 1rem;
    }
    .dash-ops-field label {
        display: block;
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--orora-gray);
        margin-bottom: 0.3rem;
    }
    .dash-ops-field select,
    .dash-ops-field input[type="date"] {
        font-size: 0.8125rem;
        padding: 0.45rem 0.65rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.45rem;
        background: #fff;
        min-width: 8.5rem;
    }
    .dash-ops-field--dates.dash-ops-field--muted input { opacity: 0.55; pointer-events: none; }
    .dash-ops-dates { display: flex; align-items: center; gap: 0.35rem; }
    .dash-ops-dates__sep { color: var(--orora-gray); font-size: 0.75rem; }
    .dash-ops-apply { padding: 0.5rem 1.1rem; font-size: 0.8125rem; }
    .dash-ops-alert-strip {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.5rem;
        padding: 0.75rem 1.15rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.65rem;
        margin-bottom: 1.25rem;
        font-size: 0.8125rem;
    }
    .dash-ops-alert-strip__item {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: #374151;
        text-decoration: none;
        padding: 0.2rem 0.45rem;
        margin: -0.2rem -0.45rem;
        border-radius: 0.35rem;
        transition: background 0.15s, color 0.15s;
    }
    .dash-ops-alert-strip__item:hover {
        background: #f3f4f6;
        color: #111;
    }
    .dash-ops-alert-strip__item.is-active {
        background: rgba(164, 212, 0, 0.18);
        color: var(--orora-sidebar);
    }
    .dash-ops-alert-strip__item strong { font-weight: 700; }
    .dash-ops-alert-strip__dot {
        width: 8px;
        height: 8px;
        border-radius: 9999px;
        flex-shrink: 0;
    }
    .dash-ops-alert-strip__item--critical .dash-ops-alert-strip__dot { background: #dc2626; }
    .dash-ops-alert-strip__item--warning .dash-ops-alert-strip__dot { background: #f59e0b; }
    .dash-ops-alert-strip__item--info .dash-ops-alert-strip__dot { background: #3b82f6; }
    .dash-ops-row { margin-bottom: 1.25rem; }
    .dash-ops-stats-4 {
        grid-template-columns: repeat(4, 1fr);
        margin-bottom: 0;
    }
    @media (max-width: 1100px) {
        .dash-ops-stats-4 { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 520px) {
        .dash-ops-stats-4 { grid-template-columns: 1fr; }
    }
    .dash-ops-kpi {
        align-items: flex-start;
        gap: 0.75rem;
    }
    .dash-ops-kpi > div:first-child {
        min-width: 0;
        flex: 1;
    }
    .dash-ops-kpi .dash-stat-value { font-size: 1.65rem; }
    .dash-ops-charts-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }
    .dash-ops-charts-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
    }
    @media (max-width: 1100px) {
        .dash-ops-charts-2,
        .dash-ops-charts-3 { grid-template-columns: 1fr; }
    }
    .dash-ops-chart-sm { height: 200px; position: relative; }
    .dash-ops-strips {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }
    @media (max-width: 900px) {
        .dash-ops-strips { grid-template-columns: 1fr; }
    }
    .dash-ops-strip {
        display: block;
        padding: 1rem 1.15rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.65rem;
        text-decoration: none;
        color: inherit;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .dash-ops-strip:hover {
        border-color: rgba(164, 212, 0, 0.45);
        box-shadow: 0 2px 8px rgba(0, 43, 43, 0.06);
    }
    .dash-ops-strip__head {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        margin-bottom: 0.75rem;
        color: var(--orora-sidebar);
    }
    .dash-ops-strip__head .dash-nav-icon { width: 1.125rem; height: 1.125rem; color: var(--orora-button); }
    .dash-ops-strip__metrics {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }
    .dash-ops-strip__label {
        display: block;
        font-size: 0.6875rem;
        color: var(--orora-gray);
    }
    .dash-ops-strip__value {
        font-size: 1.125rem;
        font-weight: 700;
        color: #111;
    }
    .dash-table--compact th,
    .dash-table--compact td { padding: 0.5rem 0.65rem; font-size: 0.8125rem; }
    .dash-ops-alert-groups { display: flex; flex-direction: column; gap: 0.85rem; max-height: 320px; overflow-y: auto; }
    .dash-ops-alert-group__title {
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--orora-gray);
        margin-bottom: 0.35rem;
    }
    .dash-ops-alert-group ul { list-style: none; margin: 0; padding: 0; }
    .dash-ops-alert-group ul > li { border-bottom: 1px solid #f3f4f6; }
    .dash-ops-alert-group ul > li:last-child { border-bottom: none; }
    .dash-ops-alert-line {
        font-size: 0.8125rem;
        padding: 0.5rem 0;
        display: grid;
        gap: 0.15rem;
    }
    .dash-ops-alert-line--link {
        text-decoration: none;
        color: inherit;
        border-radius: 0.35rem;
        margin: 0 -0.35rem;
        padding: 0.5rem 0.35rem;
        transition: background 0.15s;
    }
    .dash-ops-alert-line--link:hover {
        background: #f9fafb;
    }
    .dash-ops-alert-line--link:hover .dash-ops-alert-line__action {
        color: var(--orora-button);
    }
    .dash-ops-alert-line strong { color: #111; }
    .dash-ops-alert-line span { color: var(--orora-gray); font-size: 0.75rem; }
    .dash-ops-alert-line__action {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--orora-sidebar);
    }
    .dash-ops-alerts-filter-empty { margin-top: 0.5rem; }
    .dash-ops-alert-line--critical { border-left: 3px solid #dc2626; padding-left: 0.5rem; }
    .dash-ops-alert-line--warning { border-left: 3px solid #f59e0b; padding-left: 0.5rem; }
    .dash-ops-rank {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .dash-ops-rank li {
        display: grid;
        grid-template-columns: 1.5rem 1fr auto;
        gap: 0.5rem;
        align-items: center;
        padding: 0.55rem 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.8125rem;
    }
    .dash-ops-rank li:last-child { border-bottom: none; }
    .dash-ops-rank__n {
        font-weight: 700;
        color: var(--orora-gray);
        font-size: 0.75rem;
    }
    .dash-ops-rank__label { font-weight: 600; color: #111; text-decoration: none; }
    a.dash-ops-rank__label:hover { color: var(--orora-sidebar); }
    .dash-ops-rank__value { font-weight: 600; color: var(--orora-button); white-space: nowrap; }

    .dash-cost-details { list-style: none; }
    .dash-cost-details > summary { list-style: none; cursor: pointer; }
    .dash-cost-details > summary::-webkit-details-marker { display: none; }
    .dash-cost-details__summary {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .dash-cost-meta {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0.35rem 0 0;
        line-height: 1.4;
    }
    .dash-cost-meta--up { color: #b45309; }
    .dash-cost-meta--down { color: #047857; }
    .dash-cost-breakdown {
        margin-top: 0.85rem;
        padding-top: 0.85rem;
        border-top: 1px solid #e5e7eb;
        font-size: 0.8125rem;
    }
    .dash-cost-breakdown__title {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #374151;
    }
    .dash-cost-breakdown__list {
        display: grid;
        gap: 0.35rem;
        margin: 0 0 0.65rem;
    }
    .dash-cost-breakdown__list > div {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
    }
    .dash-cost-breakdown__list dt { color: #6b7280; margin: 0; }
    .dash-cost-breakdown__list dd { margin: 0; font-weight: 600; color: #111; }
    .dash-cost-breakdown__formula {
        margin: 0;
        color: #4b5563;
        line-height: 1.5;
        font-size: 0.75rem;
    }
</style>
