# Codex: Sprint 1 – Mapping & Q&A

## Overview
This sprint introduced geolocation search and basic Q&A threads for events.

## Goals
- Allow visitors to filter events by location.
- Provide a discussion area per event using `qa_thread` posts.

## REST Routes
- `GET /artpulse/v1/events?lat={lat}&lng={lng}&within_km={km}` – Map filter.
- `GET /artpulse/v1/qa-thread/{event}` – Fetch Q&A posts.

## Database Changes
- `ap_event_rankings` table created to store calculated scores.

## Frontend Components
- Map filter widget displaying nearby events.
- Q&A thread component under event pages.

## QA Checklist
- Map filter returns an array of events.
- `qa_thread` CPT registered and visible in admin.
- `/qa-thread/{event}` route returns JSON.
- Rankings table exists in database.
- Widget script outputs an iframe.

