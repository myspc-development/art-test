---
title: Widget UI Design Guide
category: widgets
role: developer
last_updated: 2025-07-20
status: draft
---

# Widget UI Design Guide

This guide consolidates the dashboard widget references into a single document. It covers
layout patterns, style guidelines, and the customization options available to administrators
and users.

## Widget Layout Patterns

The dashboard uses a draggable grid powered by **SortableJS**. Layout data posts to the
`ap_save_user_layout` AJAX action when users reorder widgets. Widgets should be output inside
an element with the `ap-widget-card` class so the drag handles work consistently.

```php
add_action('wp_ajax_ap_save_user_layout', function () {
    check_ajax_referer('ap_save_user_layout', 'nonce');
    $layout = json_decode(file_get_contents('php://input'), true)['layout'] ?? [];
    $user_id = get_current_user_id();
    if ($user_id && is_array($layout)) {
        \ArtPulse\Admin\UserLayoutManager::save_user_layout($user_id, $layout);
        wp_send_json_success();
    }
    wp_send_json_error();
});
```

Widgets render inside a container ID of `ap-user-dashboard` so the Sortable instance can
track ordering.

## Style Guidelines

Follow the [Default Design System Codex](../default-design-system-codex.md) to keep widgets
consistent. Wrap content in `.ap-widget-card` and include a heading with `.ap-card__title`.
Import design tokens in each SCSS file with:

```scss
@use "../css/tokens.css";
```

Register widgets using `DashboardWidgetRegistry::register()` so markup and styles load
automatically.

## Admin and User Customization

Administrators manage role defaults via the **Widget Editor**. Users can drag and drop their
personal widgets on the dashboard screen. WordPress stores each arrangement in the
`metaboxorder_dashboard` meta key. Existing widgets like **Site Overview**, **Upcoming Events**
and **Trending Tags** show how to structure menu nodes and widget blocks defined in
[Dashboard Menu Codex](../dashboard-menu-codex.md).

Use the reset controls to restore defaults or export/import JSON layouts when migrating
settings.