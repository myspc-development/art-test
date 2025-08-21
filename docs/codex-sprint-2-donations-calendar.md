---
title: Codex: Sprint 2 – Donations & Calendar
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# Codex: Sprint 2 – Donations & Calendar

## Overview
Adds donation processing endpoints and a simple calendar feed.

## Goals
- Enable visitors to support artists via donations.
- Provide iCal/JSON feed for event listings.

## REST Routes
- `POST /artpulse/v1/donations` – Process donations.
- `GET /ap/v1/calendar` – Calendar feed of upcoming events.

## Database Changes
- `ap_donations` table storing payment references.

## Frontend Components
- Donation form widget using Stripe.
- Calendar view component showing monthly events.

## QA Checklist
- Donations endpoint accepts POST requests.
- Calendar feed returns valid JSON list.
- Donation form displays success/failure states.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
