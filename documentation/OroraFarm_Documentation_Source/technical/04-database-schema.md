# Database schema

This chapter documents every table in the live MySQL database `orora` (95 base tables at the time of writing), cross-checked against `database/migrations/` and `database/migrations/tenant/`. Column types are taken from `information_schema`; they reflect what the migrations produced.

> **Note:** All business tables live in **one** database. Tenant isolation is by a `tenant_id` column (see the multi-tenancy chapter). `tenant_id` is a `varchar(255)` foreign key to `tenants.id` with `ON DELETE CASCADE` on all 76 tenant-bearing tables, and is **nullable** on most legacy tables (see chapter 06).

## Conventions

- `PK` is `id bigint unsigned auto_increment` unless stated otherwise. `created_at`/`updated_at` (`timestamp NULL`) are present on most tables and omitted from the notes only when they are the only remaining columns.
- Types are abbreviated: `u` = unsigned, `bool` = `tinyint(1)`.
- "Null" shows `Y` when the column accepts NULL. Foreign keys are listed as `column -> table.column [ON DELETE rule]`. Single-column indexes that exist only to back a foreign key are omitted.
- **Model** is the Eloquent class that maps to the table (blank = none).

## Migration folders

| Folder | Content | When it is run |
|---|---|---|
| `database/migrations/` | Central/platform tables (tenants, tenant_accounts, domains, marketplace, learning, contact, admin_users, releases, notifications, jobs, cache, sessions) plus cross-cutting migrations that add `tenant_id`, per-tenant unique constraints, workspace permissions and `users`. | Standard `php artisan migrate`. |
| `database/migrations/tenant/` | The farm-application table definitions (36 migration files) (livestock, health, feeding, milk, breeding, sales, customers, finance, employees, medicines, poultry). | In `TENANCY_SINGLE_DATABASE=true` mode `AppServiceProvider::register()` calls `loadMigrationsFrom(database_path('migrations/tenant'))`, so they run in the same database with `php artisan migrate`. In database-per-tenant mode they are intended for `tenants:migrate`. |

Both folders contain a `create_users_table` migration: the central `2026_06_04_180000_create_users_table.php` creates `users` with `tenant_id` and `unique(tenant_id, email)` (it returns early if `users` already exists), while `tenant/0001_01_01_000000_create_users_table.php` is the stock Laravel definition. The live `users` table has `tenant_id`.


## Tenancy, accounts and access

### `tenants`

Workspace (tenant) catalogue; string PK is the tenant id. Model: Tenant.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | varchar(255) |  |  |
| name | varchar(255) | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |
| data | longtext | Y |  |

### `tenant_accounts`

Login lookup: maps an e-mail address to its tenant. Model: TenantAccount.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) |  |  |
| email | varchar(255) |  |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (email)

### `domains`

stancl/tenancy domain table; unused in single-database mode (TENANCY_DOMAIN_ROUTES=false). Model: —.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | int u |  | auto_increment |
| domain | varchar(255) |  |  |
| tenant_id | varchar(255) |  |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (domain)

### `users`

Workspace users (farm owners and members); tenant-scoped. Model: User.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| name | varchar(255) |  |  |
| email | varchar(255) |  |  |
| email_verified_at | timestamp | Y |  |
| password | varchar(255) |  |  |
| role | varchar(32) |  | `'workspace_admin'` |
| is_active | bool |  | `1` |
| remember_token | varchar(100) | Y |  |
| last_login_at | timestamp | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (tenant_id, email); INDEX (tenant_id)

### `workspace_module_permissions`

Per-user module/action permission rows for non-admin members. Model: WorkspaceModulePermission.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| user_id | bigint u |  |  |
| module_key | varchar(64) |  |  |
| can_view | bool |  | `0` |
| can_create | bool |  | `0` |
| can_edit | bool |  | `0` |
| can_delete | bool |  | `0` |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `user_id` -> `users.id` [CASCADE]

**Keys/indexes:** UNIQUE (user_id, module_key)

### `admin_users`

Platform administrators (guard `admin`, central connection). Model: AdminUser.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| name | varchar(255) |  |  |
| email | varchar(255) |  |  |
| password | varchar(255) |  |  |
| is_super_admin | bool |  | `1` |
| last_login_at | timestamp | Y |  |
| remember_token | varchar(100) | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Keys/indexes:** UNIQUE (email)

### `password_reset_tokens`

Laravel password reset tokens. Model: —.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| email | varchar(255) |  |  |
| token | varchar(255) |  |  |
| created_at | timestamp | Y |  |

**Keys/indexes:** PRIMARY KEY (email)

### `sessions`

Database session store (SESSION_DRIVER=database). Model: —.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | varchar(255) |  |  |
| user_id | varchar(255) | Y |  |
| ip_address | varchar(45) | Y |  |
| user_agent | text | Y |  |
| payload | longtext |  |  |
| last_activity | int |  |  |

**Keys/indexes:** INDEX (last_activity); INDEX (user_id)

## Framework infrastructure

### `cache`

Database cache store. Model: —.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| key | varchar(255) |  |  |
| value | mediumtext |  |  |
| expiration | bigint |  |  |

**Keys/indexes:** INDEX (expiration); PRIMARY KEY (key)

### `cache_locks`

Cache atomic locks. Model: —.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| key | varchar(255) |  |  |
| owner | varchar(255) |  |  |
| expiration | bigint |  |  |

**Keys/indexes:** INDEX (expiration); PRIMARY KEY (key)

### `jobs`

Queued jobs (QUEUE_CONNECTION=database). Model: —.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| queue | varchar(255) |  |  |
| payload | longtext |  |  |
| attempts | smallint u |  |  |
| reserved_at | int u | Y |  |
| available_at | int u |  |  |
| created_at | int u |  |  |

**Keys/indexes:** INDEX (queue)

### `job_batches`

Queue job batches. Model: —.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | varchar(255) |  |  |
| name | varchar(255) |  |  |
| total_jobs | int |  |  |
| pending_jobs | int |  |  |
| failed_jobs | int |  |  |
| failed_job_ids | longtext |  |  |
| options | mediumtext | Y |  |
| cancelled_at | int | Y |  |
| created_at | int |  |  |
| finished_at | int | Y |  |

### `failed_jobs`

Failed queue jobs. Model: —.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| uuid | varchar(255) |  |  |
| connection | varchar(255) |  |  |
| queue | varchar(255) |  |  |
| payload | longtext |  |  |
| exception | longtext |  |  |
| failed_at | timestamp |  | `current_timestamp()` |

**Keys/indexes:** INDEX (connection, queue, failed_at); UNIQUE (uuid)

### `migrations`

Laravel migration ledger. Model: —.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | int u |  | auto_increment |
| migration | varchar(255) |  |  |
| batch | int |  |  |

### `notifications`

Laravel database notifications (release announcements, marketplace inquiries). Model: —.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | char(36) |  |  |
| type | varchar(255) |  |  |
| notifiable_type | varchar(255) |  |  |
| notifiable_id | bigint u |  |  |
| data | text |  |  |
| platform_release_id | bigint u | Y |  |
| read_at | timestamp | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `platform_release_id` -> `platform_releases.id` [CASCADE]

**Keys/indexes:** INDEX (notifiable_type, notifiable_id); INDEX (notifiable_type, notifiable_id, read_at); UNIQUE (platform_release_id, notifiable_type, notifiable_id)

## Farms, livestock groups and animals

### `farms`

A farm site owned by a workspace; registration, location, GPS. Model: Farm.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| name | varchar(255) |  |  |
| registration_number | varchar(255) | Y |  |
| country | varchar(255) |  | `'Rwanda'` |
| province_code | tinyint u | Y |  |
| province | varchar(255) | Y |  |
| district_code | smallint u | Y |  |
| district | varchar(255) | Y |  |
| sector | varchar(255) | Y |  |
| sector_code | varchar(20) | Y |  |
| cell | varchar(255) | Y |  |
| cell_code | int u | Y |  |
| village | varchar(255) | Y |  |
| village_code | int u | Y |  |
| latitude | decimal(10,7) | Y |  |
| longitude | decimal(10,7) | Y |  |
| farm_size_hectares | decimal(10,2) | Y |  |
| registration_date | date | Y |  |
| status | varchar(255) |  | `'active'` |
| primary_species | varchar(32) |  | `'cattle'` |
| ownership_type | varchar(255) |  | `'sole_proprietor'` |
| owner_first_name | varchar(255) | Y |  |
| owner_last_name | varchar(255) | Y |  |
| owner_national_id | varchar(255) | Y |  |
| contact_phone | varchar(255) | Y |  |
| contact_email | varchar(255) | Y |  |
| owner_emergency_contact | varchar(255) | Y |  |
| organization_name | varchar(255) | Y |  |
| tax_id | varchar(255) | Y |  |
| owner_dob | date | Y |  |
| owner_gender | varchar(255) | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (primary_species); INDEX (tenant_id); UNIQUE (tenant_id, registration_number)

