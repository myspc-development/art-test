---
title: Dashboard Builder UAT Checklist
category: qa
role: qa
last_updated: 2025-07-20
status: draft
---

# Dashboard Builder UAT Checklist

This document outlines the final user acceptance testing (UAT) steps for the Dashboard Builder. Run these checks in a staging or preview environment before any public release.

## 1. Deploy to Staging

- Use the CI/CD pipeline or build script `npm run build` followed by the deployment step for your staging server.
- Enable `WP_DEBUG` in `wp-config.php` if the environment is secure so PHP notices surface during testing.
- Verify the Dashboard Builder loads for Members, Artists and Organization admins without errors.
- Confirm default layout presets are available from the **Import/Export** panel for each role.

## 2. Seed Test Users

Create sample accounts so testers can interact with real data:

```bash
wp user create member1 member1@example.com --role=member --user_pass=pass
wp user create member2 member2@example.com --role=member --user_pass=pass
wp user create member3 member3@example.com --role=member --user_pass=pass

wp user create artist1 artist1@example.com --role=artist --user_pass=pass
wp user create artist2 artist2@example.com --role=artist --user_pass=pass
wp user create artist3 artist3@example.com --role=artist --user_pass=pass

wp user create org1 org1@example.com --role=organization_admin --user_pass=pass
wp user create org2 org2@example.com --role=organization_admin --user_pass=pass
wp user create org3 org3@example.com --role=organization_admin --user_pass=pass
```

- Import seed data such as events and messages from the `data/` directory if available:

```bash
wp db import data/event-seed.sql
```

## 3. Tester Workflow

Invite internal or community testers and provide them with login details. Ask testers to:

1. Customize their dashboard using drag-and-drop.
2. Save the layout and refresh the page to confirm persistence.
3. Switch roles (where permitted) and verify role-based presets load correctly.
4. Reset the layout to defaults and ensure widgets return to the original order.
5. Check that hiding a widget removes it from the registry and REST calls.
6. Note any PHP or JavaScript errors displayed while `WP_DEBUG` is active.

## 4. Reporting

Collect feedback via your preferred tracking tool. Include screenshots and steps to reproduce any issues. When all checks pass, update this document’s status to **complete** and proceed with the release checklist.

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
