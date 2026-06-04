# Multi-tenancy (MySQL)

Orora uses **one MySQL database** for everything. Each farmer account is a **tenant** row; all farms, animals, sales, etc. include a **`tenant_id`** column so data stays isolated.

## Architecture

| What | Where |
|------|--------|
| **Public site** | One URL, e.g. `https://ororafarm.com` |
| **Catalog** | `tenants`, `tenant_accounts` (email → tenant) |
| **Farmer data** | Same database — `users`, `farms`, `animals`, … with `tenant_id` |

Registration **does not** create a new MySQL database (works on cPanel shared hosting).

```
/register → tenants row + users row (tenant_id) → dashboard
/login    → tenant_accounts finds tenant_id → session → scoped queries
```

## Setup

1. Create one MySQL database (e.g. `sandycyi_orora` on cPanel).

2. `.env`:

```env
DB_DATABASE=sandycyi_orora
APP_URL=https://ororafarm.com
TENANCY_SINGLE_DATABASE=true
TENANCY_DOMAIN_ROUTES=false
```

3. Migrate (central + all app tables — required for `/register`):

```bash
php artisan orora:install
```

Or: `php artisan migrate --force`

If registration fails with **Table 'users' doesn't exist**, the app migrations were not run yet — run the command above after deploying the latest code.

## Frontend assets (Vite)

The login/dashboard UI needs compiled assets in `public/build/` (including `manifest.json`).

On your computer (with Node.js):

```bash
npm ci
npm run build
```

Upload the entire **`public/build/`** directory to the server (cPanel File Manager or FTP). Without this folder you get `Vite manifest not found`; the app can fall back to basic styles on auth pages only.

4. Optional demo:

```bash
php artisan orora:seed-demo --fresh
```

## Authentication

| URL | Purpose |
|-----|---------|
| `/` | Sign in |
| `/register` | New account → new `tenants.id` + scoped data |
| `/dashboard` | Modules |

## How isolation works

- `TenantContext` — current `tenant_id` from session/cookies
- `BelongsToTenant` — global scope on farm models + auto-fill `tenant_id` on create
- `TenantAccountService` — login/register wiring

## Key files

- `app/Services/TenantContext.php`
- `app/Models/Concerns/BelongsToTenant.php`
- `app/Services/TenantAccountService.php`
- `database/migrations/tenant/` — app table schemas (loaded into the same DB)
- `database/migrations/2026_06_03_120000_add_tenant_id_to_application_tables.php`

## Legacy: separate database per tenant

Set `TENANCY_SINGLE_DATABASE=false` only if you run Stancl database-per-tenant on a VPS with `CREATE DATABASE` permission. Not for typical cPanel hosting.