### `farm_members`

Farm membership rows. Only referenced by the `Farm::members()` relation; no controller, service or view writes to it (unused). Model: FarmMember.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| first_name | varchar(255) |  |  |
| last_name | varchar(255) |  |  |
| date_of_birth | date |  |  |
| phone | varchar(255) |  |  |
| gender | varchar(255) |  |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `farm_id` -> `farms.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `livestock`

Herd/batch (livestock group) on a farm; parent of animals. Model: Livestock.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| herd_groups | longtext | Y |  |
| herd_group_other | varchar(255) | Y |  |
| livestock_types | longtext | Y |  |
| livestock_type_other | varchar(255) | Y |  |
| production_purposes | longtext | Y |  |
| production_purpose_other | varchar(255) | Y |  |
| farming_methods | longtext | Y |  |
| farming_method_other | varchar(255) | Y |  |
| feeding_methods | longtext | Y |  |
| feeding_method_other | varchar(255) | Y |  |
| name | varchar(255) | Y |  |
| breed | varchar(255) | Y |  |
| head_count | int u |  | `0` |
| status | varchar(255) |  | `'active'` |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `farm_id` -> `farms.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `animals`

Individual animal register (tag, breed, sex, parentage, status). Model: Animal.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| livestock_id | bigint u | Y |  |
| tag_number | varchar(255) |  |  |
| name | varchar(255) | Y |  |
| gender | varchar(255) |  | `'male'` |
| photo_path | varchar(255) | Y |  |
| species | varchar(255) | Y |  |
| breed | varchar(255) | Y |  |
| date_of_birth | date | Y |  |
| weight_kg | decimal(8,2) | Y |  |
| color_markings | varchar(255) | Y |  |
| acquisition_type | varchar(255) | Y |  |
| acquisition_date | date | Y |  |
| source | varchar(255) | Y |  |
| mother_tag | varchar(255) | Y |  |
| father_tag | varchar(255) | Y |  |
| health_status | varchar(255) |  | `'Healthy'` |
| production_status | varchar(255) | Y |  |
| lifecycle_status | varchar(255) |  | `'Active'` |
| current_condition | varchar(255) | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `farm_id` -> `farms.id` [CASCADE]; `livestock_id` -> `livestock.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (livestock_id, tag_number); INDEX (tenant_id)

### `movements`

Animal or flock movements (transfer, sale, death, export). Model: Movement.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| animal_id | bigint u | Y |  |
| flock_id | bigint u | Y |  |
| quantity | int u |  | `1` |
| from_farm_id | bigint u |  |  |
| to_farm_id | bigint u | Y |  |
| movement_type | varchar(255) |  |  |
| moved_on | date |  |  |
| reference | varchar(255) | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [SET NULL]; `flock_id` -> `flocks.id` [SET NULL]; `from_farm_id` -> `farms.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]; `to_farm_id` -> `farms.id` [SET NULL]

**Keys/indexes:** INDEX (tenant_id)

### `certificates`

Health/vaccination/export/ownership certificates for animals or flocks. Model: Certificate.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| animal_id | bigint u | Y |  |
| flock_id | bigint u | Y |  |
| certificate_type | varchar(255) |  |  |
| certificate_number | varchar(255) | Y |  |
| issuing_authority | varchar(255) | Y |  |
| issued_on | date |  |  |
| expires_on | date | Y |  |
| status | varchar(255) |  | `'valid'` |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [SET NULL]; `farm_id` -> `farms.id` [CASCADE]; `flock_id` -> `flocks.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

## Poultry (flocks and eggs)

### `flocks`

Poultry flock (batch) with current/initial bird counts. Model: Flock.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) |  |  |
| farm_id | bigint u |  |  |
| livestock_id | bigint u | Y |  |
| flock_code | varchar(255) |  |  |
| name | varchar(255) |  |  |
| production_type | varchar(255) |  |  |
| breed | varchar(255) | Y |  |
| source | varchar(255) | Y |  |
| placed_on | date |  |  |
| placed_count | int u |  |  |
| current_count | int u |  |  |
| male_count | int u | Y |  |
| female_count | int u | Y |  |
| house_or_pen | varchar(255) | Y |  |
| expected_end_on | date | Y |  |
| lifecycle_status | varchar(255) |  | `'Active'` |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `farm_id` -> `farms.id` [CASCADE]; `livestock_id` -> `livestock.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (farm_id, lifecycle_status); UNIQUE (tenant_id, flock_code)

### `flock_events`

Ledger of flock count changes (placement, mortality, cull, sale, transfer, adjustment). Model: FlockEvent.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) |  |  |
| flock_id | bigint u |  |  |
| event_type | varchar(255) |  |  |
| quantity | int |  |  |
| balance_after | int u |  |  |
| occurred_on | date |  |  |
| reference_type | varchar(255) | Y |  |
| reference_id | bigint u | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `flock_id` -> `flocks.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (flock_id, occurred_on)

### `egg_collections`

Daily egg collection per flock and shift. Model: EggCollection.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) |  |  |
| farm_id | bigint u |  |  |
| flock_id | bigint u |  |  |
| collection_code | varchar(255) |  |  |
| collected_on | date |  |  |
| shift | varchar(255) | Y |  |
| eggs_count | int u |  |  |
| cracked_count | int u |  | `0` |
| weight_kg | decimal(10,2) | Y |  |
| collected_by | varchar(255) | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `farm_id` -> `farms.id` [CASCADE]; `flock_id` -> `flocks.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (flock_id, collected_on, shift); UNIQUE (tenant_id, collection_code)

## Health and medicines

### `health_records`

Generic health event per animal/flock. Model: HealthRecord.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| animal_id | bigint u | Y |  |
| flock_id | bigint u | Y |  |
| record_type | varchar(255) |  |  |
| recorded_on | date |  |  |
| health_status | varchar(255) |  |  |
| title | varchar(255) | Y |  |
| treatment | varchar(255) | Y |  |
| medication | varchar(255) | Y |  |
| veterinarian | varchar(255) | Y |  |
| next_follow_up | date | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [SET NULL]; `farm_id` -> `farms.id` [CASCADE]; `flock_id` -> `flocks.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `vaccinations`

Vaccination events and schedule. Model: Vaccination.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| livestock_id | bigint u | Y |  |
| animal_id | bigint u | Y |  |
| flock_id | bigint u | Y |  |
| health_record_id | bigint u | Y |  |
| vaccine_name | varchar(255) |  |  |
| vaccine_type | varchar(255) | Y |  |
| manufacturer | varchar(255) | Y |  |
| batch_number | varchar(255) | Y |  |
| dosage | varchar(255) | Y |  |
| administration_method | varchar(255) | Y |  |
| vaccination_date | date |  |  |
| next_due_date | date | Y |  |
| status | varchar(255) |  | `'Scheduled'` |
| veterinarian_name | varchar(255) | Y |  |
| veterinary_clinic | varchar(255) | Y |  |
| administered_by | varchar(255) | Y |  |
| side_effects | text | Y |  |
| reaction_notes | text | Y |  |
| notes | text | Y |  |
| attachment_path | varchar(255) | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [SET NULL]; `farm_id` -> `farms.id` [CASCADE]; `flock_id` -> `flocks.id` [SET NULL]; `health_record_id` -> `health_records.id` [SET NULL]; `livestock_id` -> `livestock.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `treatments`

