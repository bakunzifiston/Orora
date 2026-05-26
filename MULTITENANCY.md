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

4. (Optional) Seed a demo tenant (`demo.localhost`):

```bash
php artisan db:seed
```

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

**Main URL** (`http://localhost:8000`) uses the default tenant from `.env`:

```
DEFAULT_TENANT_ID=demo
```

Tenant domains (e.g. `http://demo.localhost:8000`) use the same layout; each domain has its own user database.

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

## Key files

- `app/Models/Tenant.php` — tenant model
- `config/tenancy.php` — tenancy configuration
- `routes/web.php` — central (landlord) routes
- `routes/tenant.php` — tenant routes
- `app/Providers/TenancyServiceProvider.php` — events (create/delete DB)
