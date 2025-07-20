---
title: Widget Editor Developer Guide
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---

# Widget Editor Developer Guide

Combines implementation notes from the Dashboard Editor guide and Codex
instructions. Covers adding new widgets, REST endpoints and verification
checklists.

### Role Examples

The editor supports unique layouts for every role. For artists you might register widgets like `artwork_manager` and `payout_summary` so creators can monetize their work. Organizations often include `ticketing_overview` and `analytics_report` widgets for event tracking.

Preview mode loads the full dashboard in an iframe. Admins can toggle roles and adjust base widget styles. POST requests to `/layout/{role}` now accept a `style` object along with the layout array. React sources live in `assets/js/widget-editor/WidgetEditorApp.jsx` and are bundled to `build/widget-editor-ui.js`.

🔗 View available widgets and role visibility in the [Widget Matrix](../widgets/widget-matrix-reference.md).

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
