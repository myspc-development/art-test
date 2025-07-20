---
title: Admin Permissions
category: admin
role: admin
last_updated: 2025-07-20
status: draft
---
# Admin Permissions

Capability checks fall back to the role map stored per organization. The helper
`ap_user_has_org_capability( $user_id, $org_id, $cap )` returns true when the
user's role grants the capability.

| Capability | Allowed Roles |
|------------|---------------|
| `edit_events` | admin, editor |
| `curator_threads` | curator, admin |
| `build_feed` | admin, promoter |
| `view_analytics` | admin |