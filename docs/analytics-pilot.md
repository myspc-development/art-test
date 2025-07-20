---
title: Analytics Pilot
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# Analytics Pilot

Partner organizations can request early access to the analytics dashboard. Access is granted per user and controlled by a new REST endpoint.

## Steps

1. Collect the partner admin's email address.
2. Send a `POST` request to `/wp-json/artpulse/v1/analytics/pilot/invite` with the email.
3. The endpoint creates the user if needed, assigns the `ap_premium_member` capability and emails a short welcome message.
4. Direct partners to visit `/wp-admin/admin.php?page=artpulse-engagement` to view metrics.
5. To revoke access remove the capability from the user's profile.

```bash
curl -X POST -d 'email=org@example.com' https://example.com/wp-json/artpulse/v1/analytics/pilot/invite
```

This lightweight flow keeps the pilot manageable while gathering feedback on the new dashboards.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
