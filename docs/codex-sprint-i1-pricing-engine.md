---
title: Codex: Sprint I1 – Pricing Engine & Upgrade UI
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# Codex: Sprint I1 – Pricing Engine & Upgrade UI

## Overview
This sprint introduces paid tiers and an upgrade interface while keeping ArtPulse's creator-first approach. Billing integrates with Stripe and unlocks premium analytics, CRM tools and visibility boosts for artists and organizations.

## Goals
- Per-user and per-organization upgrade levels
- Checkout flow via Stripe or WooCommerce
- Unlock CRM, data export and insights features
- Upgrade prompts across the UI

## Data Model
Upgrade metadata can be stored on users or organizations.

Option A – User based
```php
get_user_meta( $user_id, 'ap_upgrade_level', true ); // 'free' | 'pro'
```

Option B – Organization based
```php
get_post_meta( $org_id, 'ap_org_plan', true ); // 'free' | 'pro' | 'partner'
```

| Key               | Value      | Scope   |
|-------------------|-----------|---------|
| `ap_upgrade_level`| `free`, `pro` | user |
| `ap_pro_expiration`| timestamp | user/org |
| `ap_org_plan`     | `partner` | org |

## Stripe Integration
Use Stripe Checkout for monthly or one‑time plans.

### Products
- **Artist Pro** – $5/month
- **Org Pro** – $29/month
- **Partner** – custom or invoice

### Webhooks
Listen for `checkout.session.completed` and `customer.subscription.updated`.
Endpoint:
```http
POST /artpulse/v1/payments/webhook
```
Mark upgrade level as `pro` and store expiration on success.

### Create Checkout Button
```http
POST /artpulse/v1/payments/create-checkout
```
Response:
```json
{ "url": "https://checkout.stripe.com/..." }
```
Button example:
```html
<a class="btn-upgrade" href="{{checkoutUrl}}">Upgrade to Pro</a>
```

## Feature Unlock Logic
```php
function ap_user_has_upgrade( $user_id, $feature = 'pro' ) {
  $level = get_user_meta( $user_id, 'ap_upgrade_level', true );
  if ( $feature === 'pro' ) {
    return $level === 'pro' || $level === 'partner';
  }
  return false;
}
```
Call this helper before displaying premium components.

## Upgrade Comparison UI
Create an `/upgrade` page using `[ap_upgrade_compare]`.

| Feature | Free | Pro | Partner |
|---------|-----|-----|---------|
| Event Listings | ✅ | ✅ | ✅ |
| CRM Tools | ❌ | ✅ | ✅ |
| Weekly Analytics | ❌ | ✅ | ✅ |
| Grant Reporting | ❌ | ❌ | ✅ |
| Premium Badge | ❌ | ✅ | ✅ |
| Monthly Cost | $0 | $5 | Custom |

Logged-in users see a Stripe link to upgrade; others are prompted to log in.

## QA Checklist
- User upgrades via Stripe → role and level updated
- Org shows Pro badge if upgraded
- Locked features prompt upgrade
- Plan expiration disables premium features
- Downgrade path clearly explained

## Developer Checklist
- Stripe products configured
- Webhook route and handler implemented
- Upgrade metadata stored
- Upgrade comparison page built
- Feature checks integrated with `ap_user_has_upgrade()`

### Codex Docs To Update
- Monetization
- Pricing Tiers
- Feature Gating Helpers
- Admin Tools
- Stripe Webhook Setup
- Manual plan assignment UI
- UI Components (UpgradeCompare.jsx, Pro Badge Renderer, Partner Onboarding)

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
