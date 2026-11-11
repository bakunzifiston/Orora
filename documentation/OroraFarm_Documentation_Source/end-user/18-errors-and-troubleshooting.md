# Errors and troubleshooting

When something goes wrong, OroraFarm shows a message. This chapter explains the real messages you may see, what they mean, and what to do. Read the message slowly. It almost always tells you the problem.

## How messages appear

| Type | How it looks | Meaning |
|---|---|---|
| Green message | At the top of the page after you save | It worked. Example: *Payment recorded.* |
| Red message at the top | Above the form | The action was **refused**. Nothing was saved |
| Red text under a field | Under the box you filled in | That field is wrong. Fix it and save again |
| Grey full page | A page with a large number | A system or permission problem (see "Full-page errors") |

> **Tip:** When a form is refused, the values you typed are normally kept. Do not retype everything. Read the red text, fix only the wrong field, and save again.

## Step 1: simple checks

Before anything else:

1. Read the red message.
2. Check you chose the right **farm**. Many lists depend on the farm.
3. Check the **date** is not in the future (for milk).
4. Check you are not saving the same thing twice.
5. Refresh the page (press **F5**) and try once more.

---

## 1. Validation messages (field errors)

OroraFarm uses the field's technical name in these messages, so you see words like "farm id" or "yield liters". Match the name to the box.

| Message you see | What it means | What to do |
|---|---|---|
| The farm id field is required. | You did not choose a farm | Choose the farm |
| The yield liters field is required. | No litres entered | Type the litres |
| The yield liters field must be at least 0.01. | Zero or negative litres | Type a number above 0 |
| The amount field must be at least 0.01. | Zero amount for a sale payment or expense | Type a real amount |
| The session date field must be a date before or equal to today. | A future date for a milk session | Use today or an earlier date |
| The display name field is required. | The name is empty | Type the name |
| The email field must be a valid email address. | Email not written correctly | Example: `name@example.com` |
| The selected category is invalid. (or any "selected ... is invalid") | The choice is not on the list, or it belongs to another farm | Choose from the list again |
| The termination date field must be a date after or equal to hire date. | Employee end date before start date | Fix the dates |
| The expiry date field must be a date after or equal to issued date. | Document expires before it was issued | Fix the dates |
| The attachment field must not be greater than 5120 kilobytes. | Receipt file over 5 MB | Take a smaller photo or compress it |
| The attachment field must be a file of type: pdf, jpg, jpeg, png, doc, docx. | Wrong file type | Use one of the listed types |
| The file field must not be greater than 2048 kilobytes. | CSV import file too big | Split the file |
| The currency field must not be greater than 3 characters. | Currency too long | Type `RWF` |
| The animal id field prohibits flock id from being present. | You chose both an animal and a flock | Choose only one |
| The ... field must be a number. | Letters or symbols in a number box | Type digits only, no spaces or "RWF" |

### Messages written for OroraFarm

