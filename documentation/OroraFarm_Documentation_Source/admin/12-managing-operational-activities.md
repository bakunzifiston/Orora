# Managing operational activities

This chapter covers the day-to-day activity modules an administrator supervises that are not covered in the stock, inventory, production and sales chapters: **Health**, **Feeding** schedules, **Employees**, **Customers**, **Expenses**, **Breeding**, **Certificates** and **Movement**. For each, it lists what a record is, the rules that apply, and what to review.

## Health (**Health** module)

The Health area has an **Overview** and sections **Vaccinations**, **Dewormings**, **Treatments**, **Disease**, **Vet visits**, **Mortality**, **Medicines** and a **Timeline**. Records are linked to a farm and to an animal, a flock or a livestock group.

| Section | Key facts |
|---|---|
| Vaccinations | Date, vaccine, status (from the vaccination status list), optional **next due date** (not before the vaccination date). Feeds the "Vaccinations due" alert. Can log an expense. |
| Dewormings | Date, product, status (Scheduled / done), optional medicine and quantity used; a **Scheduled** deworming within 7 days raises an alert. Deducts medicine stock when a medicine is chosen. |
| Treatments | Disease, medicine or free-text medicine, start / end date (end not before start), follow-up date, status (Ongoing, Completed, Discontinued...). Deducts medicine stock. |
| Disease | Diagnosis date (today or earlier), severity level, **recovery status** (recovering, recovered, chronic...). "Recovering" and "chronic" cases count as open and drive the **Sick animals** KPI. |
| Vet visits | Date range, vet, follow-up. |
| Mortality | See *Managing livestock and poultry operations*. |
| Medicines | See *Inventory and resources*. |
| Timeline | A chronological list of **health records** (the general health entries). It is not a combined view of vaccinations, treatments and the rest. |

Administrator tasks:

1. Weekly, open **Disease** and filter for *recovering* and *chronic* cases. Close cases that have ended by setting the recovery status to *recovered*; otherwise the animal keeps counting as sick.
2. Check that an animal marked **Sick / Under treatment** has a matching disease or treatment record. The two are independent: changing an animal's **health status** does not create a record and creating a record does not change the status.
3. Review treatments marked *Ongoing* beyond their expected end date.
4. Because health forms can log an **expense** and deduct **medicine stock**, deleting or editing a treatment reverses or re-applies those effects (stock is returned; a linked expense follows the source record).

## Feeding operations

Feeding records, inventory and low-stock logic are described in *Inventory and resources*. As an administrator also supervise:

- **Schedules** (**Feeding > Schedules**): frequency daily, weekly, biweekly or monthly; status **active**, **paused** or **completed**. A schedule is **overdue** when active and its next due date is earlier than today. Recording a feeding from the schedule advances it.
- **Feed calculator** (`feed-calculator` module): lets staff estimate rations for a group or animal. It is a calculation aid; it does not change stock. Running a calculation needs **Create** on this module (the request is a POST).

## Employees

**Employees** is a staff register, **not** a user list. An employee record can have: personal details (first and last name required), contact and emergency contact, employment details (status, employment type, job role, primary farm, hire and termination dates), payroll (contract type, base salary, currency, pay frequency, payment method), addresses, documents (with issue and expiry dates), and **farm assignments** (one per farm per employee, with from / to dates).

| Item | Rules |
|---|---|
| Status | active, inactive, on leave, terminated |
| Termination date | not before the hire date |
| Farm assignment | "assigned until" not before "assigned from"; only one assignment per employee and farm |
| Employee document | expiry not before the issue date |
| Import / export | CSV template, import (needs **Create**) and export (needs **View**) |

The **Employees overview** shows: **Total**, **Active**, **On leave**, **Terminated** and **Monthly payroll**.

Administrator tasks:

- Keep status current: when someone leaves, set **Terminated**, enter the termination date, **and** deactivate their user account (two separate steps; see *Managing users*).
- Review **documents** with approaching expiry yourself; no alert is generated.
- Payroll is a record of agreed pay; the application does not run payroll, produce payslips or post salaries to the ledger.

> **Warning:** The **Employees > Edit > Payroll** update needs only **Employees > Edit**. Anyone who can edit an employee can change salary details. Grant **Edit** on Employees sparingly.

## Customers

A **customer** is a buyer. Types are Individual, Company and Cooperative. Required: type, display name, status (active, inactive, blacklisted), trust level, currency. Others are optional: contacts, addresses, communications log, credit settings.

- **Credit**: set the credit limit and payment terms from the customer page (**Edit** permission). The outstanding balance is recalculated from sales and payments. When the balance is above a positive limit the dashboard raises **Credit limit exceeded**; it does not block selling (see *Monitoring production and sales*).
- **Communications**: a log of calls / messages you record for the customer.
- **Deleting** a customer is blocked once the customer has any sale.
- Only customers with status **active** are offered on the new-sale form.
- A change to a customer's credit limit is recorded in that customer's own log (`credit_changed` with old and new values), the nearest thing to an audit trail for this module.

Mark customers who stop paying as **blacklisted** or **inactive** so they are no longer offered on new sales.

## Expenses

Expenses record money spent. Required: category, date, amount (at least 0.01), currency (three letters), status. The status list is **paid**, **draft**, **void**:

- Only **paid** expenses count in finance and in the dashboard's **Expenses** and **Expense breakdown**. A paid expense is posted to the ledger on its expense date (cash or bank account depending on the payment method).
- Changing a paid expense to draft or void, or deleting it, **reverses** its ledger posting.
- Expenses with a **farm** requirement: all categories except those in the "General / other" group must name a farm.
- An optional animal **or** flock (not both), group, vendor, payment method, paid-by name and an attachment (PDF, image or Word, up to 5 MB).
- **Linked expenses** created by feed purchases, vaccinations, treatments and so on carry the source and cannot be deleted from the Expenses screen.

Expense groups: **Feed expenses**, **Health expenses**, **Breeding expenses**, **Farm operations**, **General / other**. Categories (**Expenses > Categories**) belong to a group; system categories cannot be deleted. Review the category list occasionally and avoid creating near-duplicates.

## Breeding, certificates and movement

These are described in the livestock chapter. For oversight:

- **Breeding** (cattle only): process pregnancy checks 35 days after service; watch **Calvings approaching**.
- **Certificates**: renew within 30 days of expiry; set expired ones to status **expired** manually.
- **Movement**: informational log; reconcile with the animal's actual farm.

## Activity oversight routine

| Frequency | Do |
|---|---|
| Daily | Dashboard alerts; milk / egg records present; sick animals have treatment; feeding done and stock deducted |
| Weekly | Disease cases open too long; overdue schedules; draft sales; expenses recorded; employee leave |
| Monthly | Payroll records vs actual pay; customer credit; certificate expiry; vendor and category list tidy |

## Known limitations

- No approval workflow for any record: whoever has **Create** or **Edit** changes data immediately.
- No assignment of tasks to people, no task list and no calendar.
- Health status on an animal is not linked to the existence of treatment or disease records.
- No document store for employees beyond the document records and file uploads on the forms.
