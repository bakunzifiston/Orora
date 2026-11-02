# Data-quality monitoring

OroraFarm validates each form and protects a number of relationships, but it has **no data-quality screen**, no duplicate finder and no audit log. Administrators therefore have to monitor quality themselves, using the lists, filters and dashboard alerts that do exist. This chapter lists what the system guarantees, what it does not, and a practical checklist to fill the gap.

Everything below is derived from the validation rules, services and database constraints in the code.

## What the application already guarantees

### Uniqueness that is enforced

| Item | Rule | Message / behaviour |
|---|---|---|
| User e-mail | Unique in the workspace and in the platform sign-in directory (so unique across all workspaces) | "The email has already been taken." |
| Farm registration number | Must not already exist in the farms table; the check is not limited to your own workspace | "The registration number has already been taken." |
| Animal tag number | Unique **within a livestock group** (same tag allowed in another group) | validation error on Tag number |
| Feed inventory | One row per farm + feed type | validation error on Feed type |
| Milk session | One per herd + date + shift | "A milk session already exists for this herd, date, and shift." |
| Milk record | One per animal per session | "This animal already has a record in this session." |
| Birth record | One per breeding | "A birth record already exists for this breeding." |
| Employee farm assignment | One per employee + farm (database constraint) | database error |
| Generated codes | Sale numbers, flock codes, session codes, payment references, finance transaction codes, customer and employee codes are unique | generated automatically |
| Ledger postings | One recognition posting per source record (database constraint) | prevents double-posting a sale or expense |

### Relationship checks in forms

- Animals, flocks, livestock, expenses, feed records and sales check that the chosen animal, flock or group **belongs to the chosen farm**.
- Flocks and egg collections only on poultry farms; milk only for lactating animals of the session's herd.
- A payment cannot exceed the balance due; stock movements cannot take stock below zero; cracked eggs cannot exceed eggs collected; livestock head counts and quantities cannot be negative.

### Deletion guards

| Record | Guard |
|---|---|
| Customer | Cannot be deleted if it has any sale ("Customer has sales history and cannot be deleted.") |
| Medicine | Cannot be deleted if linked to a treatment |
| Expense category | System categories and categories with expenses are protected |
| Expense | One created from another record (feed purchase, vaccination, etc.) cannot be deleted directly: "Delete the source record instead, or unlink first." |
| Sale | Only draft or cancelled sales can be deleted |
| Milk session | Only open sessions can be deleted |
| Milk storage tank | Must be empty |
| Breeding record | Cannot be deleted once a birth exists |

## What is **not** protected (watch these)

| Risk | Why it can happen | How to check |
|---|---|---|
| **Duplicate customers** | No uniqueness check on customer name, phone, e-mail or ID | **Customers > Directory**, sort / search by name and phone; merge by keeping one and moving future sales to it (there is no merge tool) |
| **Duplicate employees** | No uniqueness check on national ID, phone or e-mail | **Employees > Directory** |
| **Duplicate vendors, feed types, expense categories** | Names are free text; a new vendor name typed on a purchase form creates a new vendor | **Expenses > Vendors**, **Feeding > Feed types** |
| **Duplicate egg collections** | No uniqueness per flock, date and shift | **Eggs > Collections**, filter by date |
| **Duplicate livestock groups / flocks** | Names are not unique | **Livestock**, **Flocks** |
| **Same animal twice with different tags** | Only the tag is checked, within a group | **Animals**, search by name and parent tags |
| **Duplicate farm registration** within your workspace | The check exists, but a different spelling is accepted | **Farms**, search by name / owner |

> **Tip:** Agree one naming convention (for example "AgroVet Kigali", never "Agrovet") and tell staff; the application cannot enforce it.

## Orphan and dangling records

An *orphan* is a record whose parent is gone or never was. The relationships are mostly enforced by the database:

- **Farm deletion cascades.** Deleting a farm removes its livestock groups, animals, flocks, sales, feed inventory and other farm-owned records in one step. That is by design, not an orphan - but it is an easy way to lose data. Archive a farm by setting **Status = inactive** instead.
- **Livestock group deletion** clears the group link on animals, treatments, vaccinations, vet visits and sales items rather than deleting them. Animals remain but **belong to no herd**, so they drop out of milk sessions that need a herd. Search **Animals** for blank groups after any group deletion.
- **Animal deletion cascades.** Deleting an animal also deletes, through database foreign keys, that animal's health records, vaccinations, treatments, vet visits, disease records, mortality records, milk records, pregnancy checks, breeding records (as the female) and births where it is the mother. Expenses, feedings, dewormings and sale line items keep their rows but lose the animal link. Never delete an animal to "hide" it; set its **Lifecycle status** (Sold, Deceased, Transferred, Missing, Retired) instead, so its history stays. Only users with **Animals > Delete** can do this, so keep that box for administrators.
- **User deactivation** preserves "created by" links; there is no user deletion in the workspace.
- **Movement records** are free-standing logs. A movement does not change an animal's farm, so an animal can show "Farm A" while a movement says it moved to Farm B. Reconcile by editing the animal.
- **Head counts** (livestock group) and flock counts can differ from the number of registered animals or flock events because they can be edited by hand.
- **Platform level:** a tenant created at `/admin/tenants/create` has no user; a workspace with a user but no farm shows as **No farm** / **Accounts pending**; a user whose e-mail was changed on the **Profile** page may no longer match the sign-in directory. These appear in the platform admin's **Users** list with the **No farm** filter.

