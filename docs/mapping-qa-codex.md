---
title: ArtPulse Codex: Mapping & Q&A Threads
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# ArtPulse Codex: Mapping & Q&A Threads

This codex outlines the sprint 1 features for interactive maps and time-bound Q&A threads.

## 1. Map Component & Distance Filter

### Backend

- Add `\_geo_lat` and `\_geo_lng` meta fields to `artpulse_event` posts.
- Extend the `/artpulse/v1/events` endpoint with `lat`, `lng` and `within_km` parameters. Events are filtered using the Haversine formula.
- REST responses include a `location` field with the latitude and longitude.

### Frontend

- The `GeoMap.jsx` component displays events on an interactive map (Leaflet or Mapbox). Markers cluster by location and open a preview on click.
- The component appears on the discovery page and can fetch the user's location to filter events within a radius.

### QA Checklist

- Ensure REST returns only events within the chosen radius.
- Map pins match event addresses and open an info window on click.
- Map loads in under two seconds on broadband.

## 2. Q&A Thread System

### Backend

- Register the `qa_thread` custom post type with `start_time`, `end_time`, `event_id` and `participants` meta fields.
- Provide `/qa-thread/{event_id}` to load comments and `/qa-thread/{id}/post` to submit a message if the user is allowed and the thread is open.
- Threads automatically lock after `end_time` via cron or runtime checks.

### Frontend

- `QAThread.jsx` renders messages chronologically with markdown support. Artists and curators are highlighted.
- Only listed participants may post, while all users can read. Posting is disabled once closed.

### QA Checklist

- Thread appears only after `start_time`.
- Authorized users can post during the open window.
- Thread locks after `end_time` and posts remain visible.

## Developer Sprint Checklist

- REST `/events` route supports `lat`, `lng` and `within_km` filters.
- `GeoMap.jsx` clusters events on a map.
- `qa_thread` post type registered with meta fields.
- `QAThread.jsx` renders threads.
- Lock logic implemented via cron or runtime.