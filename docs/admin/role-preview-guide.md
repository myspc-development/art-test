---
title: Role Preview Guide
category: admin
role: admin
last_updated: 2025-07-20
status: complete
---

# Role Preview Guide

This planned feature allows administrators to preview dashboards as other roles. It builds on the existing role-based layouts described in [Admin Dashboard UI](./admin-dashboard-ui.md).

## Preview Controls

A **Preview Role** dropdown appears at the top of the dashboard editor. Selecting a role reloads the dashboard using that role's layout while keeping the admin's session active.

Backend support will expose an impersonation endpoint:

```http
POST /wp-json/artpulse/v1/impersonate/{role}
```

The endpoint returns a nonce used to render widgets as if the admin had that role.

## Implementation Notes

1. The dropdown defaults to the admin's current role.
2. Selecting another role triggers a call to the impersonation endpoint and reloads layout data.
3. If the endpoint fails, the UI falls back to server rendered templates.

## Related Links

- [Admin Feature Documentation Review](./admin-feature-review.md)
- [Dashboard Editor Developer Guide](../dashboard-editor-developer-guide.md)

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