Treatment events; consume medicine stock. Model: Treatment.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| livestock_id | bigint u | Y |  |
| animal_id | bigint u | Y |  |
| flock_id | bigint u | Y |  |
| health_record_id | bigint u | Y |  |
| disease_name | varchar(255) |  |  |
| medicine_name | varchar(255) |  |  |
| medicine_id | bigint u | Y |  |
| dosage | varchar(255) | Y |  |
| quantity_used | decimal(12,2) | Y |  |
| treatment_method | varchar(255) | Y |  |
| start_date | date |  |  |
| end_date | date | Y |  |
| follow_up_date | date | Y |  |
| status | varchar(255) |  | `'Ongoing'` |
| veterinarian_name | varchar(255) | Y |  |
| symptoms | text | Y |  |
| diagnosis | text | Y |  |
| notes | text | Y |  |
| attachment_path | varchar(255) | Y |  |
| recorded_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [SET NULL]; `farm_id` -> `farms.id` [CASCADE]; `flock_id` -> `flocks.id` [SET NULL]; `health_record_id` -> `health_records.id` [SET NULL]; `livestock_id` -> `livestock.id` [SET NULL]; `medicine_id` -> `medicines.id` [SET NULL]; `recorded_by` -> `users.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `dewormings`

Deworming events; consume medicine stock. Model: Deworming.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| livestock_id | bigint u | Y |  |
| animal_id | bigint u | Y |  |
| flock_id | bigint u | Y |  |
| health_record_id | bigint u | Y |  |
| product_name | varchar(255) |  |  |
| medicine_id | bigint u | Y |  |
| quantity_used | decimal(12,2) | Y |  |
| product_type | varchar(255) | Y |  |
| active_ingredient | varchar(255) | Y |  |
| manufacturer | varchar(255) | Y |  |
| batch_number | varchar(255) | Y |  |
| dosage | varchar(255) | Y |  |
| administration_method | varchar(255) | Y |  |
| deworming_date | date |  |  |
| next_due_date | date | Y |  |
| status | varchar(255) |  | `'Scheduled'` |
| veterinarian_name | varchar(255) | Y |  |
| administered_by | varchar(255) | Y |  |
| notes | text | Y |  |
| attachment_path | varchar(255) | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [SET NULL]; `farm_id` -> `farms.id` [CASCADE]; `flock_id` -> `flocks.id` [SET NULL]; `health_record_id` -> `health_records.id` [SET NULL]; `livestock_id` -> `livestock.id` [SET NULL]; `medicine_id` -> `medicines.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (farm_id, deworming_date); INDEX (tenant_id)

### `vet_visits`

Veterinary visits. Model: VetVisit.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| livestock_id | bigint u | Y |  |
| animal_id | bigint u | Y |  |
| flock_id | bigint u | Y |  |
| health_record_id | bigint u | Y |  |
| disease_name | varchar(255) |  |  |
| medicine_name | varchar(255) |  |  |
| dosage | varchar(255) | Y |  |
| treatment_method | varchar(255) | Y |  |
| start_date | date |  |  |
| end_date | date | Y |  |
| follow_up_date | date | Y |  |
| status | varchar(255) |  | `'Ongoing'` |
| veterinarian_name | varchar(255) | Y |  |
| symptoms | text | Y |  |
| diagnosis | text | Y |  |
| notes | text | Y |  |
| attachment_path | varchar(255) | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [SET NULL]; `farm_id` -> `farms.id` [CASCADE]; `flock_id` -> `flocks.id` [SET NULL]; `health_record_id` -> `health_records.id` [SET NULL]; `livestock_id` -> `livestock.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `mortalities`

Death records and disposal. Model: Mortality.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| animal_id | bigint u | Y |  |
| deaths_count | int u |  | `1` |
| flock_id | bigint u | Y |  |
| health_record_id | bigint u | Y |  |
| death_date | date |  |  |
| cause_of_death | varchar(255) | Y |  |
| reported_by | varchar(255) | Y |  |
| veterinarian_name | varchar(255) | Y |  |
| disposal_method | varchar(255) | Y |  |
| postmortem_done | bool |  | `0` |
| notes | text | Y |  |
| attachment_path | varchar(255) | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [SET NULL]; `farm_id` -> `farms.id` [CASCADE]; `flock_id` -> `flocks.id` [SET NULL]; `health_record_id` -> `health_records.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `disease_records`

Disease cases, severity and recovery. Model: DiseaseRecord.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| disease_code | varchar(255) |  |  |
| farm_id | bigint u |  |  |
| livestock_id | bigint u | Y |  |
| animal_id | bigint u | Y |  |
| flock_id | bigint u | Y |  |
| health_record_id | bigint u | Y |  |
| disease_name | varchar(255) |  |  |
| diagnosis_date | date |  |  |
| severity_level | varchar(255) |  | `'medium'` |
| recovery_status | varchar(255) |  | `'recovering'` |
| contagious_status | varchar(255) |  | `'unknown'` |
| quarantine_required | bool |  | `0` |
| symptoms | text | Y |  |
| veterinarian_name | varchar(255) | Y |  |
| notes | text | Y |  |
| attachment_path | varchar(255) | Y |  |
| created_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [SET NULL]; `created_by` -> `users.id` [SET NULL]; `farm_id` -> `farms.id` [CASCADE]; `flock_id` -> `flocks.id` [SET NULL]; `health_record_id` -> `health_records.id` [SET NULL]; `livestock_id` -> `livestock.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (animal_id, diagnosis_date); INDEX (farm_id, diagnosis_date); UNIQUE (tenant_id, disease_code); INDEX (tenant_id)

### `medicines`

Medicine catalogue and stock on hand per farm. Model: Medicine.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| name | varchar(255) |  |  |
| category | varchar(255) | Y |  |
| active_ingredient | varchar(255) | Y |  |
| dosage_instructions | text | Y |  |
| administration_method | varchar(255) | Y |  |
| unit | varchar(255) |  | `'ml'` |
| quantity_on_hand | decimal(12,2) |  | `0.00` |
| reorder_level | decimal(12,2) | Y |  |
| expiry_date | date | Y |  |
| supplier | varchar(255) | Y |  |
| notes | text | Y |  |
| is_active | bool |  | `1` |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id, expiry_date); INDEX (tenant_id); INDEX (tenant_id, name)

### `medicine_movements`

Medicine stock ledger (purchase, adjustments, consumption). Model: MedicineMovement.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| medicine_id | bigint u |  |  |
| movement_type | varchar(255) |  |  |
| quantity | decimal(12,2) |  |  |
| unit | varchar(255) |  |  |
| balance_after | decimal(12,2) |  |  |
| reference_type | varchar(255) | Y |  |
| reference_id | bigint u | Y |  |
| notes | text | Y |  |
| moved_at | timestamp | Y |  |
| recorded_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `medicine_id` -> `medicines.id` [CASCADE]; `recorded_by` -> `users.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (medicine_id, moved_at); INDEX (reference_type, reference_id); INDEX (tenant_id)

## Feeding

### `feed_types`

Feed catalogue (unit, category, nutrient data). Model: FeedType.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| feed_supplier_id | bigint u | Y |  |
| name | varchar(255) |  |  |
| unit | varchar(255) |  | `'kg'` |
| category | varchar(255) | Y |  |
| description | text | Y |  |
| is_active | bool |  | `1` |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `feed_supplier_id` -> `feed_suppliers.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `feed_suppliers`

Feed suppliers (legacy; copied into expense_vendors by a data migration). Model: FeedSupplier.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| name | varchar(255) |  |  |
| contact_person | varchar(255) | Y |  |
| phone | varchar(255) | Y |  |
| email | varchar(255) | Y |  |
| address | varchar(255) | Y |  |
| notes | text | Y |  |
| is_active | bool |  | `1` |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `feed_inventories`

Stock level per farm and feed type (unique pair). Model: FeedInventory.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| feed_type_id | bigint u |  |  |
| quantity_on_hand | decimal(12,2) |  | `0.00` |
| storage_capacity_kg | decimal(14,2) | Y |  |
| unit | varchar(255) |  | `'kg'` |
| reorder_level | decimal(12,2) | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `farm_id` -> `farms.id` [CASCADE]; `feed_type_id` -> `feed_types.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (farm_id, feed_type_id); INDEX (tenant_id)

### `feed_inventory_movements`

Feed stock ledger. Model: FeedInventoryMovement.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| feed_inventory_id | bigint u |  |  |
| movement_type | varchar(255) |  |  |
| quantity | decimal(12,2) |  |  |
| unit | varchar(255) |  |  |
| balance_after | decimal(12,2) |  |  |
| reference_type | varchar(255) | Y |  |
| reference_id | bigint u | Y |  |
| notes | text | Y |  |
| moved_at | timestamp |  | `current_timestamp()` on update current_timestamp() |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `feed_inventory_id` -> `feed_inventories.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (reference_type, reference_id); INDEX (tenant_id)

### `feedings`

Feeding events; consumption reduces inventory. Model: Feeding.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| livestock_id | bigint u | Y |  |
| animal_id | bigint u | Y |  |
| flock_id | bigint u | Y |  |
| feed_type_id | bigint u | Y |  |
| feed_inventory_id | bigint u | Y |  |
| feeding_schedule_id | bigint u | Y |  |
| quantity | decimal(10,2) |  |  |
| unit | varchar(255) |  | `'kg'` |
| fed_on | date |  |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [SET NULL]; `farm_id` -> `farms.id` [CASCADE]; `feed_inventory_id` -> `feed_inventories.id` [SET NULL]; `feed_type_id` -> `feed_types.id` [SET NULL]; `feeding_schedule_id` -> `feeding_schedules.id` [SET NULL]; `flock_id` -> `flocks.id` [SET NULL]; `livestock_id` -> `livestock.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `feeding_schedules`