| Message | Where | What to do |
|---|---|---|
| A milk session already exists for this herd, date, and shift. | Open session | Open the existing session instead |
| This animal already has a record in this session. | Milk record | Each cow once per session. Remove the wrong record and add again |
| Only lactating animals can be milked. | Milk record | Set the animal's production status to Lactating, or pick another cow |
| Animal must belong to the session farm. / Animal must belong to the session herd. | Milk record | Choose a cow from the same farm and herd |
| Enter at least one yield in the table or paste lines. | Save all yields | Type at least one litre value |
| Add at least one animal record before completing the session. | Complete session | Add cow records first |
| Only open sessions can be completed. | Complete session | The session is already Completed or Cancelled |
| This session is no longer open for changes. | Milk record | The session is closed. Ask your supervisor |
| Only open sessions can be deleted. | Delete session | Closed sessions cannot be deleted |
| Storage tank must belong to the same farm as the session. | Complete session | Choose a tank of the same farm |
| Insufficient milk in storage. | Distribution (milk sales skip the deduction and still complete) | The tank has less milk than you asked. Check the tank quantity |
| Only 40.00 L is available in this storage tank. | Distribution | Lower the litres or choose another tank |
| Empty the container before removing it. | Delete tank | A tank with milk cannot be deleted |
| Select the calf that was fed. | Distribution | Choose a calf |
| The selected animal is not an active calf (under 12 months or in the calves group). | Distribution | Choose a calf under 12 months or in "Calves Group" |
| Describe what the milk was used for. | Distribution, Other use | Fill "Describe the use" |
| Enter the name of the person who received the milk. | Distribution | Fill the recipient name |
| Enter a valid phone number. | Distribution | 7 to 20 digits, may start with + |
| The date and time cannot be in the future. | Distribution | Use the present time or earlier |
| The recipient type does not match the selected use. | Distribution | Pick a recipient type allowed for the use |
| Sold milk is managed from the Sales module. | Distribution | Change sold milk from the sale |
| Unit price is required for milk, meat, egg, and live-bird items. | Sale item | Type the price |
| Unit price must be greater than zero. | Sale item | Price above 0 |
| Item type does not match this sale. | Sale item | Use the item form of this sale type |
| Items can only be added to draft sales. | Sale item | The sale is no longer a draft |
| This animal is already on this sale. | Sale item | The animal is already in the sale items. Nothing to do |
| This animal is already on another in-progress animal sale. | Sale item | Finish or delete the other draft first |
| Animal COW-014 is not active and cannot be sold. | Sale, complete | The animal is Sold, Deceased or Transferred |
| Animal must belong to the sale farm. / Flock must belong to the sale farm. | Sale item | Choose from the sale's farm |
| Cannot sell more birds than the flock currently holds. | Sale item | Lower the number of birds |
| Select a flock for live-bird sales. / Enter how many birds are being sold. | Sale item | Fill the flock or the number |
| Sale item must specify a storage tank. | No longer blocks milk sales | The tank is optional; add "From tank" only if you want stock deducted |
| Add at least one line item before confirming. / Cannot complete a sale without items. | Confirm, Complete | Add items |
| Only draft sales can be edited. | Save header | A completed sale cannot be changed |
| This sale cannot be cancelled. | Cancel | A completed or cancelled sale cannot be cancelled |
| Only draft or cancelled sales can be deleted. | Delete | A completed sale cannot be deleted |
| Cannot record payments on a cancelled sale. | Payment | The sale is cancelled |
| This sale has no balance due. | Payment | The sale is already fully paid |
| Payment cannot exceed the balance due (150,000 RWF). | Payment | Lower the amount to the balance or less |
| Customer has sales history and cannot be deleted. | Delete customer | Set the customer to Inactive instead |
| This expense is linked to another module. Edit it from the source record. | Edit expense | Edit the Feeding, Health or Breeding record that created it |
| Delete the source record instead, or unlink first. | Delete expense | Delete the source record |
| System categories cannot be deleted. | Delete category | System categories stay |
| Category has expenses and cannot be deleted. | Delete category | Make it inactive instead |
| Employee is already assigned to that farm. | Farm assignment | Assign a different farm |
| Accounting period ... is not open for posting. | Sale or expense | Tell your workspace admin |

### Import messages

| Message | What to do |
|---|---|
| Please choose a CSV file to import. | Click Choose file first |
| The import file must be a CSV. | Save the sheet as CSV |
| The CSV file may not be greater than 2 MB. | Split the file |
| The CSV file is empty. / The CSV file is missing a header row. | Use the downloaded template without changing the header row |
| The CSV file has a header row but no data rows. | Add at least one data row |
| The CSV file exceeds the maximum of 2000 data rows. | Split the file |
| ... rows failed | Read the row numbers and reasons below the form. Correct only those rows. Rows that were fine are already saved |
| Your import preview expired. Upload the file again. | Animal import only. Upload again |

---

