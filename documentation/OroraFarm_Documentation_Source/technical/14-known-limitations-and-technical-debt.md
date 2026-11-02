# Known Limitations and Technical Debt

Each entry is evidence-based (file references are relative to the repository root). **Severity** reflects impact on correctness, security or operability: High, Medium, Low. Text under "Recommendation" is a suggestion, not an existing feature.

## Summary table

| ID | Area | Finding | Severity |
|---|---|---|---|
| S-1 | Security | No password reset, e-mail verification or 2FA | High |
| S-2 | Security/privacy | Public trace endpoints expose any tenant's animal health history by sequential id | High |
| F-1 | Finance | Completed sales cannot be cancelled, refunded or corrected | High |
| S-3 | Security | `exists:` validation rules are not tenant-scoped | Medium |
| S-4 | Security | Raw query without tenant filter in finance overview | Medium |
| S-5 | Security | Tenant scope fails open when no tenant context is set | Medium |
| S-6 | Security | Public forms and registration are not rate limited | Medium |
| S-7 | Security | Attachments served from the public disk without authentication | Medium |
| S-8 | Deployment | `.env.example` ships `APP_DEBUG=true`; secure cookie flag unset | Medium |
| F-2 | Finance | Bank account (1100) is never used because of a payment-method string mismatch | Medium |
| O-1 | Operations | `finance:backfill` ignores `--tenant` and is not tenant-aware | Medium |
| P-1 | Performance | Schema introspection queries on every scoped query and permission check | Medium |
| P-2 | Performance | Dashboard computes everything live per page view | Medium |
| Q-1 | Quality | Critical modules have no automated tests; tests need MySQL | Medium |
| B-1 | Business logic | Credit limits not enforced | Medium |
| B-2 | Business logic | Movements, certificates and egg collections do not drive stock or status | Medium |
| B-3 | Business logic | Flock sale/mortality can floor the count at 0 without error | Medium |
| N-1 | Notifications | No outbound channel, no scheduler, no queue usage | Medium |
| B-4 | Business logic | Animal status changes are one-way | Low |
| B-5 | Business logic | Abattoir return validation gaps | Low |
| S-9 | Security | `is_super_admin` does not gate most admin actions | Low |
| S-10 | Security | CSV export formula injection; raw HTML for learning content | Low |
| D-1 | Debt | Legacy `MilkSale*`/`Sale` classes with no entry points | Low |
| D-2 | Debt | Very large classes and duplicated logic | Low |
| D-3 | Debt | Non-transactional multi-step writes in controllers | Low |
| D-4 | Debt | Duplicated system account rows for 4250 | Low |
| D-5 | Debt | Mixed reference-type conventions in stock ledgers | Low |
| U-1 | Usability | Species resolution defaults to cattle with no active farm | Low |

## Security

### S-1 No password reset, e-mail verification or 2FA (High)

- Evidence: `resources/views/auth/login.blade.php:67` renders `Forgot password?` as a disabled span titled "Coming soon"; no password-reset routes in `routes/web.php`; `User` does not implement `MustVerifyEmail`; no 2FA code anywhere. `config/auth.php` brokers and the `password_reset_tokens` table (users migrations) are unused.
- Impact: a user who forgets a password depends on a workspace admin (or, for admins, the CLI). A workspace's only admin who is locked out cannot recover by themselves. Anyone can register with any e-mail address (no verification).
- Recommendation: implement token-based reset via a mail provider (requires configuring `MAIL_*`; currently `log`), verify e-mail at registration, consider 2FA for platform admins.

### S-2 Public traceability exposes all animals (High)

- Evidence: `app/Services/Marketplace/AnimalTraceService.php` `findByTag()` and `resolve()` use `withoutGlobalScope('tenant')`; routes `/trace`, `/trace/{animal}`, `/trace/{animal}/pdf` have only `throttle:30,1` (`routes/marketplace.php`).
- Impact: for any animal id (sequential integers) or tag number, the farm name/registration, vaccinations, treatments, health records, movements and certificates are returned to unauthenticated visitors. Tag search returns animals across all tenants.
- Recommendation: add an explicit per-animal or per-farm "publicly traceable" flag, use unguessable public identifiers, and limit shown fields.

