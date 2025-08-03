---
title: Dashboard QA Checklist
category: qa
role: qa
last_updated: 2025-07-31
status: complete
---

# Dashboard QA Checklist

This document combines the former **Dashboard Builder UAT** and **Widget QA** checklists. Use it to verify both the builder interface and the live dashboard widgets before release.

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

## 3. Builder Workflow

Invite internal or community testers and provide them with login details. Ask testers to:

1. Customize their dashboard using drag-and-drop.
2. Save the layout and refresh the page to confirm persistence.
3. Switch roles (where permitted) and verify role-based presets load correctly.
4. Reset the layout to defaults and ensure widgets return to the original order.
5. Check that hiding a widget removes it from the registry and REST calls.
6. Note any PHP or JavaScript errors displayed while `WP_DEBUG` is active.

## 4. Widget Verification

### Render
- [ ] Widget title displays correctly
- [ ] Placeholder content replaced with real data

### Data Save
- [ ] Widget options save via AJAX
- [ ] REST API returns updated values
- [ ] Invalid data shows an inline error

### Responsive Layout
- [ ] Widgets stack properly on mobile
- [ ] Grid adjusts on tablet and desktop
- [ ] Collapse/expand controls work at all sizes

### Permissions
- [ ] Only authorized roles can view the widget
- [ ] Editing restricted to users with `manage_options`
- [ ] Hidden widgets remain inaccessible via direct REST calls

### Browser Compatibility
- [ ] Render tested in latest Chrome, Firefox and Safari
- [ ] Mobile Safari and Chrome show no layout shifts
- [ ] Keyboard navigation works consistently across browsers

### Accessibility
- [ ] ARIA labels present for interactive controls
- [ ] Sufficient color contrast for text and icons
- [ ] Widget is usable without a mouse

### Performance
- [ ] Widget loads within two seconds on a fresh page view
- [ ] No excessive network requests
- [ ] Memory usage stays under 50 MB after multiple edits

Status:

- [ ] Verified
- [ ] Needs Testing

## 5. Widget Role Rendering Verification

Optionally enable a debug block on the dashboard to verify widget visibility for
each role. Add the following snippet inside `dashboard-generic.php` or a similar
template. It prints the current role, the active layout and all registered
widgets when viewing the page as an administrator:

```php
<?php if (current_user_can('manage_options')): ?>
  <div class="notice notice-info">
    <p><strong>🧩 DEBUG: Rendering Widget Diagnostic</strong></p>
    <p><strong>Current User Role:</strong> <?= esc_html(DashboardController::get_role(get_current_user_id())) ?></p>

    <p><strong>Active Layout:</strong></p>
    <pre><?php print_r(DashboardController::get_user_dashboard_layout(get_current_user_id())); ?></pre>

    <p><strong>Registered Widgets:</strong></p>
    <pre><?php print_r(ArtPulse\Core\DashboardWidgetRegistry::get_all()); ?></pre>
  </div>
<?php endif; ?>
```

Compare the layout array with the widgets shown on screen after saving changes in the Dashboard Builder. This helps confirm all widgets registered to a role are rendered correctly.

## 6. Reporting

Collect feedback via your preferred tracking tool. Include screenshots and steps to reproduce any issues. When all checks pass, update this document’s status to **complete** and proceed with the release checklist.

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*

## 7. Sign Off
Once all testers confirm the expected behaviour and no blocking issues remain, note the completion date below and notify the release manager.

