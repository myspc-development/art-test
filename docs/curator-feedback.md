---
title: ArtPulse Codex: Curator Feedback & Field Notes
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# ArtPulse Codex: Curator Feedback & Field Notes

This guide covers tools for curators to record observations and scores during events.

## 1. Private Notes
- Logged-in curators can attach notes to any event they visit.
- Notes remain hidden from the public and other roles.

## 2. Rating Fields
- Optional star ratings or custom metrics.
- Store fields like originality or accessibility alongside each note.

## 3. Visit Timestamping
- Track who visited and when if permissions allow it.
- Each note records the curator ID and visit time.

## 4. Report Export
- Export notes as a filterable list for later review.
- Use CSV or PDF format depending on grant needs.

## Developer Checklist
- `ap_event_notes` table stores `event_id`, `user_id`, `rating` and `note` text.
- REST endpoints secured with capability checks.
- Export route supports filters by date, curator or event.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