### S-3 `exists:` rules not tenant scoped (Medium)

- Evidence: about 70 `exists:` rules under `app/Http` (e.g. `MovementController::rules()`, `CertificateController::rules()`, `AbattoirDispatchController::store`, `AnimalRequest` `farm_id`, `EggCollectionRequest` `farm_id`). Only a few use `Rule::exists(...)->where(...)`, and those filter by farm, not by tenant.
- Impact: a foreign tenant's record id passes validation and is stored; validation messages act as an existence oracle. Reads remain scoped, so data is not displayed.
- Recommendation: central helper `Rule::exists($table, 'id')->where('tenant_id', TenantContext::id())` (or make the `BelongsToTenant` scope apply to a custom rule).

### S-4 Cross-tenant count in Finance overview (Medium)

- Evidence: `app/Services/Finance/FinanceReportService.php` `transactionCount()` uses `DB::table('finance_transactions')` without `tenant_id`.
- Impact: the "transaction count" figure on the Finance overview and dashboard includes other tenants' transactions. `MilkOverviewAnalyticsService` also uses `DB::table('animals'/'livestock')` but only for ids obtained from scoped queries.
- Recommendation: use the Eloquent model (`FinanceTransaction::query()`) or add the tenant filter; add a test.

### S-5 Tenant scope fails open (Medium)

- Evidence: `app/Models/Concerns/BelongsToTenant.php` returns without constraint when `TenantContext::id()` is null; `creating` hook then also leaves `tenant_id` null.
- Impact: code that runs without a request-derived tenant (console commands such as `finance:backfill`, seeders, any future queued job) reads all tenants' rows and writes unowned rows.
- Recommendation: throw (or constrain to `tenant_id IS NULL`) when no context is active outside an explicit `TenantContext::run()` / `withoutGlobalScope` call.

### S-6 No rate limiting on public writes (Medium)

- Evidence: only `LoginRequest` (5 attempts) and `/trace*` (`throttle:30,1`) are limited; `POST /register`, `POST /contact`, `POST /shop/{listing}/inquiry` have no throttle or CAPTCHA (`routes/web.php`, `routes/marketplace.php`).
- Impact: automated tenant creation (each registration creates a tenant and user), contact spam, and in-app notification spam to sellers.
- Recommendation: `throttle` middleware plus CAPTCHA/honeypot on those routes.

### S-7 Attachments on the public disk (Medium)

- Evidence: treatment, vaccination, deworming, vet visit, mortality, expense, disease and pregnancy-check attachments and animal photos use `Storage::disk('public')` (e.g. `app/Http/Controllers/TreatmentController.php:144`, `ExpenseService::storeAttachment`).
- Impact: files are reachable through `/storage/...` with no authentication or tenant check if the URL leaks; file names are random but paths include the record id.
- Recommendation: store on a private disk and stream through an authorised controller route.

### S-8 Deployment defaults (Medium)

- Evidence: `.env.example` has `APP_ENV=local`, `APP_DEBUG=true`, no `SESSION_SECURE_COOKIE`; `LOG_LEVEL=debug`.
- Recommendation: production checklist in chapter 12; consider failing `orora:install` when `APP_ENV=production` and `APP_DEBUG=true`.

### S-9 Admin role granularity (Low)

- Evidence: `AdminUser::isSuperAdmin()` is checked only in `PlatformReleaseController` publish/delete and `MarketplaceListingAdminRequest::authorize`; tenant deletion (`TenantController::destroy`) and account purge (`PlatformUserController::destroy`) check nothing beyond `auth:admin`. All admins created by the CLI are super admins, so the gap is latent.

### S-10 Output and export hygiene (Low)

- `resources/views/marketplace/learning/show.blade.php:77` prints `{!! $post->content !!}` unescaped (content is admin/seeder authored; the learning routes currently redirect to `/`).
- CSV exports (`app/Services/Export/*`) do not neutralise cells beginning with `=`, `+`, `-`, `@`.

## Finance and business logic

### F-1 Completed sales cannot be reversed (High)

