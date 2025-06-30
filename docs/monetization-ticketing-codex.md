# ArtPulse Codex: Monetization & Ticketing

This guide describes optional modules for selling event tickets, applying
promotional discounts and managing paid memberships. It is intended for
developers who wish to integrate payment providers or extend monetization
features.

## 1. Paid Tickets & Tiers

`TicketManager` exposes REST endpoints for listing ticket tiers and
initiating a purchase. An event may define multiple ticket levels such as
General, VIP or Early Bird. Integrations with WooCommerce or Stripe can be
added via hooks.

Example endpoints:

```
GET  /wp-json/artpulse/v1/event/{id}/tickets
POST /wp-json/artpulse/v1/event/{id}/buy-ticket
```

`PaymentWebhookController` listens for provider callbacks (e.g. Stripe
webhooks) and can create RSVP records after successful payment.

## 2. Promo Codes & Discounts

`PromoManager` validates promo codes and returns a discounted price. Codes
may be limited by event or usage count and can be generated through admin
UI extensions.

Endpoint:

```
POST /wp-json/artpulse/v1/event/{id}/promo-code/apply
```

## 3. Memberships

`MembershipManager` provides a lightweight REST interface for retrieving or
updating a user's membership level. Hooks allow restricting VIP events or
applying automatic discounts.

Endpoint:

```
GET/POST /wp-json/artpulse/v1/user/membership
```

## 4. Sales Overview

`SalesOverview` aggregates ticket purchases for the current artist. The
endpoint returns total revenue, tickets sold and a daily trend array. Results
can be filtered by event ID and date range.

Endpoint:

```
GET /wp-json/artpulse/v1/user/sales?event_id=123&from=2024-01-01&to=2024-02-01
```

## 5. Payout Tracking

`PayoutManager` stores payouts in the `ap_payouts` table and exposes a REST
route for viewing payout history and current balance. Artists may update their
payout method via a settings route.

Endpoints:

```
GET  /wp-json/artpulse/v1/user/payouts
POST /wp-json/artpulse/v1/user/payouts/settings
```

## 6. Event Promotion

`EventPromotionManager` offers a simple route to feature an event after payment
processing. The route sets a `ap_featured` meta flag which themes can use to
boost visibility.

Endpoint:

```
POST /wp-json/artpulse/v1/event/{id}/feature
```

## Extensibility

Filters such as `artpulse_ticket_providers` or `artpulse_membership_levels`
can be introduced to support additional payment gateways or custom member
benefits.
