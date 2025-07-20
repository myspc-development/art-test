---
title: Social Discovery Layer
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# Social Discovery Layer

This module surfaces nearby events, followed artists and trending artworks to encourage exploration.

## Location-Based Feed

The `GET /artpulse/v1/nearby` endpoint accepts `lat`, `lon` and `radius` parameters and returns events sorted by distance. If geolocation is not available the profile postal code is used as a fallback.

## Follow System

Users can follow artists, events or tags via the `/artpulse/v1/follows` REST route. Followed items appear in the personalized home feed and counts are stored in the `ap_follows` table.

## Trending Engine

An hourly worker stores scores in `ap_trending_rankings`. Scores use view, wishlist, share and comment counts over the last two days. Clients may fetch the top items with `GET /artpulse/v1/trending?type=artwork&limit=20`.

## Unified Home Feed

The front end combines sections for nearby events, trending items and followed content. Each section can be toggled with feature flags.

💬 Found something outdated? Submit Feedback
