---
title: ArtPulse Codex Next Steps Checklist
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---


# ✅ Next Tasks for Codex – ArtPulse Member Dashboard

These are immediate follow-up actions aligned with the roadmap to begin actual development and integration.

---

## 🔧 1. Backend Setup (WordPress PHP)

### a. Register Post Types
- [x] Create `event` and `message` post types with needed metadata fields (e.g. location, capacity).
- [x] Optional: `news_item` post type for feed integration.

```php
register_post_type('event', [ /* args with 'supports' => [title, editor, custom-fields] */ ]);
```

### b. Add REST API Endpoints
- [x] `/events/nearby` (GET) → with geolocation filtering
- [x] `/event/{id}/rsvp` (POST) → store RSVP status for user
- [x] `/user/{id}/follow` (POST) → manage following/favorites
- [x] `/event/{id}/chat` (POST/GET) → post/view comments or questions

---

## 📦 2. React Widget Setup (Frontend)

### a. Scaffold React Widgets
Start from base components (or `create-react-app` build inside plugin folder).

- [x] `NearbyEventsMapWidget.jsx`
- [x] `RSVPButton.jsx`
- [x] `EventChatWidget.jsx`

Use:
```bash
npm install leaflet react-leaflet @testing-library/react
```

---

## 🎯 3. Widget Registration in WordPress
- [x] Register new widgets with WP dashboard
- [x] Expose widget configuration in the Dashboard Builder
- [x] Add visibility toggles and role targeting

---

## 🧪 4. Testing Framework Extensions
- [x] Extend `phpunit.wp.xml.dist` to cover new RSVP and follow APIs
- [x] Add test cases for REST permission validation and return structure
- [x] Create Jest test stubs for new widgets (render, prop usage, user interactions)

---

## 🧩 5. UI Mockup & Data Binding
- [x] Create placeholder layout (e.g. mock dashboard page)
- [x] Bind real-time data to widgets from backend
- [x] Ensure widgets respect visibility rules from dashboard layout JSON

---

## 🔐 6. Access Control
- [x] Ensure only logged-in users can:
  - RSVP
  - Message
  - Follow/Favorite
- [x] Use `wp_create_nonce()` and `check_ajax_referer()` in each AJAX endpoint

---

## 📘 7. Developer Docs
- [x] Document each widget component (`.md` or JSDoc)
- [x] Include usage examples, props, data format, REST endpoints
- [x] Update `README.md` and Dashboard Builder docs with new capabilities
For more information on widgets see [Widget Registry Reference](widgets/widget-registry-reference.md) and [Widget Specification](widgets/widget-specification.md). Analytics guidelines live in the [Admin & Analytics Codex](guides/developer/admin-analytics-codex.md).

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
