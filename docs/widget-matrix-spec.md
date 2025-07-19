# 📐 Admin Widget Role Matrix Spec

This document defines the full technical and UX spec for implementing the **Admin Widget Role Matrix** for the ArtPulse dashboard system.

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
Returns current matrix from WP `option`

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

## 🎨 Admin UX Enhancements (Optional)
- [ ] Tooltip or preview for each widget row
- [ ] Bulk enable/disable column buttons
- [ ] Sticky header for widget names
- [ ] Save toast confirmation

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
- [ ] Matrix loads from saved config on admin load
- [ ] All widgets render in table
- [ ] Changes persist to backend correctly
- [ ] Dashboard reflects updated role permissions
- [ ] Invalid roles or widgets are ignored
- [ ] Unit + integration test coverage present

---