
# 🧭 ArtPulse Member Dashboard – Implementation Roadmap

---

## PHASE 1: Core Setup & Structure

### ✅ 1. Define Custom Post Types & Meta
- [ ] `event`: Post type with geolocation (`event_lat`, `event_lng`), RSVP status, capacity, tags
- [ ] `message`: Post type for messaging threads (linked to events or artists)
- [ ] `news_item` (optional): for curated news

### ✅ 2. Create/Update REST API Endpoints
- [ ] `GET /events/nearby`: Return events with geo + radius filter
- [ ] `POST /event/{id}/rsvp`: RSVP logic with user_id, status
- [ ] `POST /user/{id}/follow`: Follow/unfollow artist/org
- [ ] `POST /event/{id}/message`: Create message thread/comment

---

## PHASE 2: Widget Development

### 🗺️ Event Discovery
- [ ] `NearbyEventsMapWidget.jsx`
  - Uses Leaflet or Google Maps API
  - Pulls via `/events/nearby` with location + date filter

- [ ] `UpcomingEventsByLocationWidget.php`
  - Lists events filtered by city, category, or tags

### ❤️ Following & Favoriting
- [ ] `FollowedArtistsActivityWidget.php`
  - Shows posts/artworks by followed artists/orgs

- [ ] `MyFavoritesWidget.jsx`
  - Async load of user’s saved events or artworks

---

## PHASE 3: Messaging & Interaction

### 💬 Messaging
- [ ] `EventChatWidget.jsx`
  - Pulls and posts messages related to events
  - Threads via `event_id`

- [ ] `ArtistInboxPreviewWidget.php`
  - Previews recent messages to/from artists or orgs

### 📩 RSVP System
- [ ] `RSVPButton.jsx`
  - Toggle RSVP state with `/event/{id}/rsvp`
  - Show capacity and current attendance

- [ ] `MyRSVPsWidget.php`
  - Lists upcoming events user has RSVP’d to

---

## PHASE 4: Sharing & Social Engagement

### 📢 Community Sharing
- [ ] `ShareThisEventWidget.jsx`
  - Social share buttons (Twitter/X, Facebook, LinkedIn)
  - Copy-to-clipboard event URL

- [ ] `MySharedEventsActivityWidget.php`
  - Tracks internal shares to ArtPulse community

---

## PHASE 5: Feed & Content

### 📰 Feed Integration
- [ ] `ArtPulseNewsFeedWidget.php`
  - Shows news and posts from followed artists/orgs

- [ ] `RecommendedForYouWidget.php`
  - Uses follow data, tags, or interaction history

---

## PHASE 6: QA, Testing, and Docs

### 🧪 Testing
- [ ] Add PHPUnit tests for new endpoints (RSVP, follow)
- [ ] Write Jest/Cypress tests for React widgets

### 📘 Documentation
- [ ] Update Widget Editor Guide to include new widgets
- [ ] Add user help tooltips to each widget (optional)

---

## 🔄 Optional Enhancements
- [ ] RSS Feed ingestion for external art news
- [ ] iCal export for RSVP’d events
- [ ] Mentioning other users in comments via `@username` autocomplete
