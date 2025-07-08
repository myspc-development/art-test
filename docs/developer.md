# Developer Guide

This page lists hooks and helpers for extending the dashboard widget system.

## Adding Widgets
Use `\ArtPulse\Core\DashboardWidgetRegistry::register()` in a `plugins_loaded` hook.
Each widget accepts an ID, label, icon, description and optional `category` and `roles` keys.
Provide a callback that outputs the widget markup.

```php
DashboardWidgetRegistry::register(
    'profile_overview',
    __('Profile Overview', 'artpulse'),
    'admin-users',
    __('Shows basic account info and avatar.', 'artpulse'),
    'ap_widget_profile_overview',
    ['category' => 'profile', 'roles' => ['member', 'artist']]
);
```

## Role Fallback Logic
`UserLayoutManager::get_layout_for_user()` checks the user's saved layout, their role default and finally all registered widgets. This ensures new users always see a complete dashboard.

## Export and Import
Call `UserLayoutManager::export_layout( $role )` to get JSON. Use `UserLayoutManager::import_layout( $role, $json )` to load it.