### A quick orphan sweep

1. **Animals**: any animal with no group, or a lifecycle status of Sold / Deceased that still has an open sale, session or schedule.
2. **Livestock**: groups with head count 0 and status active.
3. **Feeding > Inventory**: items whose feed type is inactive.
4. **Sales > Transactions**: drafts older than a week (they reserve their animals).
5. **Customers**: customers with a credit balance but no recent sales.

## Backdated and future-dated entries

Some forms reject future dates; most accept any past date. Because the dashboard and reports are **period-based**, a backdated entry silently changes figures for a month you may already have reported.

| Form | Date rule |
|---|---|
| Milk session | date must be **today or earlier**; not in the future |
| Milk distribution | date and time cannot be in the future |
| Breeding record, pregnancy check, birth record, disease diagnosis | must be today or earlier |
| Animal date of birth | today or earlier |
| Farm owner / member date of birth | before today |
| **Sale date** | **any date** (past or future) |
| **Expense date** | **any date** |
| **Sale payment date** | any date |
| Feeding record, egg collection, mortality, vaccination, treatment, health record | no future restriction in the request validation |
| Stock movement date | optional, any date; defaults to now |

Other date relationships enforced: end dates and follow-ups cannot be earlier than start dates (treatments, vet visits, schedules, certificates, flock end date, employee contracts), and next-due dates cannot be earlier than the vaccination / deworming date.

### Why backdating matters here

- **Finance posts by the sale or expense date.** A sale completed today but dated last month lands in last month's profit and loss and cash flow. Accounting periods are created automatically as open and the application has no way to close them, so nothing blocks this.
- **Dashboard "this month / this year"** moves accordingly; the same sale can change last month's numbers after you have reported them.
- **Overdue payments** is based on the sale date: backdating a sale beyond 30 days instantly makes it overdue.
- **Stock history**: a feeding record dated in the past still deducts stock *now*; the movement is dated in the past but the balance changes today.

> **Warning:** Since there is no audit log, you cannot see afterwards that a record was backdated. Make a rule that sales and expenses are entered the day they happen, and review recent entries whose date is much older than their creation.

## Stale data that keeps alerting

| Symptom | Cause | Fix |
|---|---|---|
| **Vaccinations due** never clears | The count includes every vaccination record with a past or near next-due date, even if a newer vaccination was recorded | Edit or clear **Next due date** on superseded records, or tolerate a standing number |
| **Scheduled feedings overdue** | Active schedule not advanced | Record the feeding from the schedule, or set the schedule to inactive / end it |
| **Certificates expiring** then silence | Alert only for expiry within 30 days ahead; expired ones leave the alert but their status stays "valid" | Update status to expired / renew |
| **Pregnancy checks due** | Pending breedings with no check | Record the check or update the breeding outcome |
| **Animals need attention** | Health status left as Sick / Recovering | Update status when treatment ends |
| **Credit limit exceeded** | Balance above limit | Record payments or raise the limit |

## Importing data

Animals, customers and employees can be imported from CSV (template provided). The animal wizard shows a **preview** and needs an explicit **confirm**; a **replace** mode overwrites existing animals. Always:

1. Download the **template** and keep its header row.
2. Import a handful of rows first.
3. Read the preview, especially for duplicate tags, and cancel if anything looks wrong.
4. Export the animals afterwards and compare totals.

## Monthly data-quality review

| # | Check | Where |
|---|---|---|
| 1 | Search for duplicate customers, vendors, employees | Directories |
| 2 | Animals without a group, or with conflicting statuses | **Animals** |
| 3 | Head counts vs animal counts per group | **Livestock** vs **Animals** |
| 4 | Drafts and long-confirmed sales | **Sales > Transactions** |
| 5 | Entries with unusual dates (very old, or at month end) | Sales, Expenses, Feeding |
| 6 | Inventory balances vs physical counts | **Feeding > Inventory**, **Health > Medicines** |
| 7 | Superseded vaccination due dates and expired certificates | **Health > Vaccinations**, **Certificates** |
| 8 | Inactive users still listed as active | **Users & access** |

## Known limitations

- No duplicate detection, no merge tool and no bulk clean-up.
- No audit log, so changes and backdating cannot be traced to a person or time.
- No ability to close an accounting period or lock a month's records.
- Some counts (head counts, flock current count, medicine quantity on its edit form) can be overwritten by hand without a history entry.
