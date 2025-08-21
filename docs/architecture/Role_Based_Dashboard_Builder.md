# Role-Based Dashboard Builder

This document describes the planned Dashboard Builder interface that lets **Member**, **Artist** and **Organization** users customise their dashboards. It extends the existing widget registry and layout system.

## 1. Widget Registry Integration

`DashboardWidgetRegistry` already stores widget definitions. Widgets may include a `roles` array which restricts availability. The builder loads widgets for the current user role by calling `DashboardWidgetRegistry::get_widgets_by_role()` via a REST endpoint. The registry data is injected into the front‑end using `wp_localize_script()` so React components can build the widget palette.

## 2. Dashboard Builder UI

The builder is a React application. `react-grid-layout` provides the drag‑and‑drop grid. Users can:

- Drag widgets around the canvas
- Add widgets from the **Add Widget** panel
- Toggle each widget's visibility
- Resize widgets within the 12‑column grid
- Enable **Show All Widgets** to preview items restricted to other roles

Earlier versions stored a React-based editor in `src/admin/WidgetEditorApp.jsx`, but that application has since been removed.

## 3. Save and Load Layout

Layout state is persisted via REST calls to `/artpulse/v1/ap_dashboard_layout`. Per user layouts are stored in the `ap_dashboard_layout` user meta key. A **Reset to Default** action posts to `/ap/layout/reset` which removes the user meta so role defaults apply.

## 4. Widget Configuration Modal

Widgets may expose a settings schema. Clicking a configurable widget opens the modal loaded from `WidgetSettingsForm` which fetches `/widget-settings/{id}`. Saved values are stored alongside layout data.

## 5. Capability Checks

Only users with the `edit_dashboard_layout` capability can rearrange widgets. Administrators may define default layouts per role which are stored in the `ap_dashboard_widget_config` option as arrays of widget IDs.

## 6. Preview Mode

The editor includes a **Preview Role** drop‑down. Selecting another role reloads layout data using that role so administrators can simulate what different users see.

## 7. Layout Schema Example

```json
[
  { "id": "MyFavoritesWidget", "visible": true, "position": { "x": 0, "y": 0 }, "size": { "w": 6, "h": 2 } },
  { "id": "EventChatWidget", "visible": false }
]
```

This schema matches the structure returned by the REST endpoints and is compatible with `react-grid-layout`.
