---
title: Member Dashboard Overview
category: user
role: member
last_updated: 2025-08-30
status: complete
---
# Member Dashboard Overview

The member dashboard is rendered with the `[ap_user_dashboard]` shortcode. `DashboardController` assigns the following widgets by default:

1. `widget_news` – Latest updates from ArtPulse.
2. `membership` – Current membership details.
3. `upgrade` – Upgrade prompt for additional features.
4. `account-tools` – Quick access to account utilities.
5. `recommended_for_you` – Suggested content based on activity.
6. `my_rsvps` – Upcoming events you have RSVP’d to.
7. `favorites` – Saved artists or events.
8. `local-events` – Events near your location.
9. `my-follows` – Artists and organizations you follow.
10. `notifications` – Recent notifications.
11. `messages` – Direct messages and inbox previews.
12. `dashboard_feedback` – Feedback form for dashboard improvements.
13. `cat_fact` – Fun cat facts to lighten the mood.

Administrators can override this layout through the dashboard builder or by updating the `ap_dashboard_widget_config` option. Individual members may customize their own layout via drag-and-drop; changes are stored in the `ap_dashboard_layout` user meta key.
