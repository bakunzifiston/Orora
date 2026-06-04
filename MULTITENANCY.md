# Multi-tenancy (MySQL)

Orora uses [stancl/tenancy](https://tenancyforlaravel.com/) with **one MySQL database per farm account**, on **one public website URL**.

## How it works (single domain)

| What | Where |
|------|--------|
| **Public site** | One domain, e.g. `https://ororafarm.com` |
| **Sign up / sign in** | `/register`, `/` on that domain |
| **Central catalog** | DB `orora` — `tenants`, `tenant_accounts` (email → tenant), sessions |
| **Each farmer’s data** | Separate DB `tenant{id}` — users, farms, animals, sales, milk, etc. |

Farmers do **not** get their own subdomain. Everyone uses **ororafarm.com**; after login, the session points at **their** database.

```
ororafarm.com/register  →  creates tenant + tenant{id} DB  →  dashboard
ororafarm.com/login     →  loads that farmer’s DB by email
```

Default `.env` (no extra domains):

```env
APP_URL=https://ororafarm.com
TENANCY_DOMAIN_ROUTES=false
```

`APP_URL` **must** include `https://` (or `http://`). A value like `ororafarm.com` alone causes `Invalid URI: Scheme is malformed` when running `php artisan tenants:migrate`.

## Setup

1. Create the central MySQL database:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS orora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

2. Configure `.env` (MySQL + `APP_URL` above).

3. Central migrations:

```bash
php artisan migrate
```

4. Optional demo data:

```bash
php artisan orora:seed-demo --fresh
```

Sign in at your site URL with:

- **Email:** `demo@ororafarm.rw`
- **Password:** `password`

5. Local dev:

```bash
php artisan serve
```

Open `http://127.0.0.1:8000` — same single-domain behaviour.

## Authentication

| URL | Purpose |
|-----|---------|
| `/` | Sign in |
| `/register` | New farmer account → own `tenant{id}` database |
| `/dashboard` | Modules (after login) |

Each **registration** creates a new tenant row and database. Login uses `tenant_accounts` + session `tenant_id` to open the correct DB.

`DEFAULT_TENANT_ID=demo` is only a fallback for legacy demo emails without a `tenant_accounts` row.

**Admin:** `/admin/tenants` — list/delete tenants (manual provisioning; domain field hidden in single-domain mode).

## Production deploy

```bash
php artisan migrate
php artisan tenants:migrate
# or: php artisan orora:migrate
php artisan config:clear
```

Do **not** set `TENANCY_DOMAIN_ROUTES=true` unless you intentionally use per-tenant subdomains (advanced; not needed for ororafarm.com).

If you see `TenantCouldNotBeIdentifiedOnDomainException`, deploy the latest code, set `TENANCY_DOMAIN_ROUTES=false`, and run `php artisan config:clear`.

## cPanel / shared hosting (registration CREATE DATABASE denied)

Hosts like cPanel use a MySQL user that **cannot** run `CREATE DATABASE` via SQL. Registration fails with:

`Access denied for user '…'@'localhost' to database 'tenant…'`

**Fix:** use cPanel’s API to create databases (included in this app when `TENANCY_CPANEL_HOST` is set).

1. In cPanel → **Security** → **Manage API Tokens** → create a token with database permissions.
2. In `.env` on the server:

```env
DB_DATABASE=sandycyi_orora
# Optional override; default is sandycyi_tenant from DB_DATABASE:
# TENANCY_DATABASE_PREFIX=sandycyi_tenant

TENANCY_CPANEL_HOST=https://your-server-hostname:2083
TENANCY_CPANEL_USERNAME=sandycyi
TENANCY_CPANEL_API_TOKEN=your_api_token_here
```

3. `php artisan config:clear`
4. Try **Register** again — the app creates `sandycyi_tenant{id}` via cPanel, then runs tenant migrations.

Local dev (root MySQL): leave `TENANCY_CPANEL_*` unset; the app uses normal `CREATE DATABASE`.

## Tenant migrations

Migrations for farm data live in `database/migrations/tenant/`. They run when a tenant is created (register or admin) and via:

```bash
php artisan tenants:migrate
```

## Key files

- `app/Services/TenantAccountService.php` — register, login, session tenant
- `routes/web.php` — all app routes (single domain)
- `routes/tenant.php` — only loaded if `TENANCY_DOMAIN_ROUTES=true`
- `config/tenancy.php` — `enable_domain_routes`, `default_tenant_id`
