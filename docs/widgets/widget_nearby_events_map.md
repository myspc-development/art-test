---
title: Nearby Events
category: widgets
role: developer
last_updated: 2025-07-24
status: complete
---

# Nearby Events

**Widget ID:** `widget_nearby_events_map`

**Roles:** member, artist

## Description
Events around your location.

The widget uses the browser's geolocation API to center the map and fetch events within a 50km radius via `/events/nearby`. When permission is denied it falls back to the site default location.

### Customization
- Override the search radius with the `radius` attribute in the widget settings.
- Enable map clustering by setting `clusterMarkers` to `true` when registering the block.