Recurring feeding plans with next due date. Model: FeedingSchedule.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| feed_type_id | bigint u |  |  |
| feed_inventory_id | bigint u | Y |  |
| livestock_id | bigint u | Y |  |
| animal_id | bigint u | Y |  |
| flock_id | bigint u | Y |  |
| quantity | decimal(10,2) |  |  |
| unit | varchar(255) |  | `'kg'` |
| frequency | varchar(255) |  | `'daily'` |
| days_of_week | longtext | Y |  |
| scheduled_time | time | Y |  |
| start_date | date |  |  |
| end_date | date | Y |  |
| next_due_date | date | Y |  |
| status | varchar(255) |  | `'active'` |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [SET NULL]; `farm_id` -> `farms.id` [CASCADE]; `feed_inventory_id` -> `feed_inventories.id` [SET NULL]; `feed_type_id` -> `feed_types.id` [CASCADE]; `flock_id` -> `flocks.id` [SET NULL]; `livestock_id` -> `livestock.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

## Milk

### `milk_sessions`

A milking session per herd, date and shift. Model: MilkSession.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| livestock_id | bigint u |  |  |
| session_code | varchar(255) |  |  |
| session_date | date |  |  |
| session_shift | varchar(255) |  |  |
| total_yield_liters | decimal(10,2) |  | `0.00` |
| number_of_animals_milked | int u |  | `0` |
| average_yield_per_animal | decimal(10,2) |  | `0.00` |
| milked_by | varchar(255) |  |  |
| milking_method | varchar(255) |  |  |
| status | varchar(255) |  | `'open'` |
| notes | text | Y |  |
| destination_storage_id | bigint u | Y |  |
| created_by | bigint u | Y |  |
| completed_at | timestamp | Y |  |
| completed_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `completed_by` -> `users.id` [SET NULL]; `created_by` -> `users.id` [SET NULL]; `destination_storage_id` -> `milk_storage.id` [SET NULL]; `farm_id` -> `farms.id` [CASCADE]; `livestock_id` -> `livestock.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (livestock_id, session_date, session_shift); INDEX (session_date, farm_id, status); INDEX (tenant_id); UNIQUE (tenant_id, session_code)

### `milk_records`

Per-animal yield within a session. Model: MilkRecord.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| milk_session_id | bigint u |  |  |
| animal_id | bigint u |  |  |
| record_code | varchar(255) |  |  |
| yield_liters | decimal(10,2) |  |  |
| milking_duration_minutes | int u | Y |  |
| lactation_stage | varchar(255) | Y |  |
| lactation_number | int u | Y |  |
| udder_condition | varchar(255) | Y |  |
| abnormal_milk | bool |  | `0` |
| abnormal_notes | text | Y |  |
| notes | text | Y |  |
| created_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [CASCADE]; `created_by` -> `users.id` [SET NULL]; `milk_session_id` -> `milk_sessions.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (animal_id); UNIQUE (milk_session_id, animal_id); INDEX (tenant_id); UNIQUE (tenant_id, record_code)

### `milk_storage`

Milk tank/container with current volume. Model: MilkStorage.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| storage_code | varchar(255) |  |  |
| container_name | varchar(255) |  |  |
| container_type | varchar(255) |  | `'bulk_tank'` |
| capacity_liters | decimal(10,2) |  |  |
| current_quantity_liters | decimal(10,2) |  | `0.00` |
| storage_temperature | decimal(5,2) | Y |  |
| storage_location | varchar(255) | Y |  |
| status | varchar(255) |  | `'available'` |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `farm_id` -> `farms.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (farm_id, status); INDEX (tenant_id); UNIQUE (tenant_id, storage_code)

### `milk_storage_movements`

Milk storage ledger (intake, sale, adjustment, spoilage, distribution). Model: MilkStorageMovement.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| milk_storage_id | bigint u |  |  |
| movement_type | varchar(255) |  |  |
| quantity_liters | decimal(10,2) |  |  |
| balance_after | decimal(10,2) |  |  |
| reference_type | varchar(255) | Y |  |
| reference_id | bigint u | Y |  |
| notes | text | Y |  |
| moved_at | timestamp |  | `current_timestamp()` on update current_timestamp() |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `milk_storage_id` -> `milk_storage.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (reference_type, reference_id); INDEX (tenant_id)

### `milk_distributions`

Where milk went (sold, calf, household, ...). Model: MilkDistribution.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| distribution_code | varchar(255) |  |  |
| distributed_at | datetime |  |  |
| quantity_liters | decimal(10,2) |  |  |
| category | varchar(255) |  |  |
| other_use | varchar(255) | Y |  |
| recipient_type | varchar(255) |  |  |
| recipient_name | varchar(255) | Y |  |
| recipient_phone | varchar(30) | Y |  |
| animal_id | bigint u | Y |  |
| milk_storage_id | bigint u | Y |  |
| storage_movement_id | bigint u | Y |  |
| sale_transaction_id | bigint u | Y |  |
| sale_item_id | bigint u | Y |  |
| notes | text | Y |  |
| recorded_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [SET NULL]; `farm_id` -> `farms.id` [CASCADE]; `milk_storage_id` -> `milk_storage.id` [SET NULL]; `recorded_by` -> `users.id` [SET NULL]; `sale_item_id` -> `sale_items.id` [SET NULL]; `sale_transaction_id` -> `sale_transactions.id` [SET NULL]; `storage_movement_id` -> `milk_storage_movements.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (category, distributed_at); UNIQUE (distribution_code); INDEX (farm_id, distributed_at); UNIQUE (sale_item_id); INDEX (tenant_id)

## Breeding

### `breeding_records`

Mating/AI cycle with expected delivery date. Model: BreedingRecord.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| female_animal_id | bigint u |  |  |
| male_animal_id | bigint u | Y |  |
| external_sire_name | varchar(255) | Y |  |
| external_sire_breed | varchar(255) | Y |  |
| external_sire_code | varchar(255) | Y |  |
| breeding_code | varchar(255) |  |  |
| breeding_date | date |  |  |
| pregnancy_check_due_on | date | Y |  |
| breeding_type | varchar(255) |  |  |
| animal_type | varchar(255) |  |  |
| heat_detection_method | varchar(255) | Y |  |
| heat_detected_date | date | Y |  |
| technician_name | varchar(255) | Y |  |
| semen_batch_number | varchar(255) | Y |  |
| semen_straw_code | varchar(255) | Y |  |
| semen_source | varchar(255) | Y |  |
| expected_calving_date | date | Y |  |
| gestation_period_days | int u | Y |  |
| breeding_status | varchar(255) |  | `'pending'` |
| notes | text | Y |  |
| created_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `created_by` -> `users.id` [SET NULL]; `farm_id` -> `farms.id` [CASCADE]; `female_animal_id` -> `animals.id` [CASCADE]; `male_animal_id` -> `animals.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (breeding_status); INDEX (breeding_status, pregnancy_check_due_on); INDEX (farm_id, breeding_date); UNIQUE (tenant_id, breeding_code); INDEX (tenant_id)

### `breeding_logs`

Audit trail of breeding cycle actions. Model: BreedingLog.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| breeding_record_id | bigint u |  |  |
| action_type | varchar(255) |  |  |
| action_by | bigint u | Y |  |
| action_date | timestamp |  | `current_timestamp()` on update current_timestamp() |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `action_by` -> `users.id` [SET NULL]; `breeding_record_id` -> `breeding_records.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (breeding_record_id, action_date); INDEX (tenant_id)

### `pregnancy_checks`

