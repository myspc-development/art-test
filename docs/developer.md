---
title: Developer Guide
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# Developer Guide

This page lists hooks and helpers for extending the dashboard widget system.

## Adding Widgets
Use `\ArtPulse\Core\DashboardWidgetRegistry::register()` in a `plugins_loaded` hook.
Each widget accepts an ID, label, icon, description and optional `category` and `roles` keys.
Provide a callback that outputs the widget markup.

```php
DashboardWidgetRegistry::register(
    'membership',
    __('Membership', 'artpulse'),
    'users',
    __('Subscription status and badges.', 'artpulse'),
    'ap_widget_membership',
    ['category' => 'engagement', 'roles' => ['member', 'artist']]
);
```

## Role Fallback Logic
`UserLayoutManager::get_layout_for_user()` checks the user's saved layout, their role default and finally all registered widgets. This ensures new users always see a complete dashboard.

## Export and Import
Call `UserLayoutManager::export_layout( $role )` to get JSON. Use `UserLayoutManager::import_layout( $role, $json )` to load it.

## Messaging

Direct messages are managed via REST endpoints registered by `DirectMessages`.
Routes:

```text
POST /wp-json/artpulse/v1/messages
GET  /wp-json/artpulse/v1/messages?with={user_id}
GET  /wp-json/artpulse/v1/conversations
POST /wp-json/artpulse/v1/message/read
```

Include a `nonce` parameter using the value from the `APMessages.nonce` script
localization. This value is produced by `wp_create_nonce('wp_rest')`. The
bundled `assets/js/ap-messages.js` helper polls the API using a recursive
`setTimeout` loop and sends messages via `wp.apiFetch`‑style requests.

💬 Found something outdated? Submit Feedback
