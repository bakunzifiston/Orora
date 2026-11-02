# Common administration problems

Use this chapter as a troubleshooting table. Each entry names the symptom, the cause that the code actually implements, and the fix. Messages are quoted as the application shows them.

## Signing in

| Symptom | Cause | Fix |
|---|---|---|
| "These credentials do not match our records." | Wrong e-mail or password; or the e-mail is not in the sign-in directory, so the system cannot tell which workspace to open | Check spelling (e-mails are lower-cased); an admin resets the password in **Users & access > Edit**. If the person recently changed their own e-mail on **Profile**, see the next row. |
| Person changed their e-mail on **Profile** and can no longer sign in | The Profile page updates the user but not the sign-in directory (`tenant_accounts`), which still holds the old address; the new address maps to no workspace. The admin edit form cannot repair it, because it refuses the old address as "already taken" by the directory | Ask the platform operator to update the directory row (old e-mail to new e-mail) for your workspace. To prevent this, tell members not to change their e-mail themselves; admins should change e-mails only through **Users & access > Edit**. |
| "Too many login attempts. Please try again in N seconds." | Five failed attempts for the same e-mail from the same IP address | Wait for the countdown. Repeated typing of a wrong password only extends it. |
| "Your account has been deactivated. Contact your workspace admin." | The user is **Inactive** | Admin clicks **Activate** in **Users & access**. |
| "Your session expired. Please sign in again." | The browser still held a login cookie but the workspace could not be identified (for example after the platform deleted the workspace, or cookies were cleared) | Sign in again; if it repeats, the account may have been removed by the platform. |
| Forgot password | No self-service reset exists; the "Forgot password?" link is disabled ("Coming soon") | A workspace admin sets a new password for the person. If the only admin is locked out, only the platform operator can help (direct database work). Keep two admins. |
| A new person registered at `/register` and sees an empty workspace instead of yours | Registration always creates a **new workspace** with that person as its admin | Delete nothing; instead add them to your workspace by **Users & access > Add user** with a different e-mail (one e-mail can belong to only one workspace). Ask the platform operator to delete the stray empty workspace if needed. |
| Platform admin cannot reach `/admin` | Wrong account type, or no admin exists | Check with `php artisan orora:super-admin-status`; create or reset with `php artisan orora:super-admin`. |

## Access and permissions

| Symptom | Cause | Fix |
|---|---|---|
| "403 - You do not have permission to access this module." | No permission row for that module, **View** off, or the action box (Create / Edit / Delete) off | **Users & access > Edit**: tick **View** then the needed action. Remember Complete / Cancel / Confirm and "Run calculation" and stock movements need **Create**. |
| Member signs in and lands on **Profile** | They have no module with **View** ticked, so there is no home page to send them to | Tick **Dashboard > View** (or another module). |
| A menu item is missing | The sidebar hides modules without **View**, and hides modules the active farm's type does not use (for example **Milk** on a poultry farm) | Check permissions, then the active farm's type. |
| "404" on **Milk**, **Breeding**, **Animals**, **Flocks** or **Eggs** | The active farm is of the other type (cattle vs poultry) | Switch the **Farm** filter to a matching farm. |
| "403 - Only workspace admins can manage users." | A member opened `/workspace/users` | Only the **Workspace admin** role can; promote or ask an admin. |
| `/feedings`, `/feedings/create` or `/sales-legacy` give 403 | Old redirect addresses that have no route name are refused for everyone | Use **Feeding > Records** and **Sales > Transactions**; update bookmarks. |
| A member can see money on the dashboard without Finance rights | The dashboard shows revenue, expenses and receivables to anyone with **Dashboard > View** | Do not grant **Dashboard > View** to people who should not see financial totals (they then land on their first permitted module). |
| A member with no module rights can still list animals for sale | Shop listing pages only need a signed-in session | Deactivate the user, or avoid adding people who should not trade; there is no permission for the shop. |

## User administration

| Symptom | Cause | Fix |
|---|---|---|
| "The email has already been taken." when adding a user | The address exists in this or another workspace | Use another address; one address cannot be in two workspaces. |
| "You cannot remove the last workspace admin." / "You cannot deactivate the last workspace admin." | Safeguard counts **active** admins; only one left | Promote another person to **Workspace admin** first. |
| "You cannot change your own role away from workspace admin." / "You cannot deactivate your own account." | Self-protection | Ask another admin. |
| There is no Delete button on a user | By design | Use **Deactivate**. |
| Password change did not sign the person out elsewhere | Sessions are not invalidated on password change | To cut access immediately, **Deactivate** the user (they are signed out on their next request), change the password, then **Activate** again. |
| New member has "No modules" in the list | No permission row was saved (View never ticked) | **Edit** and tick modules. |

## Data entry and workflow

