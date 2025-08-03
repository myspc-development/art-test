---
title: Dashboard Compliance & Role-Based Verification
category: qa, architecture, spec
audience: qa, developer, maintainer
status: active
last_updated: 2025-07-29
---

# ✅ Codex Prompt: Dashboard Compliance & Role-Based Verification

This prompt ensures that dashboards for all key roles — `member`, `artist`, `organization`, and `admin` — comply with documented specs, render correctly, enforce permissions, and expose the expected widgets, layouts, and REST integrations.

## 🧑‍💼 Roles Under Test

- [ ] `admin`
- [ ] `artist`
- [ ] `member`
- [ ] `organization`

---

## 📋 1. DASHBOARD ACCESS & RENDERING

For each role:
- Confirm pages exist in WordPress (`Pages > All Pages`)
- Verify shortcode renders valid layout HTML
- Templates use `get_header()`, `get_footer()`, `the_content()`
- Role-specific layout preset loads on first visit

| Role         | Shortcode Used          | Template Exists | Route Loads | Dashboard Visible |
|--------------|-------------------------|-----------------|-------------|-------------------|
| `member`     | `[ap_member_dashboard]` | ✅/❌            | `/member-dashboard` | ✅/❌ |
| `artist`     | `[ap_artist_dashboard]` | ✅/❌            | `/artist-dashboard` | ✅/❌ |
| `organization`| `[ap_org_dashboard]`   | ✅/❌            | `/organization-dashboard` | ✅/❌ |
| `admin`      | admin tools, builder    | ✅               | `/wp-admin/...` | ✅ |

---

## 🧩 2. WIDGET PRESENCE & VISIBILITY

For each role:
- Load `GET /wp-json/artpulse/v1/widgets` and confirm only role-appropriate widgets appear
- Visually confirm widgets on dashboard match REST list
- Widgets render expected content; hidden widgets do not appear

Examples:
- Artist → **Artist Earnings**, **Audience Insights**
- Member → **My Events**, **Favorites Feed**
- Organization → **Ticketing Stats**, **Event Analytics**
- Admin → **All widgets visible** via "Show All"

Additionally:
- Each widget uses `DashboardWidgetRegistry::register()`
- `roles` are properly declared (`roles => ['member']`, etc.)
- Widgets contain:
  - `label`
  - `description`
  - `callback` or `component`
  - Optional: `settings`, `category`, `capability`
- No widget appears for an unauthorized role

---

## 🧰 3. LAYOUT STORAGE & BUILDER BEHAVIOR

- Layouts saved in `ap_dashboard_widget_config` for each role
- Individual user layout overrides saved in `ap_dashboard_layout` user meta
- Open Dashboard Builder (`Edit Layout`)
- Add, remove, resize, and reorder widgets in the 12-column grid
- `POST /layout/{role}` saves changes and reflects on reload
- Reset Layout button restores default preset
- Switching roles in builder shows updated widget list

---

## 🔐 4. PERMISSIONS & ACCESS CONTROL

- Each widget has `roles` and/or `capability` constraint (check via code or `/widgets`)
- Role cannot load widgets outside allowed list
- Admin can impersonate or preview other roles (if feature exists)
- `GET /layout/{role}` returns correct default layout structure

---

## 📡 5. API RESPONSE VALIDATION

For each role:
- `GET /layout/{role}` returns expected widgets in order
- `POST /layout/{role}` updates persist
- `GET /widgets` only shows role-appropriate widgets
- Widget IDs in layout match registered widgets

---

## 🎨 6. UI/UX & ACCESSIBILITY

- Dashboard grid is responsive at 1920px, 1280px, and 768px widths
- `.ap-card` and `.ap-card__title` used for styling
- Drag-and-drop builder works (if enabled for role)
- Empty state shown if no widgets available
- First-time user walkthrough appears if configured
- ARIA labels and focus states present for keyboard navigation
- Custom branding/theme applies correctly to widgets and layout

---

## 📄 7. DOCUMENTATION AUDIT

| Doc | Exists | Up-to-date | Missing Sections |
|-----|--------|-------------|------------------|
| `dashboard-widget-index.md` | ✅ | ✅/❌ | role filtering |

- Create missing role guides based on actual dashboard behavior
- Add layout schema example to widget codex
- Link role dashboards from `docs/guides/user/README.md` and `codex_index.md`

---

## 🧪 8. TEST OUTCOMES

| Role        | Dashboard Loaded | Widgets Rendered | Builder Functional | REST Validated | Notes |
|-------------|------------------|------------------|--------------------|----------------|-------|
| Admin       | ✅                | ✅                | ✅                  | ✅              |       |
| Artist      | ✅                | ✅                | ✅                  | ✅              |       |
| Member      | ✅                | ✅                | ✅                  | ✅              |       |
| Organization| ✅                | ✅                | ✅                  | ✅              |       |

> Log discrepancies in `docs/issues/` or `feedback.md`.

---

## ✅ ACTION PLAN

If non-compliance is found:
1. **Shortcode or template missing** → Add shortcode handler and WP page template
2. **Layout not saving** → Patch REST layout controller or fallback preset logic
3. **Widget visibility incorrect** → Update widget `roles` in registration
4. **Docs missing** → Create overview guide and cross-link it

> Document findings in `docs/issues/` and summarize all fixes in `CHANGELOG.md`.

---

## 🧾 REFERENCES
- [`dashboard-widget-design-codex.md`](../dashboard-widget-design-codex.md)
- [`guides/developer/ui-ux-polish-codex.md`](../guides/developer/ui-ux-polish-codex.md)
- [`admin/admin-dashboard-ui.md`](../admin/admin-dashboard-ui.md)
- [`internal/dashboard-builder-release-summary.md`](../internal/dashboard-builder-release-summary.md)
- [`dashboard_widget_index.md`](../dashboard_widget_index.md)

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