## 2. Permission denied

You see a page titled **Access denied** with the message *You do not have permission to access this module.* It also appears for pages only for admins, with the message *Only workspace admins can manage users.*

What it means: your admin did not give you the right to **view**, **create**, **edit** or **delete** in that module.

What to do:

1. Do not try again and again. It will not change.
2. Click **Go to home** (or back).
3. Ask your workspace admin to change your rights.

Other account messages:

| Message | Meaning |
|---|---|
| Your account has been deactivated. Contact your workspace admin. | The admin made your account inactive |
| You cannot deactivate your own account. | Admin screens: ask another admin |
| You cannot remove the last workspace admin. | The workspace must always keep one admin |

## 3. Signing in problems

| Message | Meaning | What to do |
|---|---|---|
| These credentials do not match our records. | Wrong email or password | Check spelling, caps lock, and try again |
| Too many login attempts. Please try again in 60 seconds. | Too many wrong tries | Wait the seconds shown |
| Your session expired. Please sign in again. | You were signed out or your workspace could not be found | Sign in again |
| Your password has been reset. / We have emailed your password reset link. | Password reset worked | Follow the link in your email |
| This password reset token is invalid. | The reset link is old or already used | Ask for a new link |

## 4. Page Expired (error 419)

A grey page says **Page Expired**.

Cause: you left the page open for a long time (the session lasts about **120 minutes** without use by default) or you pressed "Back" and saved an old form.

What to do:

1. Go back and **refresh** the page (press F5).
2. If you are asked to sign in, sign in.
3. Enter your data again. The data from the expired page was **not** saved.

> **Tip:** Save your work often and do not leave forms open for hours.

## 5. Full-page errors

| Page | Meaning | What to do |
|---|---|---|
| **404 Not Found** | The page or record does not exist, was deleted, or is not available for your farm type (for example the Milk module for a poultry farm) | Go back to the list and open the record again |
| **403 Access denied** | No permission (see above) | Ask your admin |
| **419 Page Expired** | Old form (see above) | Refresh and repeat |
| **429 Too Many Requests** | Too many requests too fast (for example the public Trace page allows 30 per minute) | Wait a minute |
| **500 Server Error** | A problem inside OroraFarm | Note the time and what you did. Tell your supervisor. Do not repeat many times: you may create duplicates |

If you see 500 after saving, **check the list first**. The record may have been saved before the error. Look for it so that you do not enter it twice.

## 6. "I cannot see..." problems

| Problem | Likely cause | What to do |
|---|---|---|
| A cow is missing from the milking list | Not lactating, or different farm or herd | Check production status, farm and herd |
| A tank is missing in the sale or session form | The tank is on another farm | Choose the right farm first |
| A calf is missing in Distribution | Over 12 months and not in "Calves Group" | Check the animal's age or group |
| Milk figures are lower than I expect | Sessions are still Open | Complete the sessions |
| Revenue is lower than I expect | Sales are still Draft | Complete the sales |
| Costs look too low | Expenses are Draft | Set them to Paid |
| Cost per litre shows "No lactating animals found" | No animal has production status Lactating | Update animal statuses |
| Milk module missing in the menu | Your farm type has no milk, or no permission | Ask your admin |
| Outstanding balance does not go down | The payment was recorded on a different sale, or not recorded | Open the sale and check the Payments table |
| A new customer name does not appear in a list | Another filter is active | Click **Clear filters** |

## 7. When a form is "stuck"

1. Scroll up and down: the red message may be at the top.
2. Check fields marked with a red star (required).
3. Check number boxes for letters.
4. Try again in a different browser tab after a refresh.
5. If the **Save** button does nothing and no message appears, check your internet. The page needs a connection.

## 8. What to tell your supervisor

When you report a problem, give:

- who you are and the farm you work on;
- the page name and what you clicked;
- the **exact red message** (a photo of the screen is best);
- the time;
- the record code if any (for example MS-20261102-0001).
