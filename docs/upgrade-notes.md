---
title: Upgrade Notes
category: developer
role: developer
last_updated: 2025-08-02
status: complete
---

# Upgrade Notes

This document highlights important changes when upgrading from older versions of the ArtPulse plugin.

## Organization Role Updates

The legacy organization roles `org_manager`, `org_editor` and `org_viewer` have been removed. All organization accounts should now use the unified `organization` role.

During upgrade the `ap_migrate_org_sub_roles` routine reassigns any user with a deprecated role to `organization`. Custom plugins or scripts that checked the old role names will need to update their logic.

## Capabilities Based Access

Access differentiation is now handled exclusively through capabilities. Grant or revoke capabilities such as `manage_organization`, `edit_organization_content` or `view_organization_reports` to control what each organization user can do. This allows finer grained permissions without maintaining multiple roles.

Update any integrations to check capabilities with `current_user_can()` instead of comparing role names.

For more details see the [Role Management Codex](role_management_codex.md).

## Migration Steps

1. Update to the latest version of the plugin.
2. Trigger the upgrade routine by visiting the WordPress dashboard as an administrator.
3. Verify users previously assigned to the removed roles now appear with the `organization` role.
4. Assign new capabilities based on the level of access each user requires.

Custom roles registered by other plugins can continue to function. They should grant the desired capabilities if they extend organization privileges.
