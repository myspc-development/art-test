# ArtPulse Codex: Financial Overview & Payout Management

This guide covers optional modules for tracking revenue, refunds and payouts across all events. It focuses on dashboards, filtering tools and exports that organizations can enable in the admin area.

## 1. Financial Dashboard & Summary Cards

Display top‑level metrics on a **Finance** tab.

- **Total revenue** (all time, this month, last payout)
- **Tickets sold** per event and overall
- **Pending payouts** representing the unpaid balance
- **Refunds processed**

Each card can use an icon and a distinct color. Example markup:

```html
<div class="finance-summary">
  <div class="summary-card">Total Revenue<br>$<?php echo $total_revenue; ?></div>
  <div class="summary-card">Tickets Sold<br><?php echo $total_tickets; ?></div>
  <div class="summary-card">Pending Payouts<br>$<?php echo $pending_payouts; ?></div>
  <div class="summary-card">Total Refunds<br>$<?php echo $total_refunds; ?></div>
</div>
```

The backend aggregates ticket sales and can cache results for speed.

## 2. Detailed Transactions & Filtering

Offer a paginated table of orders, refunds and payouts with columns for date, event, buyer, item, amount, fee, net and status.

Filters should include:

- Date range
- Event
- Transaction type (sale, refund, payout)
- Buyer or order ID search

Example filter form:

```html
<form id="transaction-filters">
  <input type="date" name="date_from">
  <input type="date" name="date_to">
  <select name="event_id">...</select>
  <select name="type">
    <option value="">All Types</option>
    <option value="sale">Sale</option>
    <option value="refund">Refund</option>
    <option value="payout">Payout</option>
  </select>
  <input type="text" name="buyer_search" placeholder="Buyer or Order ID">
  <button type="submit">Filter</button>
</form>
```

Use indexed queries for performance and mask buyer data for non‑admins.

## 3. Payout History & Status Tracking

List every payout with the request date, paid date, amount, method, status and transaction ID. Show the current unpaid balance and the next payout date.

```html
<div class="payout-summary">
  <div>Current Pending Balance: $<?php echo $pending_balance; ?></div>
  <div>Next Scheduled Payout: <?php echo $next_payout_date; ?></div>
  <a href="/org/payouts/settings" class="btn btn-secondary">Payout Settings</a>
</div>
```

Store payouts in an `ap_payouts` table and sync status with Stripe, PayPal or manual transfers.

## 4. Export Tools (CSV/Excel)

Add **Export CSV** or **Export Excel** buttons above the transaction and payout tables. Respect current filters when exporting. Stream large datasets and include a UTF‑8 header row.

```html
<button onclick="exportTransactions('csv')">Export Transactions (CSV)</button>
<button onclick="exportPayouts('csv')">Export Payouts (CSV)</button>
```

## 5. Permissions & Security

Restrict finance and payout screens to organization admins or finance roles. Use HTTPS and CSRF tokens for settings updates, and never expose full payment details in the UI or exports.