Pregnancy check results for a breeding record. Model: PregnancyCheck.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| breeding_record_id | bigint u |  |  |
| animal_id | bigint u |  |  |
| check_code | varchar(255) |  |  |
| check_date | date |  |  |
| check_method | varchar(255) |  |  |
| result | varchar(255) |  |  |
| pregnancy_age_days | int u | Y |  |
| expected_calving_date | date | Y |  |
| checked_by | varchar(255) |  |  |
| clinic_name | varchar(255) | Y |  |
| next_check_date | date | Y |  |
| notes | text | Y |  |
| attachment_path | varchar(255) | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [CASCADE]; `breeding_record_id` -> `breeding_records.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (check_date); UNIQUE (tenant_id, check_code); INDEX (tenant_id)

### `birth_records`

Birth event (one per breeding). Model: BirthRecord.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| breeding_record_id | bigint u |  |  |
| mother_animal_id | bigint u |  |  |
| birth_code | varchar(255) |  |  |
| birth_date | date |  |  |
| birth_type | varchar(255) |  |  |
| total_offspring | int u |  |  |
| alive_offspring | int u |  |  |
| stillborn_offspring | int u |  | `0` |
| birth_difficulty | varchar(255) |  |  |
| birth_weight_kg | decimal(8,2) | Y |  |
| assisted_by | varchar(255) | Y |  |
| veterinarian_name | varchar(255) | Y |  |
| mother_condition_after | varchar(255) |  |  |
| notes | text | Y |  |
| attachment_path | varchar(255) | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `breeding_record_id` -> `breeding_records.id` [CASCADE]; `mother_animal_id` -> `animals.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (birth_date); UNIQUE (breeding_record_id); UNIQUE (tenant_id, birth_code); INDEX (tenant_id)

### `offspring`

Individual offspring, optionally registered as animals. Model: Offspring.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| birth_record_id | bigint u |  |  |
| mother_animal_id | bigint u |  |  |
| father_animal_id | bigint u | Y |  |
| external_sire_name | varchar(255) | Y |  |
| animal_id | bigint u | Y |  |
| offspring_code | varchar(255) |  |  |
| gender | varchar(255) |  |  |
| birth_weight_kg | decimal(8,2) | Y |  |
| color_markings | varchar(255) | Y |  |
| health_status_at_birth | varchar(255) |  |  |
| is_registered | bool |  | `0` |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [SET NULL]; `birth_record_id` -> `birth_records.id` [CASCADE]; `father_animal_id` -> `animals.id` [SET NULL]; `mother_animal_id` -> `animals.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id); UNIQUE (tenant_id, offspring_code)

## Sales, customers and abattoir

### `sale_transactions`

Unified sale header (animal, meat, milk, egg sales). Model: SaleTransaction.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| sale_number | varchar(255) |  |  |
| sale_type | varchar(255) |  |  |
| sale_date | date |  |  |
| customer_id | bigint u | Y |  |
| pricing_method | varchar(255) | Y |  |
| subtotal_amount | decimal(15,2) |  | `0.00` |
| discount_amount | decimal(15,2) |  | `0.00` |
| tax_amount | decimal(15,2) |  | `0.00` |
| total_amount | decimal(15,2) |  | `0.00` |
| currency | varchar(255) |  | `'RWF'` |
| payment_status | varchar(255) |  | `'unpaid'` |
| sale_status | varchar(255) |  | `'draft'` |
| delivery_method | varchar(255) | Y |  |
| movement_permit_id | bigint u | Y |  |
| abattoir_dispatch_id | bigint u | Y |  |
| notes | text | Y |  |
| approved_by | varchar(255) | Y |  |
| created_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `abattoir_dispatch_id` -> `abattoir_dispatches.id` [SET NULL]; `created_by` -> `users.id` [SET NULL]; `customer_id` -> `customers.id` [SET NULL]; `farm_id` -> `farms.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (farm_id, sale_status); INDEX (sale_type, sale_date); INDEX (tenant_id); UNIQUE (tenant_id, sale_number)

### `sale_items`

Sale line items. Model: SaleItem.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| sale_transaction_id | bigint u |  |  |
| customer_id | bigint u | Y |  |
| item_type | varchar(255) |  |  |
| animal_id | bigint u | Y |  |
| flock_id | bigint u | Y |  |
| livestock_id | bigint u | Y |  |
| abattoir_return_id | bigint u | Y |  |
| milk_storage_id | bigint u | Y |  |
| description | varchar(255) |  |  |
| quantity | decimal(10,3) |  | `1.000` |
| unit | varchar(255) |  | `'head'` |
| unit_price | decimal(15,2) |  | `0.00` |
| live_weight_kg | decimal(10,2) | Y |  |
| carcass_weight_kg | decimal(10,2) | Y |  |
| price_per_kg | decimal(15,2) | Y |  |
| total_price | decimal(15,2) |  | `0.00` |
| animal_condition | varchar(255) | Y |  |
| certificate_verified | bool |  | `0` |
| permit_verified | bool |  | `0` |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `abattoir_return_id` -> `abattoir_returns.id` [SET NULL]; `animal_id` -> `animals.id` [SET NULL]; `customer_id` -> `customers.id` [SET NULL]; `flock_id` -> `flocks.id` [SET NULL]; `livestock_id` -> `livestock.id` [SET NULL]; `milk_storage_id` -> `milk_storage.id` [SET NULL]; `sale_transaction_id` -> `sale_transactions.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `sale_payments`

Payments against a sale. Model: SalePayment.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| sale_transaction_id | bigint u |  |  |
| customer_id | bigint u | Y |  |
| payment_reference | varchar(255) |  |  |
| payment_date | date |  |  |
| payment_method | varchar(255) | Y |  |
| amount_paid | decimal(15,2) |  |  |
| remaining_balance | decimal(15,2) |  | `0.00` |
| transaction_reference | varchar(255) | Y |  |
| received_by | varchar(255) | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `customer_id` -> `customers.id` [SET NULL]; `sale_transaction_id` -> `sale_transactions.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id); UNIQUE (tenant_id, payment_reference)

### `sale_documents`

Documents attached to a sale (invoice, receipt, ...). Model: SaleDocument.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| sale_transaction_id | bigint u |  |  |
| document_type | varchar(255) |  |  |
| document_number | varchar(255) | Y |  |
| file_path | varchar(255) | Y |  |
| generated_by | varchar(255) | Y |  |
| generated_at | timestamp | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `sale_transaction_id` -> `sale_transactions.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `sale_logs`

Sale audit trail. Model: SaleLog.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| sale_transaction_id | bigint u |  |  |
| action_type | varchar(255) |  |  |
| action_by | bigint u | Y |  |
| action_at | timestamp |  | `current_timestamp()` on update current_timestamp() |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `action_by` -> `users.id` [SET NULL]; `sale_transaction_id` -> `sale_transactions.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `customers`

Customer master (soft-deleted). Model: Customer.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| customer_code | varchar(255) |  |  |
| customer_type | varchar(255) |  | `'individual'` |
| display_name | varchar(255) |  |  |
| status | varchar(255) |  | `'active'` |
| trust_level | varchar(255) |  | `'new'` |
| preferred_payment_method | varchar(255) | Y |  |
| currency | varchar(255) |  | `'RWF'` |
| notes | text | Y |  |
| created_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |
| deleted_at | timestamp | Y |  |

**Foreign keys:** `created_by` -> `users.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (status, customer_type); UNIQUE (tenant_id, customer_code); INDEX (tenant_id)

### `customer_profiles`

1:1 extended customer profile. Model: CustomerProfile.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| customer_id | bigint u |  |  |
| first_name | varchar(255) | Y |  |
| last_name | varchar(255) | Y |  |
| national_id | varchar(255) | Y |  |
| date_of_birth | date | Y |  |
| gender | varchar(255) | Y |  |
| organization_name | varchar(255) | Y |  |
| registration_number | varchar(255) | Y |  |
| tax_id | varchar(255) | Y |  |
| license_number | varchar(255) | Y |  |
| license_expiry_date | date | Y |  |
| website | varchar(255) | Y |  |
| industry | varchar(255) | Y |  |
| number_of_employees | int u | Y |  |
| established_date | date | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `customer_id` -> `customers.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (customer_id); INDEX (tenant_id)

### `customer_credit`

