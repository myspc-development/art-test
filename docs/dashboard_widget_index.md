---
title: Dashboard Widget Index
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# Dashboard Widget Index

This document lists dashboard widgets and REST endpoints available for the ArtPulse member dashboard.

## Widgets

- **RSVPButton.jsx** – Toggle RSVP status via `/rsvp` and `/rsvp/cancel`.
- **EventChatWidget.jsx** – Chat UI fetching `/event/{id}/chat` and posting `/event/{id}/message`.
- **ShareThisEventWidget.jsx** – Social sharing and copy link UI.
- **ArtPulseNewsFeedWidget.php** – Dashboard feed showing posts from followed artists.

## REST Endpoints

- `GET /artpulse/v1/events/nearby`
- `POST /artpulse/v1/rsvp`
- `POST /artpulse/v1/rsvp/cancel`
- `POST /artpulse/v1/user/{id}/follow`
- `POST /artpulse/v1/event/{id}/message`

These endpoints power the interactive dashboard widgets. See [ArtPulse_Member_Dashboard_Roadmap.md](ArtPulse_Member_Dashboard_Roadmap.md) for further details.
