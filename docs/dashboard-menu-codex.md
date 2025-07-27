---
title: Dashboard Menu Codex
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---

# Dashboard Menu Codex

The dashboard sidebar is built from the configuration returned by `ap_get_dashboard_menu_config()` in [`includes/dashboard-menu.php`](../includes/dashboard-menu.php). Each key in the array corresponds to a user role and contains menu items for that role.

```php
$menu = [
    'member' => [
        ['id' => 'membership', 'section' => '#membership', 'label' => __('Membership', 'artpulse'), 'icon' => 'dashicons-admin-users', 'capability' => 'read'],
        // ...
    ],
    'artist' => [
        ['id' => 'content', 'section' => '#content', 'label' => __('Content', 'artpulse'), 'icon' => 'dashicons-media-default', 'capability' => 'edit_posts'],
        // ...
    ],
    'organization' => [
        ['id' => 'next-payment', 'section' => '#next-payment', 'label' => __('Next Payment', 'artpulse'), 'icon' => 'dashicons-money', 'capability' => 'organization'],
        // ...
    ],
];
```

The `ap_merge_dashboard_menus()` helper combines menus when a user has multiple roles. Items are deduplicated by `id`, and the optional `$show_notifications` parameter controls whether the notifications link is included.

## Role Examples

- **Member** – shows membership status, upgrade link, local events, favorites, upcoming events, activity feed, account tools and notifications.
- **Artist** – inherits the member links and adds a **Content** section for managing posts.
- **Organization** – includes finance links like **Next Payment** and **Transactions** in addition to the standard menu.

## Customization Notes

To adjust the menu you can modify `ap_get_dashboard_menu_config()` or create your own wrapper that filters the array before calling `ap_merge_dashboard_menus()`. For example, remove the notifications link:

```php
$menu = ap_merge_dashboard_menus($user->roles, false);
```

Additional entries may be inserted by extending the arrays for each role. Keep the `id`, `section`, `label`, `icon` and `capability` keys so the navigation template works correctly.

