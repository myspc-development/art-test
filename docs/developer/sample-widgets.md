---
title: Sample Widgets
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---

# Sample Widgets

`src/Sample/SampleWidgets.php` provides example dashboard widgets used for demonstration and testing.
Developers can review this file to learn how widgets are registered via
`DashboardWidgetRegistry::register()`.
These widgets are loaded during the `init` action in `artpulse-management.php`
so a fresh install always includes a few sample widgets that can be dragged
around the dashboard. They are not meant for production use and can be
removed if desired.
