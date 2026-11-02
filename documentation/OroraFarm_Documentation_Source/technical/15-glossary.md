# Glossary

| Term | Meaning |
|---|---|
| Tenant | One customer workspace. A row in `tenants`; all tenant data carries its `tenant_id`. |
| Single-database tenancy | The mode used here (`TENANCY_SINGLE_DATABASE=true`): one database, rows separated by `tenant_id`, not one database per tenant. |
| `TenantContext` | Static service (`app/Services/TenantContext.php`) holding the current tenant id for the request. |
| `BelongsToTenant` | Eloquent trait that adds a global `tenant` scope and fills `tenant_id` on create. |
| Global scope | An Eloquent query constraint applied automatically to every query of a model. |
| Central connection | The database connection used for platform tables such as tenants, admin users and releases. |
| Guard | A Laravel authentication driver. `web` serves workspace users, `admin` serves platform administrators. |
| `module.access` | Middleware alias for `EnsureModuleAccess`, which maps a route name to a module and action and checks the user's permissions. |
| `workspace.admin` | Middleware alias for `EnsureWorkspaceAdmin`, restricting routes to workspace admins. |
| FormRequest | A Laravel class holding validation (and authorization) rules for a request. |
| Observer | A class reacting to Eloquent model events (created, updated, deleted); used for finance postings and sale handling. |
| Service class | A class in `app/Services` containing business logic called by controllers. |
| Blade | Laravel's server-side HTML template engine used for all views. |
| Vite | Front-end build tool that compiles CSS and JavaScript into `public/build`. |
| Mermaid | Text-based diagram language used in this documentation. |
| ERD | Entity relationship diagram. |
| FK | Foreign key: a column referencing another table's primary key. |
| Ledger | The double-entry finance tables (accounts, transactions and lines) written by `FinancePostingService`. |
| Posting | Creating ledger entries for a business event such as a completed sale or a paid expense. |
| Reversal | A ledger transaction that cancels an earlier posting with opposite entries. |
| Migration | A versioned PHP file that creates or alters database tables. |
| Seeder | A class that inserts initial or demo data. |
| Artisan | Laravel's command-line tool (`php artisan ...`). |
| Queue | Deferred job processing. Configured with the `database` driver but no queued jobs exist at present. |
| Scheduler | Laravel's cron-driven task runner. No tasks are scheduled in `routes/console.php` at present. |
| Technical debt | Known shortcuts or gaps listed in chapter 14. |
