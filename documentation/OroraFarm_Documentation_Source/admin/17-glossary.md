# Glossary

| Term | Meaning in OroraFarm |
|---|---|
| Workspace | One farm business account. All its farms, animals, sales and users belong to one workspace and are invisible to other workspaces. Also called a *tenant* in technical documents. |
| Workspace admin | A user with the role `workspace_admin`. Has every module right in the workspace plus Users & access. |
| Member | A user with the role `member`. Can use only the modules and actions ticked in the Module access matrix. |
| Platform admin / super admin | An operator of the whole OroraFarm installation who signs in to `/admin`. Not a member of any workspace. |
| Module | A functional area of the menu, such as Animals, Milk or Sales. Permissions are granted per module. |
| Module access matrix | The table on the Add user / Edit user form with View, Create, Edit and Delete boxes per module. |
| View / Create / Edit / Delete | The four permission actions. "No View, no anything": other boxes are ignored when View is off. |
| Active farm | The farm chosen in the dashboard Farm filter. It decides which species-specific menu items are shown. |
| Livestock group | A herd or group of animals of one type on a farm (for example "Cows (lactating)"). |
| Flock | A group of poultry placed on a poultry farm; its bird count changes through flock events. |
| Milk session | One milking event (a morning or evening milking) that collects individual milk records. |
| Milk storage (tank) | A tank that holds collected milk; distributions and sales reduce its stock. |
| Distribution | Any use of stored milk that is not a sale (for example calf feeding or home use). |
| Sale transaction | A sale with header, line items and payments. Moves through draft, confirmed, completed or cancelled. |
| Receivable | Money customers still owe for completed sales. |
| KPI | Key performance indicator: a headline figure on the dashboard, such as Revenue or Litres per cow per day. |
| Lifecycle status | An animal's state in its life on the farm, such as Active, Sold, Deceased. |
| Reorder level | Stock quantity at or below which feed or medicine is flagged as low stock. |
| Release / What's new | A platform announcement published by a super admin and shown to workspaces. |
| Deactivate | Switch a user off so they cannot sign in, without deleting their records. |
| Cascade delete | Deleting a record that also deletes the records that depend on it. |
| Audit log | A record of who changed what. OroraFarm has no global audit log; only some modules keep per-record history. |