1:1 credit limit/balance. Model: CustomerCredit.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| customer_id | bigint u |  |  |
| credit_limit | decimal(15,2) |  | `0.00` |
| outstanding_balance | decimal(15,2) |  | `0.00` |
| payment_terms | varchar(255) | Y |  |
| last_reviewed_at | date | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `customer_id` -> `customers.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (customer_id); INDEX (tenant_id)

### `customer_addresses`

Customer addresses. Model: CustomerAddress.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| customer_id | bigint u |  |  |
| address_type | varchar(255) |  | `'physical'` |
| address_label | varchar(255) | Y |  |
| country | varchar(255) | Y |  |
| province | varchar(255) | Y |  |
| district | varchar(255) | Y |  |
| sector | varchar(255) | Y |  |
| cell | varchar(255) | Y |  |
| village | varchar(255) | Y |  |
| street_address | text | Y |  |
| gps_latitude | decimal(10,7) | Y |  |
| gps_longitude | decimal(10,7) | Y |  |
| is_default | bool |  | `0` |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `customer_id` -> `customers.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `customer_contacts`

Customer contact people. Model: CustomerContact.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| customer_id | bigint u |  |  |
| contact_name | varchar(255) |  |  |
| role | varchar(255) | Y |  |
| phone | varchar(255) | Y |  |
| email | varchar(255) | Y |  |
| is_primary | bool |  | `0` |
| notes | varchar(255) | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `customer_id` -> `customers.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `customer_communications`

Customer communication log. Model: CustomerCommunication.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| customer_id | bigint u |  |  |
| communication_type | varchar(255) |  |  |
| direction | varchar(255) | Y |  |
| subject | varchar(255) | Y |  |
| summary | text |  |  |
| communication_date | datetime |  |  |
| contact_person | varchar(255) | Y |  |
| follow_up_required | bool |  | `0` |
| follow_up_date | date | Y |  |
| follow_up_notes | text | Y |  |
| logged_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `customer_id` -> `customers.id` [CASCADE]; `logged_by` -> `users.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (customer_id, communication_date); INDEX (tenant_id)

### `customer_documents`

Customer documents. Model: CustomerDocument.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| customer_id | bigint u |  |  |
| document_type | varchar(255) |  |  |
| document_name | varchar(255) |  |  |
| file_path | varchar(255) | Y |  |
| issued_date | date | Y |  |
| expiry_date | date | Y |  |
| notes | text | Y |  |
| uploaded_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `customer_id` -> `customers.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]; `uploaded_by` -> `users.id` [SET NULL]

**Keys/indexes:** INDEX (tenant_id)

### `customer_logs`

Customer audit trail. Model: CustomerLog.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| customer_id | bigint u |  |  |
| action_type | varchar(255) |  |  |
| action_by | bigint u | Y |  |
| action_at | timestamp |  | `current_timestamp()` on update current_timestamp() |
| old_values | longtext | Y |  |
| new_values | longtext | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `action_by` -> `users.id` [SET NULL]; `customer_id` -> `customers.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `abattoir_dispatches`

Dispatch of animals to an abattoir. Model: AbattoirDispatch.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u |  |  |
| dispatch_code | varchar(255) |  |  |
| dispatch_date | date |  |  |
| abattoir_name | varchar(255) |  |  |
| abattoir_location | varchar(255) | Y |  |
| contact_person | varchar(255) | Y |  |
| transport_method | varchar(255) | Y |  |
| vehicle_plate | varchar(255) | Y |  |
| driver_name | varchar(255) | Y |  |
| movement_permit_id | bigint u | Y |  |
| total_animals_dispatched | int u |  | `0` |
| expected_return_date | date | Y |  |
| dispatch_status | varchar(255) |  | `'pending'` |
| notes | text | Y |  |
| created_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `created_by` -> `users.id` [SET NULL]; `farm_id` -> `farms.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (tenant_id, dispatch_code); INDEX (tenant_id)

### `abattoir_dispatch_animals`

Animals in a dispatch. Model: AbattoirDispatchAnimal.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| abattoir_dispatch_id | bigint u |  |  |
| animal_id | bigint u |  |  |
| live_weight_kg | decimal(10,2) | Y |  |
| animal_condition | varchar(255) | Y |  |
| notes | varchar(255) | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `abattoir_dispatch_id` -> `abattoir_dispatches.id` [CASCADE]; `animal_id` -> `animals.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `abattoir_returns`

Carcass/cut returns from the abattoir. Model: AbattoirReturn.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| abattoir_dispatch_id | bigint u |  |  |
| animal_id | bigint u |  |  |
| return_date | date |  |  |
| carcass_weight_kg | decimal(10,2) | Y |  |
| dressing_percentage | decimal(5,2) | Y |  |
| cut_type | varchar(255) |  |  |
| cut_weight_kg | decimal(10,2) |  |  |
| grade | varchar(255) | Y |  |
| price_per_kg | decimal(15,2) | Y |  |
| is_sold | bool |  | `0` |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `abattoir_dispatch_id` -> `abattoir_dispatches.id` [CASCADE]; `animal_id` -> `animals.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

## Expenses and finance

### `expense_categories`

Expense categories (system rows is_system=true visible to all tenants). Model: ExpenseCategory.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| expense_group | varchar(255) |  |  |
| name | varchar(255) |  |  |
| code | varchar(255) | Y |  |
| description | text | Y |  |
| is_system | bool |  | `0` |
| is_active | bool |  | `1` |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (tenant_id, code); INDEX (tenant_id)

### `expense_vendors`

Vendors/suppliers. Model: ExpenseVendor.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| name | varchar(255) |  |  |
| contact_person | varchar(255) | Y |  |
| phone | varchar(255) | Y |  |
| email | varchar(255) | Y |  |
| notes | text | Y |  |
| is_active | bool |  | `1` |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `expenses`

Expense entries; paid expenses post to the ledger. Model: Expense.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u | Y |  |
| animal_id | bigint u | Y |  |
| flock_id | bigint u | Y |  |
| livestock_id | bigint u | Y |  |
| expense_category_id | bigint u |  |  |
| expense_vendor_id | bigint u | Y |  |
| expense_date | date |  |  |
| amount | decimal(14,2) |  |  |
| currency | varchar(255) |  | `'RWF'` |
| payment_method | varchar(255) | Y |  |
| paid_by | varchar(255) | Y |  |
| source_type | varchar(255) | Y |  |
| source_id | bigint u | Y |  |
| title | varchar(255) | Y |  |
| notes | text | Y |  |
| attachment_path | varchar(255) | Y |  |
| status | varchar(255) |  | `'paid'` |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [SET NULL]; `expense_category_id` -> `expense_categories.id` [CASCADE]; `expense_vendor_id` -> `expense_vendors.id` [SET NULL]; `farm_id` -> `farms.id` [SET NULL]; `flock_id` -> `flocks.id` [SET NULL]; `livestock_id` -> `livestock.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (expense_date); INDEX (source_type, source_id); INDEX (tenant_id)

### `finance_accounts`

Chart of accounts (system rows shared). Model: FinanceAccount.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| account_code | varchar(255) |  |  |
| account_name | varchar(255) |  |  |
| account_type | varchar(255) |  |  |
| account_subtype | varchar(255) |  |  |
| parent_account_id | bigint u | Y |  |
| source_module | varchar(255) | Y |  |
| expense_category_code | varchar(255) | Y |  |
| normal_balance | varchar(255) |  |  |
| is_system | bool |  | `1` |
| is_active | bool |  | `1` |
| description | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `parent_account_id` -> `finance_accounts.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (tenant_id, account_code); INDEX (tenant_id)

### `finance_periods`

Accounting periods (open/closed). Model: FinancePeriod.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| period_name | varchar(255) |  |  |
| period_type | varchar(255) |  | `'monthly'` |
| start_date | date |  |  |
| end_date | date |  |  |
| status | varchar(255) |  | `'open'` |
| closed_by | bigint u | Y |  |
| closed_at | timestamp | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `closed_by` -> `users.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id); UNIQUE (tenant_id, period_type, start_date, end_date)

### `finance_transactions`

