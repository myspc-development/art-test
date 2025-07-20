---
title: Event Chat
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# Event Chat

This module provides a lightweight real‑time chat for event pages.

## REST Endpoints

`GET  /wp-json/artpulse/v1/event/{id}/chat`
: Retrieve recent chat messages for an event.

`POST /wp-json/artpulse/v1/event/{id}/chat`
: Post a new chat message. Requires authentication and the user must be RSVP'd for the event.

## Shortcode

Insert `[ap_event_chat id="123"]` on any page to display the chat widget.
The included `ap-event-chat.js` script polls the REST endpoint at regular intervals,
scheduling each new request only after the previous one completes. Messages are still submitted via AJAX.