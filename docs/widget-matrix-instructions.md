# 🛠 Codex Instructions: Refactor & Implement Admin Widget Role Matrix

## 🎯 Goal
Build a UI in the WordPress admin panel that allows admins to define which widgets are visible for each user role.

The admin page appears under **ArtPulse → Widget Matrix** with the submenu slug
`artpulse-widget-matrix`. To change widget placement and ordering, use the
[Admin Dashboard Widgets Editor Guide](./Admin_Dashboard_Widgets_Editor_Guide.md)
via **ArtPulse → Widget Editor** (`artpulse-widget-editor`).

---

## ✅ 1. Refactor and Prepare the Codebase
- Centralize widget role logic via utility function
- Ensure widgets include `roles[]` in the registry

---

## 2. Implement REST API Endpoint
- Define `GET /wp-json/artpulse/v1/dashboard-config`
- Define `POST /wp-json/artpulse/v1/dashboard-config`
- Use `update_option('artpulse_widget_roles', $matrix);`

---

## 3. Build React Admin UI
- Matrix table with checkboxes per widget/role
- Persist changes via REST POST
- Feedback with save toast

---

## 4. Update Frontend Dashboard
- Filter widgets using saved matrix per role
- Fallback to registry defaults if config missing

---

## 5. QA & Test
- Admin UI renders and saves correctly
- Users only see allowed widgets
- Layout doesn't break
- Locked widgets enforced (if applicable)
