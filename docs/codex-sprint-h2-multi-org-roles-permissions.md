---
title: Codex: Sprint H2 – Multi-Org Roles & Permissions
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# Codex: Sprint H2 – Multi-Org Roles & Permissions

## Overview
This sprint upgrades the role system so a single user can manage multiple organizations. Access is scoped per organization with dashboards that span all orgs for administrators.

## Goals
- **Multi-org user mapping** – One user linked to multiple orgs
- **Cross-org UI** – Admin view to manage all associated orgs
- **Scoped capabilities** – Roles apply per organization
- **Org-specific invites** – Invite a user to a specific org

## Data Model
Create `ap_org_roles`:

| Field      | Type    | Notes                                 |
|------------|---------|---------------------------------------|
| `user_id`  | INT     | WordPress user ID                     |
| `org_id`   | INT     | Maps to CPT `ap_organization`         |
| `role`     | VARCHAR | `admin`, `curator`, `editor`, etc.    |
| `assigned_at` | DATETIME | Optional timestamp                 |

Unique index `(user_id, org_id, role)` ensures one role per org.

## REST Endpoints
- `GET /artpulse/v1/org-roles/current` – List current user roles
- `POST /artpulse/v1/org-roles/assign` – Assign role (admin only)

Example response for `GET /org-roles/current`:

```json
[
  { "org_id": 7, "org_name": "Soho Gallery", "role": "curator" },
  { "org_id": 12, "org_name": "NYC Artists Fund", "role": "admin" }
]
```

## Scoped Capability Enforcement
Use the helper:

```php
function ap_user_has_org_role($user_id, $org_id, $role = null) {
    global $wpdb;
    $sql = "SELECT COUNT(*) FROM ap_org_roles WHERE user_id = %d AND org_id = %d";
    if ($role) $sql .= " AND role = %s";
    return $wpdb->get_var($wpdb->prepare($sql, $user_id, $org_id, $role)) > 0;
}
```

Check this instead of `current_user_can()` for org-scoped actions.

## Cross-Org Dashboard
- **Admin tab** `/wp-admin?page=ap-orgs` shows all orgs with role badges
- **Context switcher** stores `$_SESSION['ap_active_org']` to filter views

## Invite Flow Per Org
- **Invite endpoint** `POST /artpulse/v1/org-roles/invite` with `email`, `org_id` and `role`
- Store invites in `ap_org_invites` (`email`, `org_id`, `role`, `token`, `invited_at`)
- On registration, lookup by token to assign the org role

## QA Checklist
- User with roles in two orgs sees both in the dashboard
- Assign role via REST reflects immediately
- Users manage events only where permitted
- Invited user accepts and gains scoped role
- Curator of Org A cannot access Org B

## Developer Sprint Checklist
- [x] `ap_org_roles` table live
- [x] REST routes for assign/list
- [x] Scoped permission checks
- [x] Org dashboard context switcher
- [x] Invite flow per org
- [x] Codex docs updated

## Codex Docs to Update
- Roles & Permissions
- Multi-org structure
- Scoped access enforcement
- Admin UX and cross-org dashboard
- Invite workflows
- “Managing multiple institutions or venues” guide

💬 Found something outdated? Submit Feedback