Ledger transaction header (income/expense, posting_kind). Model: FinanceTransaction.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u | Y |  |
| livestock_id | bigint u | Y |  |
| transaction_code | varchar(255) |  |  |
| transaction_date | date |  |  |
| finance_period_id | bigint u |  |  |
| transaction_type | varchar(255) |  |  |
| posting_kind | varchar(255) |  | `'recognition'` |
| source_module | varchar(255) | Y |  |
| source_type | varchar(255) | Y |  |
| source_id | bigint u | Y |  |
| description | varchar(255) |  |  |
| gross_amount | decimal(15,2) |  |  |
| tax_amount | decimal(15,2) |  | `0.00` |
| net_amount | decimal(15,2) |  |  |
| currency | varchar(255) |  | `'RWF'` |
| payment_method | varchar(255) | Y |  |
| reference_number | varchar(255) | Y |  |
| is_manual | bool |  | `0` |
| is_reconciled | bool |  | `0` |
| is_reversal | bool |  | `0` |
| reversed_transaction_id | bigint u | Y |  |
| notes | text | Y |  |
| created_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `created_by` -> `users.id` [SET NULL]; `farm_id` -> `farms.id` [SET NULL]; `finance_period_id` -> `finance_periods.id` [CASCADE]; `livestock_id` -> `livestock.id` [SET NULL]; `reversed_transaction_id` -> `finance_transactions.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (source_module, source_type, source_id, posting_kind); INDEX (farm_id, livestock_id); INDEX (tenant_id); UNIQUE (tenant_id, transaction_code); INDEX (transaction_date, farm_id)

### `finance_transaction_lines`

Debit/credit lines. Model: FinanceTransactionLine.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| finance_transaction_id | bigint u |  |  |
| finance_account_id | bigint u |  |  |
| entry_type | varchar(255) |  |  |
| amount | decimal(15,2) |  |  |
| description | varchar(255) | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `finance_account_id` -> `finance_accounts.id` [CASCADE]; `finance_transaction_id` -> `finance_transactions.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `finance_transaction_logs`

Ledger audit trail. Model: FinanceTransactionLog.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| finance_transaction_id | bigint u |  |  |
| action_type | varchar(255) |  |  |
| action_by | bigint u | Y |  |
| action_at | timestamp |  | `current_timestamp()` on update current_timestamp() |
| old_values | longtext | Y |  |
| new_values | longtext | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `action_by` -> `users.id` [SET NULL]; `finance_transaction_id` -> `finance_transactions.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `finance_balance_snapshots`

Period balance snapshots per account (no Eloquent model). Model: —.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| finance_period_id | bigint u |  |  |
| farm_id | bigint u | Y |  |
| finance_account_id | bigint u |  |  |
| opening_balance | decimal(15,2) |  | `0.00` |
| total_debits | decimal(15,2) |  | `0.00` |
| total_credits | decimal(15,2) |  | `0.00` |
| closing_balance | decimal(15,2) |  | `0.00` |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `farm_id` -> `farms.id` [SET NULL]; `finance_account_id` -> `finance_accounts.id` [CASCADE]; `finance_period_id` -> `finance_periods.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id); UNIQUE (finance_period_id, farm_id, finance_account_id)

### `finance_budgets`

Budget headers (no model). Model: —.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| farm_id | bigint u | Y |  |
| budget_code | varchar(255) |  |  |
| budget_name | varchar(255) |  |  |
| period_type | varchar(255) |  |  |
| period_start | date |  |  |
| period_end | date |  |  |
| total_income_budget | decimal(15,2) |  | `0.00` |
| total_expense_budget | decimal(15,2) |  | `0.00` |
| net_budget | decimal(15,2) |  | `0.00` |
| status | varchar(255) |  | `'draft'` |
| notes | text | Y |  |
| created_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `created_by` -> `users.id` [SET NULL]; `farm_id` -> `farms.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (tenant_id, budget_code); INDEX (tenant_id)

### `finance_budget_items`

Budget lines (no model). Model: —.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| finance_budget_id | bigint u |  |  |
| finance_account_id | bigint u |  |  |
| livestock_id | bigint u | Y |  |
| budgeted_amount | decimal(15,2) |  |  |
| actual_amount | decimal(15,2) |  | `0.00` |
| variance | decimal(15,2) |  | `0.00` |
| variance_percentage | decimal(5,2) | Y |  |
| notes | varchar(255) | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `finance_account_id` -> `finance_accounts.id` [CASCADE]; `finance_budget_id` -> `finance_budgets.id` [CASCADE]; `livestock_id` -> `livestock.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `finance_tax_rates`

Tax rate definitions (no model). Model: —.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| tax_name | varchar(255) |  |  |
| tax_code | varchar(255) |  |  |
| tax_rate | decimal(5,2) |  |  |
| applies_to | varchar(255) |  |  |
| finance_account_id | bigint u |  |  |
| is_active | bool |  | `1` |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `finance_account_id` -> `finance_accounts.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id); UNIQUE (tenant_id, tax_code)

### `finance_tax_entries`

Tax entries (no model). Model: —.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| finance_transaction_id | bigint u |  |  |
| finance_tax_rate_id | bigint u |  |  |
| taxable_amount | decimal(15,2) |  |  |
| tax_rate_snapshot | decimal(5,2) |  |  |
| tax_amount | decimal(15,2) |  |  |
| tax_period | varchar(255) | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `finance_tax_rate_id` -> `finance_tax_rates.id` [CASCADE]; `finance_transaction_id` -> `finance_transactions.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

## Employees

### `employees`

Employee master (soft-deleted). Model: Employee.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| employee_code | varchar(255) |  |  |
| display_name | varchar(255) |  |  |
| status | varchar(255) |  | `'active'` |
| employment_type | varchar(255) |  | `'full_time'` |
| job_role | varchar(255) |  | `'farm_worker'` |
| primary_farm_id | bigint u | Y |  |
| hire_date | date | Y |  |
| termination_date | date | Y |  |
| notes | text | Y |  |
| created_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |
| deleted_at | timestamp | Y |  |

**Foreign keys:** `created_by` -> `users.id` [SET NULL]; `primary_farm_id` -> `farms.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (status, job_role); UNIQUE (tenant_id, employee_code); INDEX (tenant_id)

### `employee_profiles`

1:1 extended profile. Model: EmployeeProfile.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| employee_id | bigint u |  |  |
| first_name | varchar(255) |  |  |
| last_name | varchar(255) |  |  |
| national_id | varchar(255) | Y |  |
| passport_number | varchar(255) | Y |  |
| date_of_birth | date | Y |  |
| gender | varchar(255) | Y |  |
| marital_status | varchar(255) | Y |  |
| nationality | varchar(255) | Y |  |
| phone | varchar(255) | Y |  |
| email | varchar(255) | Y |  |
| photo_path | varchar(255) | Y |  |
| education_level | varchar(255) | Y |  |
| skills | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `employee_id` -> `employees.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (employee_id); INDEX (tenant_id)

### `employee_payroll`

1:1 payroll/contract data. Model: EmployeePayroll.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| employee_id | bigint u |  |  |
| contract_type | varchar(255) |  | `'permanent'` |
| contract_start | date | Y |  |
| contract_end | date | Y |  |
| base_salary | decimal(15,2) | Y |  |
| currency | varchar(255) |  | `'RWF'` |
| pay_frequency | varchar(255) |  | `'monthly'` |
| payment_method | varchar(255) | Y |  |
| bank_name | varchar(255) | Y |  |
| bank_account | varchar(255) | Y |  |
| mobile_money_number | varchar(255) | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `employee_id` -> `employees.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (employee_id); INDEX (tenant_id)

### `employee_addresses`

Employee addresses. Model: EmployeeAddress.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| employee_id | bigint u |  |  |
| address_type | varchar(255) |  | `'physical'` |
| address_label | varchar(255) | Y |  |
| country | varchar(255) | Y |  |
| province | varchar(255) | Y |  |
| district | varchar(255) | Y |  |
| sector | varchar(255) | Y |  |
| cell | varchar(255) | Y |  |
| village | varchar(255) | Y |  |
| street_address | text | Y |  |
| is_default | bool |  | `0` |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `employee_id` -> `employees.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `employee_documents`

Employee documents. Model: EmployeeDocument.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| employee_id | bigint u |  |  |
| document_type | varchar(255) |  |  |
| document_name | varchar(255) |  |  |
| file_path | varchar(255) | Y |  |
| issued_date | date | Y |  |
| expiry_date | date | Y |  |
| notes | text | Y |  |
| uploaded_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `employee_id` -> `employees.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]; `uploaded_by` -> `users.id` [SET NULL]

**Keys/indexes:** INDEX (tenant_id)

### `employee_emergency_contacts`

Emergency contacts. Model: EmployeeEmergencyContact.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| employee_id | bigint u |  |  |
| contact_name | varchar(255) |  |  |
| relationship | varchar(255) | Y |  |
| phone | varchar(255) | Y |  |
| email | varchar(255) | Y |  |
| is_primary | bool |  | `0` |
| notes | varchar(255) | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `employee_id` -> `employees.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

### `employee_farm_assignments`

