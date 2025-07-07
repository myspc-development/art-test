# Dashboard Widgets per Role

This codex explains how dashboards show different widgets for each WordPress role and how those layouts can be shared across sites.

## 1. Register Widgets

Hook into `artpulse_register_dashboard_widget` and call `DashboardWidgetRegistry::register()` to expose a widget:

```php
add_action('artpulse_register_dashboard_widget', function () {
    \ArtPulse\Core\DashboardWidgetRegistry::register(
        'my-widget',
        'my_widget_render',
        'view_artpulse_dashboard'
    );
});
```

The function accepts the widget slug, a render callback and an optional capability required to view it.

## 2. Assign to Roles

Visit **ArtPulse → Settings → Dashboard Widgets** to arrange defaults for each role. The drag‑and‑drop editor stores the selection in the `ap_dashboard_widget_config` option. Any role added via `add_role()` that grants `view_artpulse_dashboard` will appear in the list so you can build unique dashboards.

## 3. Import & Export

Within the same screen an **Advanced: Import/Export JSON** panel lets admins download or upload layout files. The exported JSON maps role slugs to widget IDs. Uploading a file posts to `admin-post.php?action=ap_import_widget_config` to restore those settings.
