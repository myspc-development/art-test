---
title: Event Comments
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# Event Comments

This module enables user discussions on event posts using a small REST API and shortcode.

## REST Endpoints

`GET  /wp-json/artpulse/v1/event/{id}/comments`
: Retrieve approved comments for an event.

`POST /wp-json/artpulse/v1/event/{id}/comments`
: Add a new comment to the event. Requires authentication and a `content` field.

`POST /wp-json/artpulse/v1/event/comment/{comment_id}/moderate`
: Change the comment status to `approve`, `spam` or `trash`. Requires the `moderate_comments` capability.

## Shortcode

Insert `[ap_event_comments id="123"]` on any page to display the comment list and a form for logged in users. The form submits via the REST endpoints above.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
