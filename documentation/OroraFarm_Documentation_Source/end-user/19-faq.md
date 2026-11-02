# Frequently asked questions

Short answers to common questions from farm data-entry staff. Each answer points to the chapter with the full steps.

## Milk

### 1. A cow is not in my milking list. Why?
Only **lactating** cows of the **same farm and herd** as the session are offered. Open the animal and check three things: its farm, its herd, and its production status (**Lactating**). A cow with no production status is offered only if its herd group is "Cows (lactating)".

### 2. I made a mistake in one cow's litres. Can I edit it?
There is no edit button on a single cow record. While the session is **Open**, click **Remove** on that row and add the cow again with the right litres. Once the session is **Completed** you cannot change it. See *Correcting mistakes*.

### 3. What is the difference between "Complete session" and "Cancel session"?
**Complete** closes the session, makes the totals final and (if you chose a tank) adds the litres to the tank. **Cancel** closes the session without storing anything. Neither can be undone.

### 4. I completed a session without choosing a tank. Where is the milk?
It counts in production totals but is not in any tank. You cannot sell it "from a tank". For future sessions, choose the **Destination tank** when you complete the session.

### 5. Can the tank hold more than its capacity?
OroraFarm does not block it. The tank status just becomes **Full**. Check the real tank.

### 6. Milk spilled or went sour. How do I take it out of the tank?
There is no "spoilage" screen. Record a **Distribution** with "Other use", describe it (for example "Spoiled, power cut") and choose the tank.

### 7. What does "not yet accounted for" mean on the Milk overview?
It is the litres produced in the period minus all litres that have a recorded use (sold, calves, household, given away, other). A positive number may be milk still in a tank, or a missing record. See *Reports and analytics*.

### 8. Do I record milk sold in the Milk module?
No. Sell milk in **Sales > Transactions** with sale type **Milk sale**. When you confirm it, the litres leave the tank and a "Sold" line appears under Milk > Distribution.

## Sales and customers

### 9. Do I have to register a customer before I make a sale?
No. In the sale form you can choose an **Existing customer**, a **New customer** (created for you) or **Walk-in / not assigned**. A customer record is needed if you want to track who owes you money.

### 10. What is the difference between Draft and Completed?
A **Draft** is still being prepared. It does not move stock and does not count in revenue or Finance. **Completed** means the sale is final: stock and animals are updated and Finance is posted. For milk, the button **Confirm & deduct stock** completes the sale in one step.

### 11. The customer paid only part. What do I do?
Open the sale and use **Record payment** with the amount received. The payment status becomes **Partial** and the balance stays as money owed. Record the next payment later on the same sale.

### 12. I recorded a payment on the wrong sale. Can I delete it?
No. Payments cannot be edited or deleted. Tell your supervisor. See *Correcting mistakes*.

### 13. Why can't I add a second line or fix a line on a draft sale?
You can add lines to a draft, but there is no button to edit or remove a line. If a line is wrong, **Delete draft** and create the sale again.

### 14. Will OroraFarm stop me selling to a customer who owes too much?
No. The credit limit is a **warning** only. You will see "Over limit" in the Customers overview and the alert **Credit limit exceeded** on the Dashboard.

### 15. Can I delete a customer?
Only if the customer has never had a sale. Otherwise set the status to **Inactive**.

### 16. I sold eggs. Does the egg stock go down?
No. An egg sale records the money only. It does not reduce egg collection numbers.

### 17. What currency is used?
Rwandan francs, **RWF**. The Currency box starts as RWF.

## Expenses and finance

### 18. Why is my expense not in the Finance report?
Only expenses with status **Paid** are posted to Finance. A **Draft** expense is not counted. Change it to Paid, or enter it again as Paid.

### 19. Can I change the amount of an expense I already saved as Paid?
It is safer not to. The Finance entry is not updated correctly after an amount change. **Delete the expense and enter it again** with the right amount.

### 20. Why can't I edit an expense that came from the Feeding or Health module?
It is linked to the original record (for example a feed purchase). Edit or delete the **source** record and the expense follows.

### 21. Can I type a Finance entry myself?
No. Finance is read-only. Entries are made automatically from completed sales, sale payments and Paid expenses.

### 22. Why do Revenue and Cash change differ on the Finance overview?
Revenue counts a sale when it is **completed**, even if the customer has not paid. Cash change counts only money actually received or paid out.

### 23. How is the cost per litre worked out?
Paid expenses of the farm in the period, multiplied by the share of animals that are lactating, divided by the litres from completed sessions. See *Reports and analytics*.

## Employees

### 24. Does the Employees module pay my workers?
No. It stores the pay agreement (salary, how often, how paid). It does not calculate pay or record payments. Record wages in **Expenses**, category **Labor**.

### 25. Does an employee record give the person a login?
No. Logins are created by the workspace admin under Workspace users.

### 26. Can I upload a scan of a worker's contract?
No. The Documents panel is a register: you record the document's name, type, dates and notes only.

## Notifications and reports

### 27. Will I get an email or SMS for alerts?
No. Alerts appear in the app: the **bell**, the **Notifications** page and the **Dashboard alert strip**.

### 28. What creates a notification in the bell?
Release notes published by the OroraFarm team, and buyer inquiries on your Marketplace listings.

### 29. Can I download a report as PDF or Excel?
Not for reports. You can export **CSV** lists of animals, customers and employees. To get a paper copy of a report, use your browser's Print.

### 30. My Dashboard "Milk produced today" differs from the Milk overview. Why?
The Dashboard counts all sessions dated today (including open ones). The Milk overview counts completed sessions only.

## Data accuracy and general use

### 31. How do I avoid duplicates?
Search the list before adding a customer, employee, vendor or expense. OroraFarm does not check duplicates for customers, employees or vendors. It does protect against a second milk session for the same herd, date and shift, and a second record for the same cow in a session.

### 32. I got "Page Expired". Is my data lost?
The data on that page was not saved. Refresh, sign in if asked, and enter it again.

### 33. I see "You do not have permission to access this module."
Your admin has not given you that right. Ask your workspace admin.

### 34. Can I import many customers or employees at once?
Yes, with a CSV file (up to 2 MB and 2000 rows) using the template. Rows that fail are listed; rows that pass are already saved. Re-upload only the corrected rows.

### 35. Can farm staff use the Marketplace?
Signed-in users can create listings and see buyer inquiries on the public site's Shop pages. Every new or edited listing is checked by the OroraFarm team before buyers see it. See *Marketplace*.

### 36. What language can I use?
The app has English and Kinyarwanda texts. Use the language option in the page to switch where it is offered.

### 37. Whom do I ask when I am stuck?
Your supervisor first, then your workspace admin. Give them the exact red message and the time (see *Errors and troubleshooting*).
