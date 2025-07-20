---
title: Widget QA Checklist
category: widgets
role: developer
last_updated: 2025-07-20
status: complete
---

Combine these verification steps when testing the widget manager and individual widgets.

## Render Check
- [ ] Widget title displays correctly
- [ ] Placeholder content replaced with real data
- [ ] No PHP notices or JavaScript errors

## Data Save
- [ ] Widget options save via AJAX
- [ ] REST API returns updated values
- [ ] Invalid data shows an inline error

## Responsive Layout
- [ ] Widgets stack properly on mobile
- [ ] Grid adjusts on tablet and desktop
- [ ] Collapse/expand controls work at all sizes

## Permissions
- [ ] Only authorized roles can view the widget
- [ ] Editing restricted to users with `manage_options`
- [ ] Hidden widgets remain inaccessible via direct REST calls

Status:
- [ ] Verified
- [ ] Needs Testing
