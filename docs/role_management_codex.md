---
title: Role Management Codex
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---

# Role & Capability Management

This plugin registers custom roles during activation and assigns capabilities using WordPress hooks.

## Roles

- `member`
- `artist`
- `organization`
- `org_manager`
- `org_editor`
- `org_viewer`

## Capabilities

- `manage_artpulse_settings`
- `edit_artpulse_content`
- `moderate_link_requests`
- `view_artpulse_dashboard`
- `ap_premium_member`

Roles and capabilities are created in the activation hook so they are only added once. The `CapabilitiesManager` class also maps the `ap_premium_member` capability to the user's membership level via `map_meta_cap`.

## Migration

If upgrading from an early version that used different role keys, add a migration routine in your activation callback. Example:

```php
if (get_role('artpulse_moderator')) {
    remove_role('artpulse_moderator');
    add_role('moderator', 'Moderator');
}
```

***

## Default Role Assignment

New sign‑ups receive the `member` role. Users may request an upgrade to
`artist` or `organization` which an admin grants by switching the role in the
profile editor.

## Removing Roles

During uninstallation the plugin cleans up any custom roles it created. Use
`remove_role()` for each role key to avoid leaving orphaned capabilities.