| Symptom | Cause | Fix |
|---|---|---|
| "Farm type cannot change after livestock, animals, or flocks have been added." | The farm already has stock | Create another farm for the other species. |
| "The registration number has already been taken." | The number exists on a farm, possibly in another workspace | Check **Farms** for a duplicate; otherwise use the exact official number only if it is truly yours, else contact the platform operator. |
| "The selected Rwanda location is invalid." | Province / district / sector / cell / village combination does not match the built-in list | Re-select from the top (**Province** first). |
| "Animal X is not active and cannot be sold." | Lifecycle status is not **Active** (Sold, Deceased, Transferred, Missing, Retired) | Correct the status if it was wrong. |
| "Animal X must be healthy to send to the abattoir." | Health status is not **Healthy** | Update health status once the animal has recovered. |
| An animal does not appear in the new-sale list | Not Active, from another farm, or already on another in-progress animal sale | Finish or delete the other draft sale. |
| "Insufficient stock for this movement." | A feeding record or movement would take stock below zero | Record a **Purchase** or **Adjustment (add)** first; check the quantity and unit. |
| "This medicine has expired / is inactive and cannot be used for treatments." | Expiry date before the treatment date, or medicine marked inactive | Choose another batch; correct the expiry date if it was entered wrongly. |
| "A milk session already exists for this herd, date, and shift." | Duplicate session | Open the existing session and add records to it. |
| "Only lactating animals can be milked." / animal missing from the milking list | Production status is not **Lactating** (or the animal is not in a "Cows (lactating)" group when it has none), or it belongs to a different herd or farm | Update production status or group. |
| "Add at least one animal record before completing the session." | Empty session | Add records, or cancel the session. |
| Cannot edit or delete a milk session | It is completed or cancelled ("Only open sessions can be deleted.") | Cannot be reopened; adjust the tank and add a note. |
| "Only draft sales can be edited." / "Only draft or cancelled sales can be deleted." | The sale has moved on | Cancel it (if not completed). A completed sale cannot be deleted. |
| "Payment cannot exceed the balance due" | Overpayment | Enter the correct amount; there is no overpayment or refund screen. |
| "Customer has sales history and cannot be deleted." | Any sale exists | Set the customer **inactive** instead. |
| "This medicine is linked to treatments and cannot be deleted. Mark it inactive instead." | Used in treatments | Mark inactive. |
| "Delete the source record instead, or unlink first." | The expense was created by another record | Edit or delete the source record (feed purchase, vaccination...). |
| "Accounting period ... is not open for posting." | A finance period has status other than open (cannot be set in the app) | Operator action on the database. |
| Finance shows nothing for old sales | Ledger entries are posted when sales are completed / expenses paid after the module was installed | The operator can run `php artisan finance:backfill` (optionally `--tenant=`) to post existing completed sales and paid expenses. |

## Numbers that look wrong

| Symptom | Likely reason |
|---|---|
| Revenue tile is 0 | Sales still **In progress**, or sale date outside the period, or the period filter is **Today** |
| Revenue chart and Revenue tile differ | Chart uses sales including tax; tile uses ledger revenue net of tax |
| "Active animals" includes sold animals | The tile counts all animals in the filter |
| Vaccinations due never reaches 0 | Older records keep their past next-due date |
| Milk produced today is higher than Milk produced | "Today" includes open sessions; the period figure counts completed sessions only |
| Head count disagrees with number of animals | Head count is typed by hand |
| Platform farm map pins cluster together | Farms have no saved coordinates and are placed by district |
| Accounts receivable tile is lower than the Unpaid balance tile | Tile covers only postings in the selected period |

## Notifications and communication

| Symptom | Cause | Fix |
|---|---|---|
| Nobody was told that stock is low | Alerts are not pushed | Open the **Dashboard**; see the routine in *Notifications and releases*. |
| New user did not receive the release notification | They were added after the release was published, or are inactive | They can read **What's new** anyway. |
| Buyer inquiry not noticed | Delivered to workspace admins' bell only | Check **Shop > My listings** and the bell. |

## Platform operator problems

| Symptom | Cause | Fix |
|---|---|---|
| "Could not delete <name>. Some related records could not be removed." | The purge transaction failed and rolled back | Retry after checking the database logs; take a backup before retrying. |
| Tenant created but nobody can sign in | Tenant creation does not create a user | Have the farmer register, or add the user and directory row manually. |
| `orora:super-admin` says the table is missing | Migrations have not been run | Run `php artisan migrate --force` (or `orora:install`). |
| Release published but no bell notification | Audience has no active users, or "Selected tenants" with a tenant that has no users | Check the **Notified** column on the release list; edit the audience. |

## When to escalate

Escalate to the platform operator when you need: a lost-admin password reset, correction of the sign-in directory, deletion of a stray workspace, closing an accounting period, backfilling the ledger, or restoring deleted data from a backup.
