# User-account and access best practices

These recommendations are built only from what the application can actually do. Where a common security feature is missing, the chapter says so and gives the best available compensating habit.

## What the application provides, and what it does not

| Control | Available? | Notes |
|---|---|---|
| Individual accounts per person | Yes | Add each person under **Users & access** |
| Two roles | Yes | **Workspace admin** and **Member** |
| Per-module View / Create / Edit / Delete | Yes | Not per farm, not per record |
| Deactivate without deleting | Yes | Takes effect at the person's next request to a farm module |
| Last-admin protection | Yes | Cannot demote or deactivate the last active admin |
| Self-lockout protection | Yes | Cannot demote or deactivate yourself |
| Rate-limited sign-in | Yes | 5 failed attempts per e-mail + IP, then a wait |
| Passwords hashed | Yes | Standard Laravel hashing |
| Password strength | Minimal | Default rule: at least 8 characters |
| Password reset by e-mail | **No** | "Forgot password?" is disabled |
| Two-factor authentication | **No** | None in the code |
| E-mail verification | **No** | Addresses are not verified |
| Force password change / expiry | **No** | |
| Session timeout | Standard | Laravel session lifetime (default 120 minutes idle); "Remember me" lengthens it |
| Audit log of changes and sign-ins | **No** | Only per-record logs for sales, customers, employees and finance; platform admins see last sign-in time |
| Delegated user management | **No** | Only workspace admins |
| Per-user notification settings | **No** | |

## Principles

1. **Least privilege.** Start from **View** only and add Create, Edit, Delete only where the job needs them. Remember that **Complete / Cancel / Confirm**, stock movements and the feed calculator run on **Create**.
2. **Separate duties.** Ideally the person who records sales and payments (Sales Create) is not the only one who can change customers' credit limits (Customers Edit) or delete sales (Sales Delete).
3. **Keep Delete rare.** **Delete** on Farms, Livestock and Animals removes dependent data through cascades. Hold it with administrators.
4. **One person, one account.** Shared logins hide who did what; the few logs (sale logs, customer logs) record the user id.
5. **At least two administrators**, because the platform cannot reset a workspace password for you and no self-service reset exists.
6. **Few administrators.** Admins ignore the matrix and see all data, including salaries, national IDs and finance. Two or three is enough.

## Role design

Use the templates in *Roles and permissions matrix* as starting points. Map real jobs:

| Person | Role | Modules |
|---|---|---|
| Farm owner | Workspace admin | all |
| Farm manager | Workspace admin or Member with broad View / Create / Edit | Livestock, Animals, Health, Feeding, Milk, Breeding, Employees (View), Sales (View) |
| Milker / farm hand | Member | Dashboard View; Milk View + Create; Feeding View + Create |
| Poultry attendant | Member | Eggs View + Create; Flocks View; Feeding View + Create; Health View + Create (mortality) |
| Vet / health officer | Member | Health View + Create + Edit; Animals View; Feeding View |
| Sales / cashier | Member | Sales View + Create + Edit; Customers View + Create |
| Bookkeeper | Member | Finance View; Expenses View + Create + Edit; Sales View; Customers View |
| Visiting auditor | Member | View on every module; no Create / Edit / Delete; **deactivate** after the visit |

> **Warning:** Do not give **Dashboard > View** to anyone who should not see revenue, expenses and receivables, because the dashboard shows those totals regardless of the Finance permission.

## Passwords

- Choose long passphrases (the system only enforces eight characters, so the discipline is yours).
- Set a **temporary** password on **Add user**, deliver it privately (not by a group chat), and ask the person to change it on **Profile** at first sign-in. The system will not force this.
- Never reuse a password that was shared on paper or by message.
- When someone is suspected of misuse: **Deactivate** first (their session ends at the next farm-page request), then change the password, then investigate.
- Ask staff not to change their e-mail on **Profile**; they could lock themselves out (see the troubleshooting chapter). Admins change e-mails.

## Onboarding and offboarding

**Onboarding**

1. Confirm the person's job and approval from the owner.
2. Create the account with their own work e-mail; role Member; minimum modules.
3. Hand over credentials privately; ask for a password change.
4. Ask them to sign in and report what they can see; compare with what you intended.
5. Note the grant (date, who approved) in your own records; the system keeps none.

**Offboarding (same day)**

1. **Deactivate** the account.
2. Set the employee record to **Terminated** with the termination date.
3. Review the person's recent entries (sales, expenses, stock adjustments).
4. Keep the account inactive; do not delete or reuse the e-mail.

**Role change**: edit the matrix, **Save changes**; ask the person to reload.

## Periodic access review

Do this at least quarterly, and whenever staff change:

1. Open **Users & access**. For every row: is the person still working here? If not, **Deactivate**.
2. Admins: still only the intended few? Is there a second admin?
3. For each member, open **Edit** and review the **Module access** ticks against the person's current job.
4. Compare the list with **Employees**: every active login should map to an active employee (the two lists are separate, so this is a manual check).
5. Look at the **Access** column: "No modules" accounts are wasted logins; deactivate or fix them.

## Using shared devices and public computers

- Do not tick "Remember me" on shared computers: the account stays signed in for a long time.
- Use the logout button, which also clears the workspace cookies.
- Sessions expire after inactivity, but do not rely on that on a shared workstation.

## Platform-level account hygiene (operators)

- Create platform admins with a strong password through the command and avoid leaving it in shell history or `.env` once created (the environment values are only used when the command runs).
- Keep the number of super admins small; they can delete any workspace.
- Reset a platform admin password by re-running `php artisan orora:super-admin` for the same e-mail.
- Review `php artisan orora:super-admin-status` periodically to see who has signed in and when.
- Take a database backup before **Delete user**, **Delete tenant** or any bulk change.

## Known limitations

- No MFA, no password reset by e-mail, no e-mail verification, no forced password change.
- No audit log; you cannot prove who changed a permission or a record.
- No farm-level or record-level permission.
- Anyone with a signed-in session can use notifications, "What's new" and the marketplace seller pages.