- Evidence: `SaleTransactionService::cancel()` rejects `completed` and `cancelled`; `SaleTransactionController::destroy` allows only draft/cancelled; `sale_status` value `refunded` (`config/modules.php:202`) is never set. The observer branch that reverses a completed sale (`SaleTransactionObserver`) is therefore unreachable from the UI.
- Impact: a wrongly completed sale (animal marked `Sold`, flock count reduced, milk deducted, ledger posted) can only be corrected by direct database edits.
- Recommendation: implement a cancel/refund flow that reverses stock, animal status and ledger entries.

### F-2 Bank account never used (Medium)

- Evidence: `config/finance.php` `bank_payment_methods` = `bank_transfer`, `mobile_money`, `cheque`, `card`; accepted payment methods (`config/modules.php:616`, used by `SalePaymentRequest` and `ExpenseRequest`) = `Cash`, `Mobile money`, `Bank transfer`, `Cheque`, `Other`. `FinanceAccountResolver::cashAccount` uses strict `in_array`.
- Impact: every payment and expense posts against account 1000 (cash); account 1100 (bank) balances and the cash/bank split are wrong.
- Recommendation: normalise payment-method values (e.g. slugs) in one place.

### F-3 Other finance limitations (Low)

- Finance periods are auto-created monthly and cannot be closed from the UI (`FinancePeriodService`, `FinanceModuleController`).
- Payments may exceed the sale total (`SaleTransactionService::addPayment` floors `remaining_balance` at 0 but does not reject overpayment).
- No egg or meat inventory; egg sale completion has no stock effect.
- Tax is a single `tax_amount` field; no tax configuration.

### O-1 `finance:backfill` (Medium)

- Evidence: `app/Console/Commands/FinanceBackfillCommand.php` signature `finance:backfill {--tenant=}` but `handle()` never reads the option; it iterates every tenant's sales/expenses with no tenant context, so created `FinanceTransaction` rows get `tenant_id = null` (see S-5), and `FinancePeriodService` may create or reuse periods across tenants. No dry-run mode.
- Recommendation: loop `Tenant::all()` with `TenantContext::run()`, honour `--tenant`, add `--dry-run`.

### B-1 Credit limits informational (Medium)

- Evidence: `credit_limit` is read only by `CustomerModuleController` (filter) and `DashboardAnalyticsService::alerts` ("Credit limit exceeded"); `SaleTransactionService` never compares balances to limits.

### B-2 Record-only modules (Medium)

- `MovementController::store` only creates a `Movement`; it does not change `farm_id`, status or flock count and does not validate `quantity` against flock size. `CertificateController` stores metadata only (no file upload or PDF). `EggCollectionService` has no stock concept.

### B-3 Flock count floors silently (Medium)

- Evidence: `FlockService::adjust` computes `max(0, current_count + quantity)`. `SaleTransactionService::assertItemMatchesType` checks `qty <= current_count` when the item is **added**, but `completeFlockBirdSale` does not re-check, so concurrent mortalities or other sales can make the final count wrong without an error.

### B-4 One-way animal status (Low)

- Mortality, sale and abattoir dispatch overwrite `lifecycle_status`/`health_status`; editing or deleting the source record does not restore the previous values (only flock counts are reversed).

### B-5 Abattoir return validation (Low)

