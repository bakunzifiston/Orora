# Introduction

## What is OroraFarm?

OroraFarm is a web program for running a farm. You use it in a normal web browser (Chrome, Edge, Firefox or Safari) on a computer, tablet or phone. You do not need to install anything.

OroraFarm replaces the paper notebook. You write down what happens on the farm: animals registered, feed given, medicine used, eggs collected, milk sold, money spent. OroraFarm keeps these records in one place, adds them up for you, and warns you when something needs attention.

This guide is for **farm data-entry staff**. It explains, step by step, how to enter and correct records. You do not need to know computers well. If you can use a phone, you can use OroraFarm.

> **Example:** A farm in Musanze has a Friesian cow named "Inyange" with tag number RW-001. Every morning the milker records her milk. When she is vaccinated, the farm assistant records the vaccination. When a calf is born, the herdsman records the birth. All of this is kept under Inyange's farm.

## Money and language

- All money in OroraFarm is in **Rwandan francs (RWF)**. For example, the dashboard shows overdue payments as "120,000 RWF".
- The screens are in **English** by default. You can switch to **Kinyarwanda** with the **EN | RW** buttons at the top of the page. See [Access, login and logout](02-access-login-logout.md).
- Dates on screen look like "Oct 5, 2026". Date boxes use your browser's calendar picker.

## The two kinds of farms: cattle and poultry

OroraFarm supports two **farm types**. You choose the type when you register a farm. The type decides which menu items you see.

| | Cattle / dairy farm | Poultry farm |
|---|---|---|
| What you keep | Individual animals (cows, bulls, heifers, calves) | Flocks (a batch of birds counted together) |
| Main menu items | Farms, Livestock, **Animals**, Employees, Health, Feeding, Feed calculator, **Milk**, **Breeding**, Certificates, Movement, Sales, Customers, Expenses, Finance | Farms, Livestock, **Flocks**, Employees, Health, Feeding, Feed calculator, **Eggs**, Certificates, Movement, Sales, Customers, Expenses, Finance |
| How stock is counted | One record per animal, with a tag number | One record per flock, with a bird count |
| Production | Milk | Eggs |
| Breeding module | Yes | No (not available) |
| Abattoir dispatch (under Sales) | Yes | No (hidden) |
| Herd group choices | Calves Group, Heifer, Cows (lactating), Pregnant Cows, Dry Cows, Bulls, Steer (castrated male), Other | Day-old chicks, Pullets, Layers, Broilers, Breeders, Cockerels, Other |

Important rules about farm types:

- **One type per farm.** If you keep both cows and chickens, register two farms: one cattle farm and one poultry farm.
- **The type is locked** after you add any livestock group, animal or flock to the farm.
- The menu follows the farm you are working in. If your workspace has only cattle farms, you see the cattle menu. If it has only poultry farms, you see the poultry menu. If it has **both**, the menu shows the cattle items until you pick a poultry farm in the **Farm** filter on the Dashboard (and press **Apply**). Then the poultry items (Flocks, Eggs) appear. Choose "All farms" to go back.
- If you open a page that does not belong to your farm type (for example, Flocks on a cattle farm), you get a "page not found" screen. This is normal.

> **Note:** Other animals (goats, sheep, pigs, rabbits, fish and others) can be chosen as the "livestock type" inside a livestock group on a cattle farm. OroraFarm does not have special modules for them. They use the cattle screens.

## The modules

A **module** is one section of the program, shown in the left menu. These are the modules that exist:

| Menu group | Module | What it is for |
|---|---|---|
| (top) | **Dashboard** | Summary numbers, charts and alerts for your farms |
| Farm management | **Farms** | Register and manage farms |
| | **Livestock** | Livestock groups (a herd or class of animals on a farm) |
| | **Animals** | Individual animals (cattle farms) |
| | **Flocks** | Bird flocks (poultry farms) |
| | **Employees** | Staff records, payroll details, documents |
| Animal operations | **Health** | Vaccinations, deworming, treatments, medicines, disease, vet visits, mortality, timeline |
| | **Feeding** | Feed types, feed stock, feeding records, schedules |
| | **Feed calculator** | Suggests how much feed an animal or group needs |
| | **Milk** | Milking sessions, storage, distribution (cattle) |
| | **Eggs** | Egg collections (poultry) |
| | **Breeding** | Breeding records, pregnancy checks, births (cattle) |
| Compliance & records | **Certificates** | Health, vaccination, export, ownership and transport papers |
| | **Movement** | A log of animals or birds moving between farms or leaving |
| Business & finance | **Sales** | Sales of animals, meat, milk and eggs; abattoir dispatch |
| | **Customers** | Buyers and their contacts, credit |
| | **Expenses** | Costs, categories, vendors |
| | **Finance** | Money overview, profit and loss, cash flow |
| Workspace (admins only) | **Users & access** | Add staff and choose what each person may do |

This guide covers the data-entry work of the first modules in this list. Milk, Sales, Customers, Expenses, Finance and Employees are described in later chapters.

> **Note:** You only see the modules that your workspace admin allowed for you. See [Permissions](04-permissions.md).

## How this guide is organised

| Chapter | Read it when you want to... |
|---|---|
| 2. Access, login and logout | Sign in, change language, change your password |
| 3. Dashboard and navigation | Understand the first screen and the menu |
| 4. Permissions | Know why a button or page is not available to you |
| 5. Creating and updating records | Learn how every form and list works |
| 6. Farms and livestock registration | Register farms, groups and animals; import; certificates; movements |
| 7. Flocks, poultry and eggs | Register flocks, record deaths and egg collection |
| 8. Feeding and feed inventory | Record feed bought and feed given |
| 9. Health, treatments and breeding | Record vaccines, treatments, disease, vet visits, medicines, breeding and births |

## How to use this guide

1. Read chapters 2 to 5 once. They teach the basics that apply to every screen.
2. Then use chapters 6 to 9 like a recipe book. Find the task, follow the numbered steps.
3. Each task has the same parts:
   - **Purpose**: why you do it.
   - **When to do it**: the right moment.
   - **What you need**: information to have ready.
   - **Steps**: what to click and type.
   - **Fields**: a table of every box in the form.
   - **What happens after you save**: including things OroraFarm does for you automatically, such as reducing feed stock.
   - **Common mistakes**: what to avoid.
4. A red star (*) next to a box name means the box is **required**.

## Boxes you will see in this guide

> **Note:** Extra information you should know.

> **Tip:** A shortcut or good habit.

> **Warning:** Something that can cause lost data or wrong numbers.

> **Example:** A realistic farm example.

## Before you start: good habits

- **Register things in this order:** Farm, then Livestock group, then Animals (or Flock for poultry). Everything else (health, feeding, breeding) needs these first.
- **Enter records the same day.** It is easier to remember and the numbers stay correct.
- **Search before you create.** A duplicate record causes wrong totals. See [Creating and updating records](05-creating-updating-records-and-forms.md).
- **Use the same spelling** for names, tags and vendors every time.
- **Never share your password.** Every person should have their own login so records show who did what.

## Known limitations

- There is no in-program Help page. Use this guide, or ask your workspace admin.
- The search box at the top of every page ("Search farms, animals, sales...") is only a visual box at this time; it does not search. Use the Search box on each list page instead (Farms, Livestock, Animals, Customers, Employees have one).
- The padlock button at the top of the page does nothing yet.
