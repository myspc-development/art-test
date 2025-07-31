---
title: Role-Based Widget Visibility Checklist
category: qa
role: qa
last_updated: 2025-08-02
status: draft
---

# Role-Based Widget Visibility Checklist

The plugin defines separate dashboard experiences for **members**, **artists** and **organization** administrators. Widgets are registered in `DashboardWidgetRegistry` with role metadata so the editor and live shortcode can filter them accordingly. The Dashboard Builder UI lets administrators design per‑role layouts using drag and drop while individual users see their configured widgets when visiting the `[ap_user_dashboard]` page. This checklist verifies that widgets assigned to each role are displayed consistently and remain configurable across both contexts.

Use the [Dashboard Widgets by Role QA Checklist](dashboard-widgets-by-role.md) to see which widgets should appear for every role. Perform the steps below for each role you are validating. Screenshots are optional but recommended for your test report.

## Builder Validation
1. Log in with a user who can edit layouts (requires the `edit_dashboard_layout` capability).
2. Navigate to **ArtPulse → Dashboard Builder** in wp-admin.
3. Select the target role from the **Preview Role** dropdown.
4. Confirm all expected widgets are visible in the palette and on the canvas.
5. Drag a widget to a new position and click **Save Layout**.
6. Reload the page and verify the layout persists.

## Live Dashboard Validation
1. Log in as a user with the same role you tested in the builder.
2. Visit the page containing the `[ap_user_dashboard]` shortcode.
3. Ensure the same widgets displayed in the builder render in the live dashboard.
4. If a widget is still under development it should show placeholder text such as “Coming soon.”
5. Repeat these checks after clearing caches or using a new browser session to confirm persistence.

## Checklist Table
Fill out one row per widget while testing. Mark ❌ if a widget fails to appear, is greyed out, or does not save correctly.

| Role | Widget ID | In Builder? | Live Dashboard? | Shows Placeholder? | Notes |
|------|-----------|-------------|-----------------|--------------------|-------|
| Member | membership | ✅ | ✅ | ❌ | Fully implemented |
| Member | news_feed | ✅ | ✅ | ❌ | Fully implemented |
| Member | rsvp_button | ✅ | ✅ | ❌ | Implemented via JS widget |
| Member | event_chat | ✅ | ✅ | ❌ | Implemented via JS widget |
| Member | share_this_event | ✅ | ✅ | ❌ | Implemented via JS widget |
| Artist | artist_spotlight | ✅ | ✅ | ✅ | Implementation pending |
| Organization | audience_crm | ✅ | ✅ | ✅ | Placeholder shown |

Once all widgets for a role are verified, note any discrepancies in your issue tracker. If a widget is missing entirely, inspect `available-widgets.json` and confirm the ID is present. The registry should match the layout stored in `ap_dashboard_layout` user meta. Save failures may indicate nonce or capability problems that must be addressed before release.

This checklist ensures dashboard functionality remains consistent as new widgets are added. Update the table whenever additional widgets are registered or role definitions change.

