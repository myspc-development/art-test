---
title: ArtPulse Codex: Multi-Organization Management
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# ArtPulse Codex: Multi-Organization Management

## Multi-org Roles + Permissions

Organizations may operate multiple groups or partner collectives under a single account. The plugin allows users to join several orgs and maintain different roles in each.

- Roles are defined per organization using `OrgRoleManager`.
- User assignments are stored in the `ap_org_roles` table. Each record stores `user_id`, `org_id`, `role` and the `site_id` when running in multisite mode.
- REST endpoints under `/artpulse/v1/org-roles` return role definitions and handle assignments.
- Only administrators and users with `manage_options` can assign cross-org roles.
- Capabilities map to WordPress permissions so third‑party extensions can check them with `current_user_can()`.

Roles can be managed via the REST API. The former `ap-org-roles` admin page was removed in 2025.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
