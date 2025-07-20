---
title: ArtPulse Codex: Artist Monetization Tools
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# ArtPulse Codex: Artist Monetization Tools

This section outlines how artists and galleries can earn income via ticketed events, donations and digital passes. It covers database models, REST endpoints and Stripe integration.

## Overview

| Feature | Purpose |
|---------|---------|
| Ticket Sales | Paid event access, online or in-person |
| Donations | Artist tipping and general support |
| Digital Passes (Optional) | Gamified, collectible entry or VIP access |

## 1. Ticket Sales for Events

### Database Model

Custom post type `artpulse_ticket` is linked to `artpulse_event`.

| Field | Type | Notes |
|-------|------|-------|
| event_id | int | Linked parent post |
| price | float | In USD or local currency |
| quantity_limit | int | Inventory cap |
| description | text | e.g. "Includes access to Q&A and merch" |

### REST Endpoints

* **Create Ticket** (Admin/Gallery only)
  ```http
  POST /artpulse/v1/tickets
  {
    "event_id": 123,
    "price": 15,
    "quantity_limit": 50,
    "description": "General Admission"
  }
  ```
* **Purchase Ticket** (User)
  ```http
  POST /artpulse/v1/tickets/{ticket_id}/purchase
  {
    "payment_token": "stripe_token_or_id",
    "user_id": current
  }
  ```
* **List My Tickets**
  ```http
  GET /artpulse/v1/users/me/tickets
  ```

### Stripe Integration

Use Stripe Checkout for secure hosted payments.

1. Set up the product on-demand via the Stripe API.
2. Store the checkout session in `ap_ticket_orders`.
3. Confirm payment on the `wp_stripe_webhook_success` hook.

Frontend buttons can show “Buy Ticket – $15” and display sold‑out status or remaining quantity. Users can view their tickets as QR codes or reference numbers.

## 2. Artist Donations

Artists may link to an external donation URL or embed a Stripe-hosted donation page.

* Store the `donation_url` in `user_meta`.
* Display a **Support this Artist** button on profiles and event pages.
* `donation_url` is returned with user objects via the REST API.
* Expose a configuration route:
  ```http
  GET /artpulse/v1/users/{id}/donate
  ```

## 3. Digital Passes (Optional)

For festivals or collectibles, digital passes may be minted as NFT badges or limited-use QR codes. Only verified profiles can mint passes. Until native support is added, rely on third-party services such as Hic et Nunc.

## Admin & Reporting

Add a **Revenue Report** tab showing gross per event, top ticket sellers and payouts owed. Export monthly totals via:
```http
GET /artpulse/v1/reports/tickets?month=2024-07
```

## Permissions & Security

| Action | Required Role |
|--------|---------------|
| Create ticket | Gallery Admin / Artist |
| Purchase ticket | Any authenticated user |
| Refund ticket | Platform Admin only |
| View earnings | Artist (own), Admin (all) |

## QA Checklist

- User buys ticket → payment succeeds via Stripe.
- Sold-out event → UI blocks new purchase.
- Artist views dashboard → real-time ticket counts visible.
- Refund issued → purchase marked as refunded.

## Developer Checklist

- `artpulse_ticket` CPT registered with correct fields.
- REST endpoints secured with `permission_callback`.
- Stripe keys set via `ARTPULSE_STRIPE_SECRET_KEY`.
- Email confirmation sent on ticket purchase.
- Webhook handles payment confirmations.

## Roadmap Extensions

- Add ticket scanning (QR-based check-in).
- Add waitlist for sold-out events.
- Add coupons or early-access for collectors.

> 💬 *Found something outdated? [Submit Feedback](../../feedback.md)*
