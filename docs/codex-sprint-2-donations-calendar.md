# Codex: Sprint 2 – Donations & Calendar

## Overview
Adds donation processing endpoints and a simple calendar feed.

## Goals
- Enable visitors to support artists via donations.
- Provide iCal/JSON feed for event listings.

## REST Routes
- `POST /artpulse/v1/donations` – Process donations.
- `GET /artpulse/v1/calendar` – Calendar feed of upcoming events.

## Database Changes
- `ap_donations` table storing payment references.

## Frontend Components
- Donation form widget using Stripe.
- Calendar view component showing monthly events.

## QA Checklist
- Donations endpoint accepts POST requests.
- Calendar feed returns valid JSON list.
- Donation form displays success/failure states.