Employee-to-farm assignments (unique pair). Model: EmployeeFarmAssignment.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| employee_id | bigint u |  |  |
| farm_id | bigint u |  |  |
| assignment_role | varchar(255) | Y |  |
| is_primary | bool |  | `0` |
| assigned_from | date | Y |  |
| assigned_until | date | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `employee_id` -> `employees.id` [CASCADE]; `farm_id` -> `farms.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** UNIQUE (employee_id, farm_id); INDEX (tenant_id)

### `employee_logs`

Employee audit trail. Model: EmployeeLog.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) | Y |  |
| employee_id | bigint u |  |  |
| action_type | varchar(255) |  |  |
| action_by | bigint u | Y |  |
| action_at | timestamp |  | `current_timestamp()` on update current_timestamp() |
| old_values | longtext | Y |  |
| new_values | longtext | Y |  |
| notes | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `action_by` -> `users.id` [SET NULL]; `employee_id` -> `employees.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (tenant_id)

## Marketplace, learning hub and platform

### `marketplace_categories`

Marketplace category tree (central). Model: MarketplaceCategory.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| name | varchar(255) |  |  |
| slug | varchar(255) |  |  |
| icon | varchar(255) | Y |  |
| description | text | Y |  |
| sort_order | smallint u |  | `0` |
| is_active | bool |  | `1` |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Keys/indexes:** UNIQUE (slug)

### `marketplace_listings`

Public listings (animals/products). Model: MarketplaceListing.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| tenant_id | varchar(255) |  |  |
| farm_id | bigint u | Y |  |
| animal_id | bigint u | Y |  |
| animal_species | varchar(80) | Y |  |
| animal_sex | varchar(40) | Y |  |
| category_id | bigint u |  |  |
| listing_code | varchar(255) | Y |  |
| listing_type | varchar(30) | Y |  |
| title | varchar(255) |  |  |
| slug | varchar(255) |  |  |
| description | text | Y |  |
| breed | varchar(255) | Y |  |
| age | varchar(255) | Y |  |
| weight_kg | decimal(10,2) | Y |  |
| quantity | decimal(10,3) | Y |  |
| unit | varchar(20) | Y |  |
| price | decimal(12,2) | Y |  |
| price_type | varchar(20) |  | `'fixed'` |
| currency | varchar(3) |  | `'RWF'` |
| images | longtext | Y |  |
| seller_name | varchar(255) | Y |  |
| seller_phone | varchar(255) | Y |  |
| seller_email | varchar(255) | Y |  |
| seller_type | varchar(30) | Y |  |
| location_district | varchar(255) | Y |  |
| location_sector | varchar(255) | Y |  |
| is_featured | bool |  | `0` |
| is_verified | bool |  | `0` |
| status | varchar(20) |  | `'draft'` |
| expires_at | timestamp | Y |  |
| views_count | int u |  | `0` |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `animal_id` -> `animals.id` [SET NULL]; `category_id` -> `marketplace_categories.id` [CASCADE]; `farm_id` -> `farms.id` [SET NULL]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** INDEX (farm_id, animal_id); UNIQUE (listing_code); INDEX (listing_type); INDEX (location_district); INDEX (status, created_at); INDEX (status); UNIQUE (tenant_id, slug)

### `marketplace_listing_status_changes`

Listing status audit. Model: MarketplaceListingStatusChange.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| listing_id | bigint u |  |  |
| admin_user_id | bigint u | Y |  |
| user_id | bigint u | Y |  |
| from_status | varchar(30) | Y |  |
| to_status | varchar(30) |  |  |
| note | text | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `admin_user_id` -> `admin_users.id` [SET NULL]; `listing_id` -> `marketplace_listings.id` [CASCADE]; `user_id` -> `users.id` [SET NULL]

**Keys/indexes:** INDEX (listing_id, created_at)

### `marketplace_inquiries`

Buyer inquiries. Model: MarketplaceInquiry.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| listing_id | bigint u |  |  |
| buyer_name | varchar(255) |  |  |
| buyer_email | varchar(255) | Y |  |
| buyer_location | varchar(255) | Y |  |
| buyer_phone | varchar(255) | Y |  |
| message | text |  |  |
| status | varchar(20) |  | `'new'` |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `listing_id` -> `marketplace_listings.id` [CASCADE]

### `learning_categories`

Learning hub categories. Model: LearningCategory.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| name | varchar(255) |  |  |
| slug | varchar(255) |  |  |
| icon | varchar(255) | Y |  |
| description | text | Y |  |
| sort_order | smallint u |  | `0` |
| is_active | bool |  | `1` |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Keys/indexes:** UNIQUE (slug)

### `learning_posts`

Learning hub articles/videos. Model: LearningPost.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| category_id | bigint u | Y |  |
| title | varchar(255) |  |  |
| slug | varchar(255) |  |  |
| excerpt | text | Y |  |
| content_type | varchar(20) | Y |  |
| content | longtext | Y |  |
| video_url | varchar(255) | Y |  |
| video_duration | varchar(255) | Y |  |
| pdf_path | varchar(255) | Y |  |
| pdf_pages | int u | Y |  |
| thumbnail_path | varchar(255) | Y |  |
| author_name | varchar(255) | Y |  |
| author_title | varchar(255) | Y |  |
| author_photo | varchar(255) | Y |  |
| read_time | smallint u | Y |  |
| difficulty_level | varchar(20) |  | `'beginner'` |
| language | varchar(5) |  | `'en'` |
| is_featured | bool |  | `0` |
| views_count | int u |  | `0` |
| tags | longtext | Y |  |
| is_published | bool |  | `0` |
| published_at | timestamp | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `category_id` -> `learning_categories.id` [SET NULL]

**Keys/indexes:** INDEX (content_type, is_published); INDEX (difficulty_level); INDEX (is_published, published_at); INDEX (language); UNIQUE (slug)

### `contact_messages`

Public contact-form messages. Model: ContactMessage.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| name | varchar(255) |  |  |
| email | varchar(255) |  |  |
| phone | varchar(255) | Y |  |
| subject | varchar(255) |  |  |
| inquiry_type | varchar(30) |  | `'general'` |
| message | text |  |  |
| status | varchar(20) |  | `'new'` |
| replied_at | timestamp | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

### `platform_releases`

Release notes ("What's new"). Model: PlatformRelease.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| id | bigint u |  | auto_increment |
| version | varchar(50) |  |  |
| title | varchar(255) |  |  |
| description | text |  |  |
| category | varchar(40) |  |  |
| released_on | date |  |  |
| module | varchar(60) | Y |  |
| link_path | varchar(255) | Y |  |
| audience | varchar(20) |  | `'all'` |
| status | varchar(20) |  | `'draft'` |
| published_at | timestamp | Y |  |
| created_by | bigint u | Y |  |
| published_by | bigint u | Y |  |
| created_at | timestamp | Y |  |
| updated_at | timestamp | Y |  |

**Foreign keys:** `created_by` -> `admin_users.id` [SET NULL]; `published_by` -> `admin_users.id` [SET NULL]

**Keys/indexes:** INDEX (status, released_on)

### `platform_release_tenant`

Pivot: which tenants a release was published to. Model: —.

| Column | Type | Null | Default / extra |
|---|---|---|---|
| platform_release_id | bigint u |  |  |
| tenant_id | varchar(255) |  |  |

**Foreign keys:** `platform_release_id` -> `platform_releases.id` [CASCADE]; `tenant_id` -> `tenants.id` [CASCADE]

**Keys/indexes:** PRIMARY KEY (platform_release_id,tenant_id)

## Cross-cutting observations

- 76 of 95 tables carry `tenant_id` with a FK to `tenants.id` (`ON DELETE CASCADE`), and every one has an index whose leading column is `tenant_id`.
- Only `customers` and `employees` have `deleted_at` (soft deletes).
- No MySQL `ENUM` columns exist: every status/type column is `varchar`; permitted values are enforced in FormRequests via `config/modules.php` (chapter 06).
- Tables without an Eloquent model: `finance_balance_snapshots`, `finance_budgets`, `finance_budget_items`, `finance_tax_rates`, `finance_tax_entries`, `platform_release_tenant`, `domains` (stancl model used), and framework tables. The finance budget/tax/snapshot tables are created but nothing in `app/` reads or writes them.
- Models `Sale`, `MilkSale`, `MilkSaleItem`, `MilkSaleLog`, `MilkSalePayment` map to tables (`sales`, `milk_sales`, `milk_sale_items`, `milk_sale_logs`, `milk_sale_payments`) that **do not exist** in the live database. They are legacy: only `MilkSaleService` references the milk-sale models and no controller/route uses that service. Sales run entirely on `sale_transactions` / `sale_items` / `sale_payments`.
