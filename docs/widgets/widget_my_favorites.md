---
title: My Favorite Events
category: widgets
role: developer
last_updated: 2025-07-24
status: complete
---

# My Favorite Events

**Widget ID:** `widget_my_favorites`

**Roles:** member, artist

## Description
Your saved events.

This widget displays events saved by the user. It fetches data from `/wp-json/artpulse/v1/favorites/events` and lists them chronologically.

### Customization
- Adjust the number of events returned using the `limit` parameter when rendering the block.
- The widget can be repositioned in the Dashboard Builder like any other tile.

