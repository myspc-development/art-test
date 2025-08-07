---
title: Dashboard Compliance Gaps
category: docs
role: developer
last_updated: 2025-08-30
status: resolved
---
# Dashboard Compliance Gaps

The new **Full Dashboard Compliance Check** revealed discrepancies between documentation and actual plugin behavior.

- Shortcode names now align: both plugin and docs use `[ap_user_dashboard]`.
- Member and organization dashboard overview guides have been added.
- Widget registry audit confirmed all widgets declare `roles`; no exceptions were found.

Recommended actions:

1. Monitor future shortcode additions for consistency.
2. Keep `member-dashboard-overview.md` and `organization-dashboard-overview.md` updated as layouts evolve.
3. Periodically audit `DashboardWidgetRegistry` entries when adding new widgets to ensure each declares `roles`.

See `docs/qa/full-dashboard-compliance-check.md` for the complete verification checklist.
