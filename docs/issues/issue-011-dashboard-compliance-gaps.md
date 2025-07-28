---
title: Dashboard Compliance Gaps
category: docs
role: developer
last_updated: 2025-07-28
status: open
---
# Dashboard Compliance Gaps

The new **Full Dashboard Compliance Check** revealed discrepancies between documentation and actual plugin behavior.

- Shortcode names differ: the plugin registers `[ap_user_dashboard]` but docs refer to `[ap_member_dashboard]`.
- Member and organization dashboard overview guides are missing.
- Some widgets lack explicit `roles` in the registry which may expose them to unauthorized users.

Recommended actions:

1. Document current shortcode usage and update the Codex where needed.
2. Create `member-dashboard-overview.md` and `organization-dashboard-overview.md` based on the implemented layouts.
3. Audit `DashboardWidgetRegistry` entries to ensure each widget declares `roles`.

See `docs/qa/full-dashboard-compliance-check.md` for the complete verification checklist.
