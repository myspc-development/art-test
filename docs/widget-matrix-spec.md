# 📐 Admin Widget Role Matrix Spec

This document defines the full technical and UX spec for implementing the **Admin Widget Role Matrix** for the ArtPulse dashboard system.

The matrix screen lives under **ArtPulse → Settings → Widget Matrix** with slug
`artpulse-widget-matrix`.

---

## 🎯 Objective
Enable WordPress administrators to manage which dashboard widgets are available to each user role (member, artist, organization) through a matrix-style UI.

---

## 🧱 Data Structure
```json
{
  "upcoming-events": ["member", "artist"],
  "portfolio": ["artist"],
  "rsvp-stats": ["organization"]
}
```
- Key = widget ID
- Value = array of allowed user roles

Stored in WordPress via:
```php
update_option('artpulse_widget_roles', $roleMatrix);
```

---

## 🧩 UI Components
### 🔹 Matrix Table
| Widget ID        | Member | Artist | Organization |
|------------------|--------|--------|--------------|
| `upcoming-events`| ✅     | ✅     | ⬜️           |
| `portfolio`      | ⬜️     | ✅     | ⬜️           |

- Admin can toggle checkboxes to assign roles per widget
- Live updates to React state
- Submit saves changes via REST API

---

## 🔌 REST API
### GET Config
```
GET /wp-json/artpulse/v1/dashboard-config
```
Returns current matrix from WP `option`.
The JSON object includes a `widget_roles` property mapping widget IDs to allowed roles.

### POST Config
```
POST /wp-json/artpulse/v1/dashboard-config
```
Payload:
```json
{
  "widget_roles": {
    "portfolio": ["artist"],
    "rsvp-stats": ["organization"]
  }
}
```
Saves to `update_option()`

Permissions: `current_user_can('manage_options')`

---

## 🎨 Admin UX Enhancements
All previously optional enhancements were reviewed. Any features not implemented have been dropped.
- [x] Tooltip or preview for each widget row
- [x] Bulk enable/disable column buttons
- [x] Sticky header for widget names
- [x] Save toast confirmation

---

## ⚙️ Dashboard Filtering Logic (Frontend)
```ts
const userWidgets = allWidgets.filter(w =>
  widgetRoles[w.id]?.includes(currentUserRole)
);
```
Fallback: if no config found, show all widgets registered to that role

---

## ✅ Verification Checklist
- [x] Matrix loads from saved config on admin load
- [x] All widgets render in table
- [x] Changes persist to backend correctly
- [x] Dashboard reflects updated role permissions
- [x] Invalid roles or widgets are ignored
- [x] Unit + integration test coverage present

---