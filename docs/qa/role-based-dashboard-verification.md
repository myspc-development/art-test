---
title: Codex QA Prompt – Role-Based Dashboard Verification
category: qa
role: qa
last_updated: 2025-07-28
status: complete
---

# ✅ Codex QA Prompt – Role-Based Dashboard Verification

This QA prompt ensures that each user role (Admin, Artist, Member, Organization) can view their dashboard correctly, and that widgets, permissions, layout presets, and REST integrations function as defined in documentation.

---

## 🧑‍💼 ROLES UNDER TEST

- [ ] `admin`
- [ ] `artist`
- [ ] `member`
- [ ] `organization`

---

## 📋 1. DASHBOARD ACCESS

For each role:
- [ ] Log in and navigate to the dashboard (e.g. `/admin-dashboard`, `/artist-dashboard`)
- [ ] Verify presence of role-specific layout and preset
- [ ] Confirm shortcode (e.g., `[ap_artist_dashboard]`) renders the correct structure

---

## 🧩 2. WIDGET PRESENCE & VISIBILITY

For each role:
- [ ] Load `GET /wp-json/artpulse/v1/widgets` and confirm only widgets assigned to that role appear
- [ ] Visually confirm widgets on dashboard match REST list
- [ ] Each widget renders content correctly (e.g., data not empty or misaligned)
- [ ] Hidden widgets (based on roles or capabilities) do not render

Examples:
- Artist → **Artist Earnings**, **Audience Insights**
- Member → **My Events**, **Favorites Feed**
- Organization → **Ticketing Stats**, **Event Analytics**
- Admin → **All widgets visible** via "Show All"

---

## 🧰 3. DASHBOARD BUILDER BEHAVIOR

- [ ] Open Dashboard Builder (`Edit Layout`)
- [ ] Add, remove, resize widgets in the 12-column grid
- [ ] Drag-and-drop reordering is functional
- [ ] Save layout → persists via `POST /layout/{role}` or user meta
- [ ] Reset Layout returns to default preset
- [ ] Switching roles in the builder shows updated widget list

---

## 🔐 4. PERMISSIONS & ACCESS CONTROL

- [ ] Each widget has `roles` and/or `capability` constraint (check via code or `/widgets`)
- [ ] Role cannot load widgets outside allowed list
- [ ] Admin can impersonate or preview other roles (if feature enabled)
- [ ] `GET /layout/{role}` returns correct default layout structure

---

## 🔁 5. API RESPONSE VALIDATION

For each role:
- [ ] `GET /layout/{role}` → returns expected widgets in order
- [ ] `POST /layout/{role}` → updates reflect on reload
- [ ] `GET /widgets` → only shows role-appropriate widgets

---

## 🎨 6. UI/UX & ACCESSIBILITY

- [ ] Widget cards use `.ap-card` and `.ap-card__title`
- [ ] Responsive layout confirmed at 1920px, 1280px, and 768px widths
- [ ] ARIA labels and focus states present for keyboard navigation
- [ ] First-time login triggers onboarding tour (if configured)
- [ ] Custom branding/theme applies correctly to widgets and layout

---

## 🧪 7. TEST OUTCOMES

| Role        | Dashboard Loaded | Widgets Rendered | Builder Functional | REST Validated | Notes |
|-------------|------------------|------------------|--------------------|----------------|-------|
| Admin       | ✅                | ✅                | ✅                  | ✅              |       |
| Artist      | ✅                | ✅                | ✅                  | ✅              |       |
| Member      | ✅                | ✅                | ✅                  | ✅              |       |
| Organization| ✅                | ✅                | ✅                  | ✅              |       |

> Log discrepancies in `docs/issues/` or `feedback.md`.

---

## 🧾 REFERENCES
- [`dashboard-widget-design-codex.md`](../dashboard-widget-design-codex.md)
- [`ui-ux-polish-codex.md`](../guides/developer/ui-ux-polish-codex.md)
- [`admin-dashboard-ui.md`](../admin/admin-dashboard-ui.md)
- [`dashboard-builder-release-summary.md`](../internal/dashboard-builder-release-summary.md)
- [`dashboard-widget-index.md`](../dashboard_widget_index.md)

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
