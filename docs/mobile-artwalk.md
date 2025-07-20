---
title: ArtPulse Codex: Mobile ArtWalk Guide
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# ArtPulse Codex: Mobile ArtWalk Guide

This guide details the PWA and mobile crawl features aimed at public art walks and museum nights.

## 1. Progressive Web App
- Installable from the browser with an offline shell.
- Uses cached assets so event info loads even with poor connectivity.

## 2. Nearby Mode
- Auto-detects the visitor's location and lists events within 5&nbsp;km.
- Works anonymously so anyone can explore local programming.

## 3. Crawl Builder
- Users add stops to a nightly route.
- Pins show on the map and can be exported to a calendar file.

## 4. Swipeable UI
- Mobile layout is optimized for quick swipes between events.
- A sticky call to action stays visible when scrolling.

## Developer Checklist
- PWA manifest and service worker registered.
- `/events` endpoint accepts `lat` and `lng` for nearby filtering.
- Crawl planner persists selected events in local storage.