- `AbattoirDispatchController::storeReturn` accepts any `exists:animals,id` (not limited to the dispatch's animals) and flips the dispatch to `returned` after the first return.

### Other business-logic notes

- The vaccination "due" alert counts every vaccination with `next_due_date <= today + 30 days`, including long-past dates and animals that were revaccinated later (`DashboardAnalyticsService::alerts`).
- `ExpenseService::syncFromRequest` uses `firstOrFail` on the category code: a missing category aborts the whole save.
- Pregnancy-check and birth attachments are stored before the DB transaction (`BreedingService`) and are not removed on failure.

## Notifications and automation

### N-1 No outbound notifications, scheduler or queue use (Medium)

- Evidence: `routes/console.php` has no schedule; no `app/Jobs`, `app/Mail`, `app/Events`, `app/Listeners`; notifications use only the `database` channel (`app/Notifications/*`).
- Impact: reminders (vaccinations, pregnancy checks, low stock, overdue feedings) are only visible on the dashboard; sellers learn of marketplace inquiries only inside the app (`buyer_email` is optional).
- Recommendation: add scheduled reminder commands and a mail channel; document the cron/worker setup (chapter 12).

## Performance and scalability

### P-1 Schema introspection per query (Medium)

- Evidence: `BelongsToTenant` calls `Schema::hasColumn()` in the global scope and in `creating`; `WorkspaceAccessService::schemaReady()` calls `Schema::hasTable/hasColumn` (invoked from `isWorkspaceAdmin`, `isActive`, `can`, `canAccessRoute`, `syncPermissions`). Each call can issue an information-schema query.
- Impact: dozens of extra metadata queries per request, growing with the number of queries and menu items.
- Recommendation: remove these guards now that migrations are mandatory, or cache the result per process.

### P-2 Live dashboard (Medium)

- `DashboardAnalyticsService` (1198 lines) runs many aggregate queries per page view (alerts, charts, KPI strips) with no caching apart from milk cost (`file` store, 5-10 minutes). `MilkCostPerLitreService` uses the file cache while the app cache is the `central` database store.

### Other

- Sessions, cache and jobs live in the main MySQL database; sessions and cache rows expire lazily.
- Per-date code generators (`ORDER BY code DESC LIMIT 1` then +1) are not atomic; unique `(tenant_id, code)` indexes (migration `2026_06_04_190000`) turn a race into a unique-violation error rather than a duplicate.
- List pages paginate (15 per page) but several `Livestock::...->get()` / `Farm::...->get()` option lists are unbounded.

## Quality and maintainability

### Q-1 Test coverage (Medium)

- 17 feature test files (about 99 tests), no `tests/Unit` directory although `phpunit.xml` declares the suite. Tests require MySQL (`tests/TenantTestCase.php`) and skip when unavailable, so CI without MySQL silently runs almost nothing. No tests for finance posting, sale lifecycle, milk sessions/storage, breeding, medicine inventory, permissions or tenant isolation. No CI configuration in the repository.

### D-1 Dead code (Low)

- `app/Services/MilkSaleService.php` and models `MilkSale`, `MilkSaleItem`, `MilkSalePayment`, `MilkSaleLog` are not referenced by any controller or route; `Sale` is not referenced at all; `Central\Auth\AdminLoginController` implements only logout and `AdminLoginRequest` is unused for login. `config/dashboard.php` is an empty legacy placeholder.

### D-2 Large classes (Low)

- `DashboardAnalyticsService` (1198 lines), `AnimalCsvImporter` (768), `FeedCalculatorService` (748), `Central\AdminDashboardController` (580). Repeated generator code (`generateCode`/`generateSaleCode`/...) is duplicated across at least eight services.

### D-3 Non-transactional controller flows (Low)

- `MortalityController::store/update/destroy` and `ExpenseController::store` perform several writes without a wrapping transaction (`TreatmentController` and `DewormingController` do wrap them). Observers can fail after the parent row is saved.

### D-4 Duplicate system account rows (Low)

- `database/migrations/2026_09_03_120000_add_multi_species_poultry_support.php` inserts the egg revenue account `4250` once per existing tenant with `is_system = true`; because `FinanceAccount` exposes all `is_system` rows to every tenant, `FinanceAccount::byCode('4250')` finds several rows (a read-only check on the development database showed it present for most tenants and absent from the shared null-tenant catalog). Reports group by code so totals are unaffected, but the intent (one shared system row) is not met.

### D-5 Ledger reference types (Low)

- Medicine and milk movements store `reference_type` as `Class::class`; feed movements use `getMorphClass()` (same value without a morph map). Unify before introducing a morph map.

### U-1 Species default (Low)

- With several farms and none active, `SpeciesProfile::key()` returns `cattle`, so poultry-only tenants see cattle navigation until a farm is selected.

## Configuration and documentation gaps

- `README.md` is the unmodified Laravel README; install instructions live in `MULTITENANCY.md` and `orora:install` output.
- `.env.example` still uses `APP_NAME=Laravel` and example mail address; `config/demo.php` defaults the demo password to `password`.
- `public/build` is git-ignored, so each deployment depends on a manual asset upload.
- Two multi-tenancy modes coexist (single-database row-level and stancl database-per-tenant); only single-database is exercised by tests and by the documented deployment.
