# Appendix: Quick reference checklists

Print these or copy them into your own routine. Tick boxes refer to screens and labels described in the earlier chapters.

## Daily administrator checklist (about 10 minutes)

**Dashboard**
- [ ] Open **Dashboard**, set **Period** to **This month** and **Farm** to **All farms**.
- [ ] Read the **alert strip**: act on every **Critical** alert first (**Animals need attention**, **Credit limit exceeded**).
- [ ] Open each **Warning**: **Feed inventory low**, **Vaccinations due**, **Deworming scheduled soon**, **Scheduled feedings overdue**, **Certificates expiring**, **Pregnancy checks due**, **Outstanding payments**.

**Production and stock**
- [ ] **Milk > Sessions**: yesterday's and today's sessions exist for every herd and shift; none left open.
- [ ] **Eggs > Collections** (poultry): yesterday's collections are in; no duplicates.
- [ ] **Feeding > Records**: feeding was recorded; no "Insufficient stock" problems reported by staff.

**Sales and money**
- [ ] **Sales > Transactions**: no unexplained **In progress** sales from previous days; payments received today are recorded.

**People and notices**
- [ ] Bell or **Notifications**: read new buyer inquiries and "What's new" notices.
- [ ] Any staff message about being unable to sign in or missing a menu: check **Users & access** (status, role, ticks).

## Weekly administrator checklist

**Health and livestock**
- [ ] **Health > Disease**: close cases that are *recovered*; review *recovering* and *chronic* cases.
- [ ] Animals with health status **Sick / Under treatment / Quarantined / Recovering**: each has a treatment or disease record; update statuses of healed animals.
- [ ] **Health > Vaccinations** and **Dewormings**: schedule what is due within 30 and 7 days.
- [ ] **Mortality** entries complete (date, cause, deaths).

**Inventory**
- [ ] **Feeding > Inventory**: review **Low stock**; plan purchases.
- [ ] **Health > Medicines**: review **Low stock** and **Expired**; deactivate or write off expired items with an "Adjustment (remove)" movement.
- [ ] Count one or two items physically; correct differences with adjustment movements and a note.
- [ ] **Feeding > Schedules**: end, pause or advance overdue schedules.

**Sales and customers**
- [ ] **Sales > Overview**: **Unpaid balance**; chase buyers; review **Customers > Over limit**.
- [ ] Complete or cancel old drafts.

**Admin**
- [ ] **Users & access**: any new starters or leavers this week? Update accounts the same day.
- [ ] Review expense entries for the week (status **paid**, correct dates and categories).

## Monthly administrator checklist

**Close the month (manual)**
- [ ] All sales for the month **Completed** or **Cancelled**; all payments recorded.
- [ ] Expenses for the month entered; drafts resolved; status **paid** where paid.
- [ ] Stock counts reconciled; milk tanks reconciled (intake = sold + distributed + spoiled + remaining).
- [ ] Capture **Dashboard**, **Finance > Profit & loss** and **Finance > Cash flow** for the month (screenshot or print).
- [ ] Export **Animals**, **Customers** and **Employees** CSV and file them with the date.

**Data quality**
- [ ] Search for duplicate customers, employees, vendors, feed types.
- [ ] Livestock **head counts** vs number of registered animals per group.
- [ ] Animals without a group or with conflicting statuses.
- [ ] Old vaccination due dates that keep **Vaccinations due** high; expired certificates still marked valid.
- [ ] Entries with unusual dates (very old or future-looking).

**People and access**
- [ ] Review **Users & access**: deactivate leavers; check the **Access** column for "No modules"; confirm at least two admins.
- [ ] Reconcile logins vs **Employees** (status active / terminated).
- [ ] Check payroll records against actual payments.

**Platform operator (if applicable)**
- [ ] Database backup taken and a restore tested at least quarterly.
- [ ] `php artisan orora:super-admin-status` reviewed.
- [ ] **Contact inbox**: all messages marked read / replied / archived.
- [ ] **Ecommerce**: pending listings approved or rejected; inquiries updated.
- [ ] **Releases**: drafts published for deployed changes.

## Quarterly and yearly

- [ ] Full permission review: open each member's **Edit** page and verify the matrix.
- [ ] Test the lost-admin scenario: do two admins exist and can both sign in?
- [ ] Archive inactive farms (Status **inactive**) and retired animals (Lifecycle status) rather than deleting.
- [ ] Review expense categories and vendor list; retire unused ones.
- [ ] Review medicine and feed catalogues; mark obsolete items **inactive**.

## Quick reference: where do I click?

| Task | Click path |
|---|---|
| Add a user | **Workspace > Users & access** > **+ Add user** |
| Change permissions | **Users & access** > **Edit** > **Module access** > **Save changes** |
| Deactivate / reactivate | **Users & access** > **Deactivate** / **Activate** |
| Change my own password | **Profile** > **Change password** |
| Register a farm | **Farms** > **Register farm** |
| Switch the farm in view | **Farm** filter (for example on the Dashboard) |
| Add stock | **Feeding > Inventory** > **Add stock**; **Health > Medicines** > medicine > stock movement |
| Complete a sale | **Sales > Transactions** > sale > **Complete** |
| Record a payment | **Sales > Transactions** > sale > record payment |
| See last month's profit | **Finance > Profit & loss**, set the dates |
| Read what's new | **What's new** (header / bell) |
| Platform: publish a release | `/admin/releases` > **New release** > **Save & publish** |
| Platform: reset a platform admin password | `php artisan orora:super-admin --email=...` |

## Error message cheat sheet

| Message | Go to |
|---|---|
| "You do not have permission to access this module." | **Users & access** > **Edit** user |
| "Your account has been deactivated." | **Users & access** > **Activate** |
| "You cannot remove the last workspace admin." | Promote a second admin first |
| "Insufficient stock for this movement." | Add stock with a **Purchase** or **Adjustment (add)** |
| "Only draft sales can be edited." | Sale already confirmed / completed |
| "Farm type cannot change after livestock, animals, or flocks have been added." | Create a new farm |
| "Customer has sales history and cannot be deleted." | Set the customer inactive |
