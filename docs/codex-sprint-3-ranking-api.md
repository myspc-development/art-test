---
title: Codex: Sprint 3 – Event Ranking
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# Codex: Sprint 3 – Event Ranking

## Overview
Introduces the event ranking algorithm used to surface quality content.

## Goals
- Rank events based on engagement signals.

## REST Routes
- `GET /api/v1/events?orderby=rank` – Ranked event list.
- `GET /api/v1/analytics/events` – Optional analytics summary.

## Database Changes
- `ap_event_rankings` table for scores.

## QA Checklist
- Ranking table populated and used by `/events?orderby=rank`.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
