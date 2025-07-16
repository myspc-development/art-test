# Codex: Sprint I2 – Sponsored Event Boosting

## Overview
This sprint introduces a simple monetization feature that allows organizers to pay for additional visibility. Boosted events surface higher in feeds, maps and email digests.

## Goals
- **Boost button** – artists and galleries can purchase a boost
- **Checkout flow** – use Stripe for one‑time payments or deduct a credit balance
- **Ranking logic integration** – boosted events appear earlier in discovery
- **Boost badge & logging** – mark boosted listings and track their impressions

## Data Model
Create `ap_event_boosts`:

```sql
CREATE TABLE ap_event_boosts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id BIGINT,
  user_id BIGINT,
  amount DECIMAL(6,2),
  method VARCHAR(20),
  boosted_at DATETIME,
  expires_at DATETIME
);
```

## REST Endpoints
- `POST /artpulse/v1/boost/create-checkout` – create a Stripe checkout session
- `POST /artpulse/v1/boost/webhook` – process `checkout.session.completed`
- Optional helper `ap_boost_with_credits($user_id, $event_id)` to use credits

## Ranking & Discovery
- Apply a bonus inside the ranking algorithm: `$boost_bonus = ($event_is_boosted) ? 25 : 0;`
- Boosted events can be pinned within the top three positions while active

## Frontend Components
- Boost CTA shown on event edit pages or the organization dashboard
- Purchase screen with duration options and Stripe checkout
- Boost status badge: `<span class="badge-boosted">🔥 Boosted</span>`

## Reporting & Stats
- Increment `boosted_views` post meta when a boosted event is viewed
- Include boosted performance summaries in admin analytics

## QA Checklist
- Boost purchase via Stripe marks the event as boosted
- Boosted event rises in feed and map results
- Boost badge is visible on listings
- Boost expires after seven days
- Multiple boosts extend the time window but not the ranking bonus
- Stripe webhook completes with no errors

## Developer Sprint Checklist
- [x] `ap_event_boosts` table created
- [ ] REST endpoints for checkout and webhook
- [ ] Ranking integration updated
- [ ] Boost UI and badges implemented
- [ ] Stats logging added
- [ ] Codex docs updated

## Codex Docs to Update
- Monetization
- Event Boosting
- Checkout vs. Credits
- Feed Logic
- Ranking Adjustments
- Boost Prioritization
- UI Components
- Partner Toolkit – “How to Promote Your Event” guide
