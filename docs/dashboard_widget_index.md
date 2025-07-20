---
title: Dashboard Widget Index
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# Dashboard Widget Index

This document lists dashboard widgets and REST endpoints available for the ArtPulse member dashboard.

## Widgets

- **RSVPButton.jsx** – Toggle RSVP status with `/event/{id}/rsvp`.
- **EventChatWidget.jsx** – Basic chat UI for event conversations.
- **ShareThisEventWidget.jsx** – Social sharing for events.
- **ArtPulseNewsFeedWidget.php** – WordPress dashboard feed widget.

## REST Endpoints

- `GET /artpulse/v1/events/nearby`
- `POST /artpulse/v1/event/{id}/rsvp`
- `POST /artpulse/v1/user/{id}/follow`

These endpoints return placeholder data and will be expanded as the project progresses. See [ArtPulse_Member_Dashboard_Roadmap.md](ArtPulse_Member_Dashboard_Roadmap.md) for implementation details.
