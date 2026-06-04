# Multi-tenancy (MySQL)

Orora uses [stancl/tenancy](https://tenancyforlaravel.com/) with **one MySQL database per tenant**.

## Architecture

| Layer | Database | Purpose |
|-------|----------|---------|
| **Central** | `orora` (configurable) | Tenants, domains, cache, queues, sessions |
| **Tenant** | `tenant{id}` | Per-tenant data (users, etc.) |

Tenants are identified by **domain** (e.g. `acme.localhost`).

## Setup

1. Create the central MySQL database:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS orora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

2. Configure `.env` (already set for MySQL):

```
DB_CONNECTION=mysql
DB_DATABASE=orora
DB_USERNAME=root
DB_PASSWORD=your_password
```

3. Run central migrations:

```bash
php artisan migrate
```

4. Seed the comprehensive demo account (linked farms, animals, sales, milk, health, breeding, etc.):

```bash
php artisan migrate
php artisan orora:seed-demo --fresh
```

Sign in on the central app at `http://127.0.0.1:8000/` with:

- **Email:** `demo@ororafarm.rw`
- **Password:** `password`

Historical demo data spans **2020-01-01 → 2026-06-01** with at least **25 records per module** (configurable via `.env`).

| Variable | Default |
|----------|---------|
| `DEMO_DATE_START` | `2020-01-01` |
| `DEMO_DATE_END` | `2026-06-01` |
| `DEMO_MIN_RECORDS` | `25` |
| `DEMO_ANIMAL_COUNT` | `120` |
| `DEMO_FARM_COUNT` | `4` |

To re-seed without deleting the tenant, set `DEMO_SEED_FORCE=true` in `.env` and run `php artisan db:seed`.

5. Add tenant domains to `/etc/hosts`:

```
127.0.0.1 localhost
127.0.0.1 demo.localhost
127.0.0.1 acme.localhost
```

6. Start the app:

```bash
php artisan serve
```

- **Central app:** http://localhost:8000 — manage tenants
- **Tenant app:** http://demo.localhost:8000 — login, register, and dashboard

## Authentication (landing page)

The **login screen is the landing page** at `/`:

| URL | Purpose |
|-----|---------|
| `/` | Sign in (landing page) |
| `/register` | Create account → dashboard |
| `/dashboard` | Module hub (after login) |

**Main URL** (`http://localhost:8000`) — each **new registration** gets its own tenant database (`tenant{id}`). The signed-in session stores `tenant_id` so the dashboard only shows that account's data.

`DEFAULT_TENANT_ID=demo` is only a **legacy login fallback** for emails that are not in `tenant_accounts` (e.g. old demo users).

Tenant domains (e.g. `http://demo.localhost:8000`) use domain-based tenancy; each domain has its own user database.

**Admin** (manage tenants): `http://localhost:8000/admin/tenants`

Users are stored in each tenant's database (`tenant{id}.users`).

Dashboard modules are configured in `config/dashboard.php` — set `enabled` to `true` and add routes when you build each module.

## Creating tenants

Use the central UI at `/tenants` or Tinker:

```php
$tenant = App\Models\Tenant::create(['id' => 'acme', 'name' => 'Acme Corp']);
$tenant->domains()->create(['domain' => 'acme.localhost']);
```

This automatically creates database `tenantacme` and runs migrations in `database/migrations/tenant/`.

## Tenant migrations

Add migrations under `database/migrations/tenant/`. They run when a tenant is created and via:

```bash
php artisan tenants:migrate
```

## Production (custom domain)

The landing app (sign-in, register, dashboard via session `tenant_id`) only runs on hosts listed in **`CENTRAL_DOMAINS`** (see `config/tenancy.php`). Any other host is treated as a **tenant domain** and must exist in the central `domains` table.

If you see `TenantCouldNotBeIdentifiedOnDomainException` on your public URL, add that host to `.env`:

```
APP_URL=https://ororafarm.com
CENTRAL_DOMAINS=127.0.0.1,localhost,ororafarm.com,www.ororafarm.com
```

Then clear config cache on the server:

```bash
php artisan config:clear
```

Use `www` and apex consistently in DNS and in `CENTRAL_DOMAINS` (include both if users can hit either).

## Key files

- `app/Models/Tenant.php` — tenant model
- `config/tenancy.php` — tenancy configuration
- `routes/web.php` — central (landlord) routes
- `routes/tenant.php` — tenant routes
- `app/Providers/TenancyServiceProvider.php` — events (create/delete DB)
