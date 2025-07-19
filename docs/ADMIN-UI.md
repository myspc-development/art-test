# Admin Configuration UI

A WordPress admin page for configuring:
- Widget availability by role
- Default layout per role
- Locked widgets

Access this screen from **ArtPulse → Widget Matrix** (submenu slug
`artpulse-widget-matrix`). Use it to assign widgets per role. For editing
layout order, open **ArtPulse → Widget Editor** (submenu slug
`artpulse-widget-editor`); see the
[Admin Dashboard Widgets Editor Guide](./Admin_Dashboard_Widgets_Editor_Guide.md).

Implemented in:
- PHP: `admin-dashboard-config.php`
- JS: `admin-dashboard.js` (React + REST)

Stored in:
- artpulse_widget_roles
- artpulse_default_layouts
- artpulse_locked_widgets
