---
title: Codex: Sprint 3 – Event Ranking & API
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# Codex: Sprint 3 – Event Ranking & API

## Overview
Introduces event ranking algorithm and partner-facing API with bearer auth.

## Goals
- Rank events based on engagement signals.
- Allow partners to query events via a secure API.

## REST Routes
- `GET /api/v1/events?orderby=rank` – Ranked event list.
- `GET /api/v1/analytics/events` – Optional analytics summary.

## Database Changes
- `ap_event_rankings` table for scores.
- `ap_api_keys` table storing hashed API keys.

## Frontend Components
- Admin screen for API key management.

## QA Checklist
- Ranking table populated and used by `/events?orderby=rank`.
- API requests with a valid key succeed.
- Requests without a key return 401 errors.

💬 Found something outdated? Submit Feedback
