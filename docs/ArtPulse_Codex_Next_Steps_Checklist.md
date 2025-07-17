
# ✅ Next Tasks for Codex – ArtPulse Member Dashboard

These are immediate follow-up actions aligned with the roadmap to begin actual development and integration.

---

## 🔧 1. Backend Setup (WordPress PHP)

### a. Register Post Types
- [ ] Create `event` and `message` post types with needed metadata fields (e.g. location, capacity).
- [ ] Optional: `news_item` post type for feed integration.

```php
register_post_type('event', [ /* args with 'supports' => [title, editor, custom-fields] */ ]);
```

### b. Add REST API Endpoints
- [ ] `/events/nearby` (GET) → with geolocation filtering
- [ ] `/event/{id}/rsvp` (POST) → store RSVP status for user
- [ ] `/user/{id}/follow` (POST) → manage following/favorites
- [ ] `/event/{id}/message` (POST/GET) → post/view comments or questions

---

## 📦 2. React Widget Setup (Frontend)

### a. Scaffold React Widgets
Start from base components (or `create-react-app` build inside plugin folder).

- [ ] `NearbyEventsMapWidget.jsx`
- [ ] `RSVPButton.jsx`
- [ ] `EventChatWidget.jsx`

Use:
```bash
npm install leaflet react-leaflet @testing-library/react
```

---

## 🎯 3. Widget Registration in WordPress
- [ ] Register new widgets with WP dashboard
- [ ] Expose widget configuration in Admin Widget Editor
- [ ] Add visibility toggles and role targeting

---

## 🧪 4. Testing Framework Extensions
- [ ] Extend `phpunit.xml` to cover new RSVP and follow APIs
- [ ] Add test cases for REST permission validation and return structure
- [ ] Create Jest test stubs for new widgets (render, prop usage, user interactions)

---

## 🧩 5. UI Mockup & Data Binding
- [ ] Create placeholder layout (e.g. mock dashboard page)
- [ ] Bind real-time data to widgets from backend
- [ ] Ensure widgets respect visibility rules from dashboard layout JSON

---

## 🔐 6. Access Control
- [ ] Ensure only logged-in users can:
  - RSVP
  - Message
  - Follow/Favorite
- [ ] Use `wp_create_nonce()` and `check_ajax_referer()` in each AJAX endpoint

---

## 📘 7. Developer Docs
- [ ] Document each widget component (`.md` or JSDoc)
- [ ] Include usage examples, props, data format, REST endpoints
- [ ] Update `README.md` and `Widget Editor Guide` with new capabilities
