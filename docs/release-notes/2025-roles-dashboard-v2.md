---
title: Roles Dashboard v2 Release Notes
category: release-notes
role: developer
last_updated: 2025-08-16
status: complete
---

# Roles Dashboard v2

The Roles Dashboard v2 introduces streamlined management of user capabilities and cleaner navigation for administrators.

## Highlights

- Unified interface for assigning roles across multiple organizations.
- Drag-and-drop layout with live previews.
- Feature flag support for gradual rollout.

![Roles dashboard overview](/docs/release-notes/images/roles-dashboard-overview.png)

![Roles dashboard demo](/docs/release-notes/images/roles-dashboard-demo.gif)

## Upgrade Checklist

1. Back up the database and existing configuration.
2. Enable the `roles_dashboard_v2` feature flag.
3. Run `wp ap roles-dashboard upgrade` to migrate settings.
4. Clear caches and rebuild assets.
5. Follow the [manual QA script](../manual-qa/roles-dashboard.md).
