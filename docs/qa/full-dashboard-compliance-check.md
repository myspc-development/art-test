---
title: Full Dashboard Compliance Check (All Roles)
category: qa, architecture, spec
audience: qa, developer, maintainer
status: active
last_updated: 2025-07-28
---

# ✅ Codex Prompt: Full Dashboard Compliance Check (All Roles)

This prompt is used to **fully verify** that the dashboards for all key roles — `member`, `artist`, `organization`, and `admin` — are implemented per documented spec, use WordPress standards, and render expected widgets and layouts correctly.

---

## 🎯 Objective

- Confirm dashboards are implemented, rendered, and persisted per spec
- Detect layout or widget rendering failures
- Suggest corrections if shortcode/template/REST logic is missing
- Flag and fix documentation inconsistencies

---

## 🧩 1. ROLE-BASED DASHBOARD RENDERING (HTML / Shortcode / Template)

For each role:

| Role         | Shortcode Used          | Template Exists | Route Loads | Dashboard Visible |
|--------------|-------------------------|-----------------|-------------|-------------------|
| `member`     | `[ap_member_dashboard]` | ✅/❌            | `/member-dashboard` | ✅/❌        |
| `artist`     | `[ap_artist_dashboard]` | ✅/❌            | `/artist-dashboard` | ✅/❌        |
| `organization`| `[ap_org_dashboard]`   | ✅/❌            | `/organization-dashboard` | ✅/❌  |
| `admin`      | admin tools, builder    | ✅               | `/wp-admin/...` | ✅             |

- [ ] Confirm pages exist in WordPress (`Pages > All Pages`)
- [ ] Confirm shortcodes output valid layout HTML
- [ ] Templates use `get_header()`, `get_footer()`, `the_content()`

---

## 🧰 2. LAYOUT STORAGE & PRESET BEHAVIOR

- [ ] Layouts saved in `ap_dashboard_widget_config` for each role
- [ ] Individual user layout overrides saved in `ap_dashboard_layout` user meta
- [ ] Reset layout button restores default preset
- [ ] `GET /wp-json/artpulse/v1/layout/{role}` returns ordered widget IDs
- [ ] `POST /layout/{role}` saves changes and reflects on reload

---

## 🧱 3. WIDGET REGISTRY COMPLIANCE

- [ ] Each widget uses `DashboardWidgetRegistry::register()`
- [ ] `roles` are properly declared (`roles => ['member']`, etc.)
- [ ] Widgets contain:
  - `label`
  - `description`
  - `callback` or `component`
  - Optional: `settings`, `category`, `capability`
- [ ] No widget appears for an unauthorized role

---

## 🖼️ 4. UI/UX VERIFICATION

- [ ] Dashboard grid is responsive
- [ ] `.ap-card` and `.ap-card__title` used for styling
- [ ] Drag-and-drop builder works (if enabled for role)
- [ ] Empty state shown if no widgets available
- [ ] First-time user walkthrough appears if configured

---

## 📡 5. REST & BACKEND CHECKS

- [ ] `GET /layout/{role}` and `GET /widgets` return consistent data
- [ ] Widget IDs in layout match actual registered widgets
- [ ] Layout updates (`POST`) succeed and persist
- [ ] Admin can impersonate or preview role layout (if feature exists)

---

## 📄 6. DOCUMENTATION AUDIT

| Doc | Exists | Up-to-date | Missing Sections |
|-----|--------|-------------|------------------|
| `dashboard-widget-index.md` | ✅ | ✅/❌ | role filtering |

- [ ] Create missing role guides based on actual dashboard behavior
- [ ] Add layout schema example to widget codex
- [ ] Link role dashboards from `docs/guides/user/README.md` and `codex_index.md`

---

## ✅ ACTION PLAN

If non-compliance is found:

1. **Shortcode or template missing** → Add shortcode handler and WP page template
2. **Layout not saving** → Patch REST layout controller or fallback preset logic
3. **Widget visibility incorrect** → Update widget `roles` in registration
4. **Docs missing** → Create overview guide and cross-link it

---

> Document findings in `docs/issues/` and summarize all fixes in `CHANGELOG.md`.
