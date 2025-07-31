---
title: Dashboard Rendering Audit & Standardization
category: qa
role: developer
last_updated: 2025-07-28
status: complete
---

# 🧾 ArtPulse Codex Prompt: Dashboard Rendering Audit & Standardization

This prompt is used to evaluate how dashboards are rendered for the `member`, `artist`, and `organization` roles and ensure that layout logic, widget injection, and UI structure conform to WordPress and ArtPulse architectural standards.

## 🎯 Objectives

- Ensure dashboards render properly per role.
- Align rendering logic with WordPress default template hierarchy where possible.
- Identify missing or redundant logic in template files or REST.
- Propose standardizations to improve maintainability.
- Flag outdated documentation or missing Codex pages.

## ✅ 1. Role-Based Template Check

For each role (`member`, `artist`, `organization`):

- [ ] Confirm if dashboard is rendered via:
  - [ ] Custom page template (e.g. `dashboard-member.php`)
  - [ ] Shortcode (e.g. `[ap_member_dashboard]`)
  - [ ] REST + React front end (e.g. `ReactDashboardShortcode.php`)
- [ ] Check if fallback is defined for missing layout or widgets
- [ ] Ensure layout template uses `get_header()`, `get_footer()` and follows WP structure

Example expected files:
- `templates/dashboard-member.php`
- `templates/dashboard-organization.php`

## 🧩 2. Widget Injection & Rendering

- [ ] Confirm widgets are conditionally loaded based on role (`DashboardWidgetRegistry`)
- [ ] Ensure fallback message or empty state exists if no widgets
- [ ] Each widget’s callback or component renders inside proper container (e.g. `.ap-card`)
- [ ] Role-specific layouts stored in:
  - [ ] `ap_dashboard_widget_config` (global)
  - [ ] `ap_dashboard_layout` (per user)

## 🔁 3. Standardize to WordPress Dashboard Conventions

Where possible:
- [ ] Use WordPress templates (`page-{slug}.php`) instead of hardcoded routes
- [ ] Replace redundant template logic with `template_include` filter
- [ ] Register dashboard pages using `add_menu_page()` or `add_submenu_page()` (if needed for admin UX)
- [ ] Ensure all user dashboards are created as **Pages** in WP admin with appropriate slugs

## 📡 4. REST Endpoint Validation

- [ ] `GET /wp-json/artpulse/v1/layout/{role}` returns expected widget layout
- [ ] `GET /wp-json/artpulse/v1/widgets` filters correctly by current user role
- [ ] Saving layout (`POST`) persists and reflects visually on refresh
- [ ] Missing layout falls back to default preset

## 🧪 5. Visual + Functional Checks

- [ ] Dashboard renders at 1920px, 1280px, 768px
- [ ] Drag-drop works if builder enabled
- [ ] “Reset Layout” and “Edit Layout” buttons function per role permissions
- [ ] No PHP or JS errors in console

## 🛠️ 6. Update or Create Documentation

- [ ] Confirm or update:
  - [ ] `artist-dashboard-overview.md`
  - [ ] `ArtPulse_Member_Dashboard_Roadmap.md`
  - [ ] `org-dashboard-widget-visibility.md`
  - [ ] `dashboard-widget-index.md`
- [ ] If missing, create:
  - [ ] `member-dashboard-overview.md`
  - [ ] `organization-dashboard-overview.md`
- [ ] Link all new/updated pages from:
  - [ ] `README.md`
  - [ ] `docs/guides/user/README.md`
  - [ ] `docs/admin/admin-dashboard-ui.md`

## 📎 Notes

> If a role has no unique template or uses generic widgets, recommend using `template-parts/dashboard-role.php` and injecting role via `get_current_user_role()`. This simplifies template management.

> Log all changes in `CHANGELOG.md` under "Dashboard Rendering Standardization".

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*

