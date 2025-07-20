---
title: Dashboard Editor Developer Guide
category: widgets
role: developer
last_updated: 2025-07-20
status: draft
---

# Dashboard Editor Developer Guide

This document focuses on the APIs and hooks available for extending the React‑based dashboard editor.

## Editor APIs

- `assets/js/admin-dashboard-widgets-editor.js` renders the React interface.
- Layouts are retrieved from `/wp-json/artpulse/v1/ap_dashboard_layout` with a `?user_id=0&role={role}` query when editing defaults.
- Updates post to the `ap_save_dashboard_widget_config` AJAX action using a nonce created on page load.

## Integration Hooks

Use `artpulse_render_settings_tab_widgets` to load custom controls within the editor tab. PHP modules can hook into `ap_dashboard_editor_register_widget` to expose additional widget blocks.

## Developer Functions

`DashboardWidgetRegistry::register()` registers a new widget block and returns its unique ID. `UserLayoutManager::save_user_layout()` persists the ordering for a user or role.

Refer to the [Widget UI Design Guide](ui/widget-ui-design-guide.md) for layout guidelines and examples.
