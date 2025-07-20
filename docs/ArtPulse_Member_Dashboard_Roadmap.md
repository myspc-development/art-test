---
title: 🧭 ArtPulse Member Dashboard – Implementation Roadmap
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---

# 🧭 ArtPulse Member Dashboard – Implementation Roadmap

---

## PHASE 1: Core Setup & Structure

### ✅ 1. Define Custom Post Types & Meta
- [x] `event`: Post type with geolocation (`event_lat`, `event_lng`), RSVP status, capacity, tags
- [x] `message`: Post type for messaging threads (linked to events or artists)
- [x] `news_item` (optional): for curated news

### ✅ 2. Create/Update REST API Endpoints
- [x] `GET /events/nearby`: Return events with geo + radius filter
- [~] `POST /event/{id}/rsvp`: RSVP logic with user_id, status
- [~] `POST /user/{id}/follow`: Follow/unfollow artist/org
- [~] `POST /event/{id}/message`: Create message thread/comment

---

## PHASE 2: Widget Development

### 🗺️ Event Discovery
- [x] `NearbyEventsMapWidget.jsx`
  - Uses Leaflet or Google Maps API
  - Pulls via `/events/nearby` with location + date filter

- [x] `UpcomingEventsByLocationWidget.php`
  - Lists events filtered by city, category, or tags

### ❤️ Following & Favoriting
- [x] `FollowedArtistsActivityWidget.php`
  - Shows posts/artworks by followed artists/orgs

- [x] `MyFavoritesWidget.jsx`
  - Async load of user’s saved events or artworks

---

## PHASE 3: Messaging & Interaction

### 💬 Messaging
- [~] `EventChatWidget.jsx`
  - Pulls and posts messages related to events
  - Threads via `event_id`

- [x] `ArtistInboxPreviewWidget.php`
  - Previews recent messages to/from artists or orgs

### 📩 RSVP System
- [~] `RSVPButton.jsx`
  - Toggle RSVP state with `/event/{id}/rsvp`
  - Show capacity and current attendance

- [x] `MyRSVPsWidget.php`
  - Lists upcoming events user has RSVP’d to

---

## PHASE 4: Sharing & Social Engagement

### 📢 Community Sharing
- [~] `ShareThisEventWidget.jsx`
  - Social share buttons (Twitter/X, Facebook, LinkedIn)
  - Copy-to-clipboard event URL

- [x] `MySharedEventsActivityWidget.php`
  - Tracks internal shares to ArtPulse community

---

## PHASE 5: Feed & Content

### 📰 Feed Integration
- [~] `ArtPulseNewsFeedWidget.php`
  - Shows news and posts from followed artists/orgs

- [x] `RecommendedForYouWidget.php`
  - Uses follow data, tags, or interaction history

---

## PHASE 6: QA, Testing, and Docs

### 🧪 Testing
- [x] Add PHPUnit tests for new endpoints (RSVP, follow)
- [x] Write Jest/Cypress tests for React widgets

### 📘 Documentation
- [x] Update Widget Editor Guide to include new widgets
- [x] Add user help tooltips to each widget (optional)

---

## 🔄 Optional Enhancements
- [x] RSS Feed ingestion for external art news
- [x] iCal export for RSVP’d events
- [x] Mentioning other users in comments via `@username` autocomplete

---

## Implementation Notes

- **Backend setup:** Register the new post types in `src/Core/Plugin.php` and add
  custom meta fields for geolocation, capacity and RSVP status.  REST routes can
  be defined in `UserDashboardManager::registerRestRoutes()` or a dedicated
  route class.
- **Widget integration:** Build the React widgets under `assets/js/widgets/` and
  register them with `DashboardWidgetRegistry::register()`. They will then be
  available in the admin Widget Editor.
- **Dashboard layout:** The file `assets/js/user-dashboard-layout.js` already
  initializes SortableJS. Layout changes are saved via the
  `/wp-json/artpulse/v1/ap_dashboard_layout` endpoint as described in
  `docs/user-dashboard-customization.md`.
- **Testing:** Extend PHPUnit tests under `tests/` for new endpoints and add
  Jest/Cypress tests for the widgets.
- **Documentation:** Update the Admin Widget Editor Guide and User Guide whenever
  new widgets or endpoints are added.

## Completion Plan

1. Implement the Phase 1 post types and REST endpoints.
2. Develop the discovery and favorites widgets from Phase 2 and register them in
   the dashboard.
3. Add messaging functionality and RSVP controls as outlined in Phase 3.
4. Include sharing widgets (Phase 4) and feed recommendations (Phase 5).
5. Expand test coverage and finalize documentation during Phase 6.
6. Evaluate and implement optional enhancements as resources allow.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
